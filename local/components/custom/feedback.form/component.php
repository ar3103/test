<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Loader;

if (!Loader::includeModule('iblock')) {
    ShowError('Module iblock is required');
    return;
}

$arResult['SUCCESS'] = false;
$arResult['ERRORS'] = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_bitrix_sessid()) {
    $name = trim((string) ($_POST['NAME'] ?? ''));
    $email = trim((string) ($_POST['EMAIL'] ?? ''));
    $text = trim((string) ($_POST['TEXT'] ?? ''));
    $rating = (int) ($_POST['RATING'] ?? 0);

    if ($name === '') {
        $arResult['ERRORS'][] = 'Укажите имя';
    }

    if ($email === '' || !check_email($email)) {
        $arResult['ERRORS'][] = 'Укажите корректный Email';
    }

    if ($text === '') {
        $arResult['ERRORS'][] = 'Заполните текст отзыва';
    }

    if ($rating < 1 || $rating > 5) {
        $arResult['ERRORS'][] = 'Оценка должна быть от 1 до 5';
    }

    if (empty($arResult['ERRORS'])) {
        $el = new CIBlockElement();
        $saved = $el->Add([
            'IBLOCK_ID' => (int) ($arParams['IBLOCK_ID'] ?? 0),
            'NAME' => $name,
            'ACTIVE' => 'N',
            'PROPERTY_VALUES' => [
                'EMAIL' => $email,
                'TEXT' => $text,
                'RATING' => $rating,
            ],
        ]);

        if ($saved) {
            $arResult['SUCCESS'] = true;
        } else {
            $arResult['ERRORS'][] = (string) $el->LAST_ERROR;
        }
    }
}

$this->IncludeComponentTemplate();
