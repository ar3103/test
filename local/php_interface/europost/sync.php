<?php

use Bitrix\Highloadblock as HL;
use Bitrix\Main\Loader;

/**
 * Endpoint с отделениями Европочты.
 * При необходимости замените на актуальный endpoint.
 */
define('EUROPOST_POINTS_URL', 'https://evropochta.by/api/points');

/**
 * @return array<int, array<string, mixed>>
 */
function europostFetchPointsFromApi()
{
    $json = @file_get_contents(EUROPOST_POINTS_URL);

    if (!$json && function_exists('curl_init')) {
        $ch = curl_init(EUROPOST_POINTS_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        $json = curl_exec($ch);
        curl_close($ch);
    }

    if (!$json) {
        return [];
    }

    $points = json_decode($json, true);
    return is_array($points) ? $points : [];
}

function europostSyncPoints()
{
    if (!Loader::includeModule('highloadblock')) {
        return;
    }

    $points = europostFetchPointsFromApi();
    if (empty($points)) {
        return;
    }

    $hlblock = HL\HighloadBlockTable::getList([
        'filter' => ['=NAME' => 'EuropostPoints'],
    ])->fetch();

    if (!$hlblock) {
        return;
    }

    $entity = HL\HighloadBlockTable::compileEntity($hlblock);
    $class = $entity->getDataClass();

    // Делаем все отделения неактивными. Актуальные вернем в UF_ACTIVE=1.
    $list = $class::getList(['select' => ['ID']]);
    while ($row = $list->fetch()) {
        $class::update($row['ID'], ['UF_ACTIVE' => 0]);
    }

    foreach ($points as $point) {
        if (empty($point['id'])) {
            continue;
        }

        $exists = $class::getList([
            'filter' => ['UF_XML_ID' => (string) $point['id']],
            'select' => ['ID'],
        ])->fetch();

        $fields = [
            'UF_XML_ID' => (string) $point['id'],
            'UF_NAME' => isset($point['name']) ? (string) $point['name'] : '',
            'UF_ADDRESS' => isset($point['address']) ? (string) $point['address'] : '',
            'UF_ACTIVE' => 1,
        ];

        if ($exists) {
            $class::update($exists['ID'], $fields);
        } else {
            $class::add($fields);
        }
    }
}
