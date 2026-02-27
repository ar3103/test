<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Loader;

if (!Loader::includeModule('iblock')) {
    return;
}

$arIBlocks = [];
$rsIBlock = CIBlock::GetList(['SORT' => 'ASC'], ['ACTIVE' => 'Y']);
while ($arIBlock = $rsIBlock->Fetch()) {
    $arIBlocks[$arIBlock['ID']] = '[' . $arIBlock['ID'] . '] ' . $arIBlock['NAME'];
}

$arComponentParameters = [
    'PARAMETERS' => [
        'IBLOCK_ID' => [
            'PARENT' => 'BASE',
            'NAME' => GetMessage('ACME_LEAD_FORM_PARAM_IBLOCK_ID'),
            'TYPE' => 'LIST',
            'VALUES' => $arIBlocks,
            'ADDITIONAL_VALUES' => 'Y',
            'REFRESH' => 'N',
        ],
        'TELEGRAM_BOT_TOKEN' => [
            'PARENT' => 'BASE',
            'NAME' => GetMessage('ACME_LEAD_FORM_PARAM_TELEGRAM_TOKEN'),
            'TYPE' => 'STRING',
            'DEFAULT' => '',
        ],
        'TELEGRAM_CHAT_ID' => [
            'PARENT' => 'BASE',
            'NAME' => GetMessage('ACME_LEAD_FORM_PARAM_TELEGRAM_CHAT_ID'),
            'TYPE' => 'STRING',
            'DEFAULT' => '',
        ],
        'YANDEX_SMARTCAPTCHA_SECRET_KEY' => [
            'PARENT' => 'BASE',
            'NAME' => GetMessage('ACME_LEAD_FORM_PARAM_YANDEX_SECRET'),
            'TYPE' => 'STRING',
            'DEFAULT' => '',
        ],
        'GOOGLE_RECAPTCHA_SECRET_KEY' => [
            'PARENT' => 'BASE',
            'NAME' => GetMessage('ACME_LEAD_FORM_PARAM_GOOGLE_SECRET'),
            'TYPE' => 'STRING',
            'DEFAULT' => '',
        ],
        'GOOGLE_SHEETS_WEBHOOK_URL' => [
            'PARENT' => 'BASE',
            'NAME' => GetMessage('ACME_LEAD_FORM_PARAM_SHEETS_URL'),
            'TYPE' => 'STRING',
            'DEFAULT' => '',
        ],
        'EMAIL_TO' => [
            'PARENT' => 'BASE',
            'NAME' => GetMessage('ACME_LEAD_FORM_PARAM_EMAIL_TO'),
            'TYPE' => 'STRING',
            'DEFAULT' => COption::GetOptionString('main', 'email_from', ''),
        ],
        'CACHE_TIME' => ['DEFAULT' => 0],
    ],
];
