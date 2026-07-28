<?php
set_time_limit(0);
ini_set('memory_limit', '1024M');

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

// Предзагружаем psr/log из поставки Битрикс, чтобы исключить конфликт версий
// с /local/lib/google/vendor/psr/log (иначе возможны E_COMPILE_ERROR по сигнатурам).
$bitrixPsrDirs = [
    $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/vendor/psr/log/src',
    $_SERVER['DOCUMENT_ROOT'] . '/bitrix/vendor/psr/log/src',
];

$psrLogFiles = [
    'LoggerInterface.php',
    'LogLevel.php',
    'InvalidArgumentException.php',
    'LoggerAwareInterface.php',
    'LoggerAwareTrait.php',
    'LoggerTrait.php',
    'AbstractLogger.php',
    'NullLogger.php',
];

foreach ($bitrixPsrDirs as $dir) {
    if (!is_dir($dir)) {
        continue;
    }

    foreach ($psrLogFiles as $file) {
        $path = $dir . '/' . $file;
        if (is_file($path)) {
            require_once $path;
        }
    }

    if (interface_exists('Psr\Log\LoggerInterface', false) && class_exists('Psr\Log\AbstractLogger', false)) {
        break;
    }
}

require $_SERVER['DOCUMENT_ROOT'] . '/local/lib/google/vendor/autoload.php';

use Bitrix\Main\Loader;

if (!Loader::includeModule('iblock')) {
    die('iblock not connected');
}

$spreadsheetId = $arParams['GOOGLE_SHEET'];
$sheetName = $arParams['GOOGLE_RANGE'];
$iblockId = (int)$arParams['IBLOCK_ID'];

$client = new Google_Client();
$client->setScopes([Google_Service_Sheets::SPREADSHEETS, Google_Service_Sheets::DRIVE]);
$client->setAuthConfig($_SERVER['DOCUMENT_ROOT'] . '/local/lib/service_key.json');
$service = new Google_Service_Sheets($client);

/* ================= Заголовки ================= */
$headers = [[
    'ID', 'Название', 'Категория', 'Фото анонса', 'Подробное описание', 'Доп фото',
    'META_TITLE', 'META_DESCRIPTION', 'PAGE_TITLE',
    'Параметры', 'Размер одежды', 'Размер обуви', 'Рост', 'Цвет волос', 'Длина волос',
    'Иностранный язык', 'Опыт работы',
]];

$propMap = [
    'Параметры' => 'PARAMS',
    'Размер одежды' => 'SIZE',
    'Размер обуви' => 'SIZE_SHOE',
    'Рост' => 'ROST',
    'Цвет волос' => 'HAIR_COLOR',
    'Длина волос' => 'HAIR_LENGTH',
    'Иностранный язык' => 'LANG',
    'Опыт работы' => 'EXPERIENCE',
];

function normalizeMultiValue($value)
{
    if ($value === null || $value === '') {
        return [];
    }

    $parts = array_map('trim', explode('|', (string)$value));
    $parts = array_filter($parts, static function ($item) {
        return $item !== '';
    });

    return array_values(array_unique($parts));
}

function normalizeHeaderKey($value)
{
    $value = trim((string)$value);
    if ($value === '') {
        return '';
    }

    $value = preg_replace('/\s+/u', ' ', $value);
    return mb_strtolower($value);
}

function getCellValue(array $row, array $headerIndex, $headerName)
{
    $key = normalizeHeaderKey($headerName);
    if ($key === '' || !isset($headerIndex[$key])) {
        return '';
    }

    return (string)($row[$headerIndex[$key]] ?? '');
}

function getFileArrayFromUrl($url)
{
    if (!$url) {
        return false;
    }

    $url = trim((string)$url);
    if ($url === '') {
        return false;
    }

    $parsed = parse_url($url);
    $path = $url;

    if (is_array($parsed) && !empty($parsed['path'])) {
        $host = $parsed['host'] ?? '';
        $serverHost = $_SERVER['SERVER_NAME'] ?? '';

        if ($host === '' || $host === $serverHost) {
            $path = $_SERVER['DOCUMENT_ROOT'] . $parsed['path'];
        }
    }

    return CFile::MakeFileArray($path);
}

function getFullUrl($fileId)
{
    return $fileId ? 'https://' . $_SERVER['SERVER_NAME'] . CFile::GetPath($fileId) : '';
}

function getSectionsString($elementId)
{
    $sections = [];
    $rs = CIBlockElement::GetElementGroups($elementId, true, ['ID', 'NAME']);
    while ($ar = $rs->Fetch()) {
        $sections[] = $ar['NAME'];
    }

    return implode('|', $sections);
}

function findSectionIdsByNames($iblockId, array $names)
{
    $ids = [];

    foreach ($names as $name) {
        $res = CIBlockSection::GetList([], ['IBLOCK_ID' => $iblockId, '=NAME' => $name], false, ['ID']);
        if ($section = $res->Fetch()) {
            $ids[] = (int)$section['ID'];
        }
    }

    return $ids;
}

function getPropFull($iblockId, $elementId, $propertyIdentifier)
{
    $values = [];

    $filter = [];
    if (is_int($propertyIdentifier)) {
        $filter['ID'] = $propertyIdentifier;
    } else {
        $filter['CODE'] = $propertyIdentifier;
    }

    $res = CIBlockElement::GetProperty($iblockId, $elementId, ['sort' => 'asc'], $filter);
    while ($p = $res->Fetch()) {
        if (is_array($p['VALUE']) && count($p['VALUE']) > 0) {
            $values = array_merge($values, $p['VALUE']);
        } elseif ($p['VALUE']) {
            $values[] = $p['VALUE'];
        }
    }

    return implode('|', $values);
}

function getPropertyMetaByCode($iblockId, $code)
{
    static $cache = [];

    if (!isset($cache[$iblockId])) {
        $cache[$iblockId] = [];
        $res = CIBlockProperty::GetList(['SORT' => 'ASC'], ['IBLOCK_ID' => $iblockId]);
        while ($prop = $res->Fetch()) {
            $cache[$iblockId][$prop['CODE']] = $prop;
        }
    }

    return $cache[$iblockId][$code] ?? null;
}

function resolvePropertyIdentifier($iblockId, $propCode, $columnName)
{
    $meta = getPropertyMetaByCode($iblockId, $propCode);
    if ($meta) {
        return $propCode;
    }

    static $nameCache = [];
    if (!isset($nameCache[$iblockId])) {
        $nameCache[$iblockId] = [];
        $res = CIBlockProperty::GetList(['SORT' => 'ASC'], ['IBLOCK_ID' => $iblockId]);
        while ($prop = $res->Fetch()) {
            $name = trim((string)($prop['NAME'] ?? ''));
            if ($name !== '') {
                $nameCache[$iblockId][normalizeHeaderKey($name)] = $prop;
            }
        }
    }

    $normalizedName = normalizeHeaderKey($columnName);
    if ($normalizedName !== '' && isset($nameCache[$iblockId][$normalizedName])) {
        $prop = $nameCache[$iblockId][$normalizedName];
        return !empty($prop['CODE']) ? $prop['CODE'] : (int)$prop['ID'];
    }

    return null;
}

function mapValuesForProperty($iblockId, $propertyIdentifier, $propCode, $columnName, array $values)
{
    $property = null;

    if (is_int($propertyIdentifier)) {
        $res = CIBlockProperty::GetList([], ['IBLOCK_ID' => $iblockId, 'ID' => $propertyIdentifier]);
        $property = $res->Fetch() ?: null;
    } elseif (is_string($propertyIdentifier) && $propertyIdentifier !== "") {
        $property = getPropertyMetaByCode($iblockId, $propertyIdentifier);
    }

    if (!$property) {
        $fallback = resolvePropertyIdentifier($iblockId, $propCode, $columnName);
        if (is_int($fallback)) {
            $res = CIBlockProperty::GetList([], ['IBLOCK_ID' => $iblockId, 'ID' => $fallback]);
            $property = $res->Fetch() ?: null;
        } elseif (is_string($fallback) && $fallback !== "") {
            $property = getPropertyMetaByCode($iblockId, $fallback);
        }
    }
    if (!$property) {
        return null;
    }

    if (empty($values)) {
        return false;
    }

    $isMultiple = ($property['MULTIPLE'] ?? 'N') === 'Y';
    $resultValues = $values;

    if (($property['PROPERTY_TYPE'] ?? '') === 'L') {
        static $enumCache = [];

        if (!isset($enumCache[$property['ID']])) {
            $enumCache[$property['ID']] = [];
            $enumRes = CIBlockPropertyEnum::GetList(['SORT' => 'ASC'], ['PROPERTY_ID' => $property['ID']]);
            while ($enum = $enumRes->Fetch()) {
                $enumValue = trim((string)($enum['VALUE'] ?? ''));
                if ($enumValue !== '') {
                    $enumCache[$property['ID']][$enumValue] = (int)$enum['ID'];
                }
            }
        }

        $mapped = [];
        foreach ($values as $value) {
            $value = trim((string)$value);
            if ($value === '') {
                continue;
            }

            if (isset($enumCache[$property['ID']][$value])) {
                $mapped[] = $enumCache[$property['ID']][$value];
                continue;
            }

            foreach ($enumCache[$property['ID']] as $enumValue => $enumId) {
                if (mb_strtolower($enumValue) === mb_strtolower($value)) {
                    $mapped[] = $enumId;
                    break;
                }
            }
        }

        $resultValues = array_values(array_unique($mapped));
    }

    if (($property['PROPERTY_TYPE'] ?? '') === 'N') {
        $numbers = [];
        foreach ($resultValues as $value) {
            $normalized = str_replace(',', '.', (string)$value);
            if ($normalized === '' || !is_numeric($normalized)) {
                continue;
            }
            $numbers[] = $normalized;
        }
        $resultValues = $numbers;
    }

    if (empty($resultValues)) {
        return false;
    }

    return $isMultiple ? array_values($resultValues) : reset($resultValues);
}


function updateSeoTemplates($iblockId, $elementId, array $headerIndex, array $row)
{
    $templates = [
        'ELEMENT_META_TITLE' => getCellValue($row, $headerIndex, 'META_TITLE'),
        'ELEMENT_META_DESCRIPTION' => getCellValue($row, $headerIndex, 'META_DESCRIPTION'),
        'ELEMENT_PAGE_TITLE' => getCellValue($row, $headerIndex, 'PAGE_TITLE'),
    ];

    $templateHandler = new \Bitrix\Iblock\InheritedProperty\ElementTemplates($iblockId, $elementId);
    $templateHandler->set($templates);
}

function updateElementFromSheetRow($iblockId, array $row, array $headerIndex, array $resolvedPropMap)
{
    $id = (int)getCellValue($row, $headerIndex, 'ID');

    $name = trim(getCellValue($row, $headerIndex, 'Название'));
    if ($name === '') {
        return ['ok' => false, 'id' => $id, 'error' => 'Пустое название'];
    }

    $previewPicture = getFileArrayFromUrl(getCellValue($row, $headerIndex, 'Фото анонса'));

    $fields = [
        'IBLOCK_ID' => $iblockId,
        'NAME' => $name,
        'DETAIL_TEXT' => getCellValue($row, $headerIndex, 'Подробное описание'),
        'DETAIL_TEXT_TYPE' => 'html',
        'ACTIVE' => 'Y',
        'IPROPERTY_TEMPLATES' => [
            'ELEMENT_META_TITLE' => getCellValue($row, $headerIndex, 'META_TITLE'),
            'ELEMENT_META_DESCRIPTION' => getCellValue($row, $headerIndex, 'META_DESCRIPTION'),
            'ELEMENT_PAGE_TITLE' => getCellValue($row, $headerIndex, 'PAGE_TITLE'),
        ],
    ];

    if ($previewPicture) {
        $fields['PREVIEW_PICTURE'] = $previewPicture;
    }

    $el = new CIBlockElement();

    if ($id > 0) {
        if (!$el->Update($id, $fields)) {
            return ['ok' => false, 'id' => $id, 'error' => $el->LAST_ERROR];
        }
    } else {
        $id = (int)$el->Add($fields);
        if ($id <= 0) {
            return ['ok' => false, 'id' => 0, 'error' => $el->LAST_ERROR];
        }
    }

    updateSeoTemplates($iblockId, $id, $headerIndex, $row);

    $sectionNames = normalizeMultiValue(getCellValue($row, $headerIndex, 'Категория'));
    if (!empty($sectionNames)) {
        $sectionIds = findSectionIdsByNames($iblockId, $sectionNames);
        if (!empty($sectionIds)) {
            CIBlockElement::SetElementSection($id, $sectionIds, false);
        }
    }

    $morePhotos = [];
    foreach (normalizeMultiValue(getCellValue($row, $headerIndex, 'Доп фото')) as $photoUrl) {
        $file = getFileArrayFromUrl($photoUrl);
        if ($file) {
            $morePhotos[] = ['VALUE' => $file, 'DESCRIPTION' => ''];
        }
    }

    $propertyValues = [];
    foreach ($resolvedPropMap as $columnName => $resolved) {
        $propertyIdentifier = $resolved['identifier'];
        $propCode = $resolved['code'];

        $rawValues = normalizeMultiValue(getCellValue($row, $headerIndex, $columnName));
        $propertyValues[$propertyIdentifier] = mapValuesForProperty($iblockId, $propertyIdentifier, $propCode, $columnName, $rawValues);
    }
    $propertyValues['MORE_PHOTO'] = !empty($morePhotos) ? $morePhotos : false;

    CIBlockElement::SetPropertyValuesEx($id, $iblockId, $propertyValues);

    return ['ok' => true, 'id' => $id, 'error' => ''];
}

/* ================= Проверка и создание заголовков ================= */
$response = $service->spreadsheets_values->get($spreadsheetId, $sheetName . '!A1:Q1');
$existingHeaders = $response->getValues() ?? [];

if (empty($existingHeaders)) {
    $service->spreadsheets_values->update(
        $spreadsheetId,
        $sheetName . '!A1',
        new Google_Service_Sheets_ValueRange(['values' => $headers]),
        ['valueInputOption' => 'RAW']
    );
    $existingHeaders = $headers;
}

$headerIndex = [];
foreach ($existingHeaders[0] as $idx => $headerName) {
    $normalizedHeader = normalizeHeaderKey($headerName);
    if ($normalizedHeader !== '') {
        $headerIndex[$normalizedHeader] = $idx;
    }
}

$resolvedPropMap = [];
foreach ($propMap as $columnName => $propCode) {
    $propertyIdentifier = resolvePropertyIdentifier($iblockId, $propCode, $columnName);
    if ($propertyIdentifier !== null) {
        $resolvedPropMap[$columnName] = [
            'identifier' => $propertyIdentifier,
            'code' => $propCode,
        ];
    }
}

/* ================= Обратная синхронизация: Google Sheets -> Bitrix ================= */
$sheetRowsResp = $service->spreadsheets_values->get($spreadsheetId, $sheetName . '!A2:Q');
$sheetRows = $sheetRowsResp->getValues() ?? [];

$sheetIdsFromTable = [];
foreach ($sheetRows as $row) {
    $tableId = (int)getCellValue($row, $headerIndex, 'ID');
    if ($tableId > 0) {
        $sheetIdsFromTable[] = $tableId;
    }
}

$sheetIds = [];
$rowNum = 2;
$errors = [];

foreach ($sheetRows as $row) {
    $result = updateElementFromSheetRow($iblockId, $row, $headerIndex, $resolvedPropMap);

    if (!$result['ok']) {
        $errors[] = 'Строка ' . $rowNum . ': ' . $result['error'];
        $rowNum++;
        continue;
    }

    $sheetIds[] = $result['id'];

    if (trim(getCellValue($row, $headerIndex, 'ID')) === '') {
        $service->spreadsheets_values->update(
            $spreadsheetId,
            $sheetName . '!A' . $rowNum,
            new Google_Service_Sheets_ValueRange(['values' => [[$result['id']]]]),
            ['valueInputOption' => 'RAW']
        );
    }

    $rowNum++;
}

/* ================= Удаление элементов, удалённых из таблицы ================= */
$allIds = [];
$resIds = CIBlockElement::GetList([], ['IBLOCK_ID' => $iblockId], false, false, ['ID']);
while ($itemId = $resIds->Fetch()) {
    $allIds[] = (int)$itemId['ID'];
}

$sheetIds = array_values(array_unique(array_filter(array_merge($sheetIdsFromTable, $sheetIds))));
$idsToDelete = [];

if (count($sheetRows) > 0 && !empty($sheetIds)) {
    $idsToDelete = array_diff($allIds, $sheetIds);
    foreach ($idsToDelete as $deleteId) {
        CIBlockElement::Delete((int)$deleteId);
    }
} elseif (count($sheetRows) > 0 && empty($sheetIds)) {
    $errors[] = 'Удаление отключено: в таблице есть строки, но не найдено ни одного корректного ID';
}

/* ================= Чтение существующих ID в Google Sheets ================= */
$response = $service->spreadsheets_values->get($spreadsheetId, $sheetName . '!A2:A');
$existingRows = $response->getValues() ?? [];
$googleMap = [];
foreach ($existingRows as $i => $row) {
    if (!empty($row[0])) {
        $googleMap[(int)$row[0]] = $i + 2;
    }
}

/* ================= Получение элементов из Bitrix ================= */
$res = CIBlockElement::GetList([], ['IBLOCK_ID' => $iblockId], false, false, ['ID', 'IBLOCK_ID', 'NAME', 'DETAIL_TEXT', 'PREVIEW_PICTURE']);

$dataAppend = [];
$dataUpdate = [];

while ($obElement = $res->GetNextElement()) {
    $item = $obElement->GetFields();

    $iprop = new \Bitrix\Iblock\InheritedProperty\ElementValues($iblockId, $item['ID']);
    $seo = $iprop->getValues();

    $category = getSectionsString($item['ID']);
    $previewPic = getFullUrl($item['PREVIEW_PICTURE']);

    $detailText = (string)($item['~DETAIL_TEXT'] ?? $item['DETAIL_TEXT'] ?? '');

    $morePhotos = [];
    $rsProp = CIBlockElement::GetProperty($iblockId, $item['ID'], ['sort' => 'asc'], ['CODE' => 'MORE_PHOTO']);
    while ($p = $rsProp->Fetch()) {
        if ($p['VALUE']) {
            $morePhotos[] = getFullUrl($p['VALUE']);
        }
    }

    $row = [
        $item['ID'],
        $item['NAME'],
        $category,
        $previewPic,
        $detailText,
        implode('|', $morePhotos),
        $seo['ELEMENT_META_TITLE'] ?? '',
        $seo['ELEMENT_META_DESCRIPTION'] ?? '',
        $seo['ELEMENT_PAGE_TITLE'] ?? '',
        getPropFull($iblockId, $item['ID'], $resolvedPropMap['Параметры']['identifier'] ?? 'PARAMS'),
        getPropFull($iblockId, $item['ID'], $resolvedPropMap['Размер одежды']['identifier'] ?? 'SIZE'),
        getPropFull($iblockId, $item['ID'], $resolvedPropMap['Размер обуви']['identifier'] ?? 'SIZE_SHOE'),
        getPropFull($iblockId, $item['ID'], $resolvedPropMap['Рост']['identifier'] ?? 'ROST'),
        getPropFull($iblockId, $item['ID'], $resolvedPropMap['Цвет волос']['identifier'] ?? 'HAIR_COLOR'),
        getPropFull($iblockId, $item['ID'], $resolvedPropMap['Длина волос']['identifier'] ?? 'HAIR_LENGTH'),
        getPropFull($iblockId, $item['ID'], $resolvedPropMap['Иностранный язык']['identifier'] ?? 'LANG'),
        getPropFull($iblockId, $item['ID'], $resolvedPropMap['Опыт работы']['identifier'] ?? 'EXPERIENCE'),
    ];

    if (isset($googleMap[$item['ID']])) {
        $dataUpdate[$googleMap[$item['ID']]] = $row;
    } else {
        $dataAppend[] = $row;
    }
}

/* ================= Обновление существующих строк ================= */
foreach ($dataUpdate as $rowNumber => $rowData) {
    $service->spreadsheets_values->update(
        $spreadsheetId,
        $sheetName . '!A' . $rowNumber,
        new Google_Service_Sheets_ValueRange(['values' => [$rowData]]),
        ['valueInputOption' => 'RAW']
    );
}

/* ================= Добавление новых строк ================= */
if ($dataAppend) {
    $service->spreadsheets_values->append(
        $spreadsheetId,
        $sheetName . '!A2',
        new Google_Service_Sheets_ValueRange(['values' => $dataAppend]),
        ['valueInputOption' => 'RAW']
    );
}

/* ================= Защита заголовков ================= */
$sheet = $service->spreadsheets->get($spreadsheetId)->getSheets()[0];
$sheetId = $sheet->getProperties()->getSheetId();

$requests = [
    new Google_Service_Sheets_Request([
        'addProtectedRange' => [
            'protectedRange' => [
                'range' => [
                    'sheetId' => $sheetId,
                    'startRowIndex' => 0,
                    'endRowIndex' => 1,
                    'startColumnIndex' => 0,
                    'endColumnIndex' => 17,
                ],
                'description' => 'Защита заголовков',
                'warningOnly' => false,
            ],
        ],
    ]),
];

$batchUpdate = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest(['requests' => $requests]);
$service->spreadsheets->batchUpdate($spreadsheetId, $batchUpdate);

echo 'Готово. Обновлено: ' . count($dataUpdate) . ', добавлено: ' . count($dataAppend) . ', удалено: ' . count($idsToDelete)
    . (empty($errors) ? '' : '. Ошибки: ' . implode('; ', $errors));
