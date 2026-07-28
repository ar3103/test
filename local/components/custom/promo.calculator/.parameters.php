<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$arComponentParameters = [
    'PARAMETERS' => [
        'IBLOCK_ID' => [
            'PARENT' => 'BASE',
            'NAME' => Loc::getMessage('PROMO_CALCULATOR_PARAM_IBLOCK_ID'),
            'TYPE' => 'STRING',
            'DEFAULT' => '',
        ],
        'EMAIL_TO' => [
            'PARENT' => 'BASE',
            'NAME' => Loc::getMessage('PROMO_CALCULATOR_PARAM_EMAIL_TO'),
            'TYPE' => 'STRING',
            'DEFAULT' => '',
        ],
        'TELEGRAM_BOT_TOKEN' => [
            'PARENT' => 'BASE',
            'NAME' => Loc::getMessage('PROMO_CALCULATOR_PARAM_TELEGRAM_BOT_TOKEN'),
            'TYPE' => 'STRING',
            'DEFAULT' => '',
        ],
        'TELEGRAM_CHAT_ID' => [
            'PARENT' => 'BASE',
            'NAME' => Loc::getMessage('PROMO_CALCULATOR_PARAM_TELEGRAM_CHAT_ID'),
            'TYPE' => 'STRING',
            'DEFAULT' => '',
        ],
        'MANAGEMENT_PERCENT' => [
            'PARENT' => 'BASE',
            'NAME' => Loc::getMessage('PROMO_CALCULATOR_PARAM_MANAGEMENT_PERCENT'),
            'TYPE' => 'STRING',
            'DEFAULT' => '15',
        ],


        'EXCEL_CONTACT_WEBSITE' => [
            'PARENT' => 'BASE',
            'NAME' => Loc::getMessage('PROMO_CALCULATOR_PARAM_EXCEL_CONTACT_WEBSITE'),
            'TYPE' => 'STRING',
            'DEFAULT' => Loc::getMessage('PROMO_CALCULATOR_DEFAULT_EXCEL_CONTACT_WEBSITE'),
        ],
        'EXCEL_CONTACT_EMAIL' => [
            'PARENT' => 'BASE',
            'NAME' => Loc::getMessage('PROMO_CALCULATOR_PARAM_EXCEL_CONTACT_EMAIL'),
            'TYPE' => 'STRING',
            'DEFAULT' => Loc::getMessage('PROMO_CALCULATOR_DEFAULT_EXCEL_CONTACT_EMAIL'),
        ],
        'EXCEL_CONTACT_PHONE' => [
            'PARENT' => 'BASE',
            'NAME' => Loc::getMessage('PROMO_CALCULATOR_PARAM_EXCEL_CONTACT_PHONE'),
            'TYPE' => 'STRING',
            'DEFAULT' => Loc::getMessage('PROMO_CALCULATOR_DEFAULT_EXCEL_CONTACT_PHONE'),
        ],
        'EXCEL_COMPANY_NAME' => [
            'PARENT' => 'BASE',
            'NAME' => Loc::getMessage('PROMO_CALCULATOR_PARAM_EXCEL_COMPANY_NAME'),
            'TYPE' => 'STRING',
            'DEFAULT' => Loc::getMessage('PROMO_CALCULATOR_DEFAULT_EXCEL_COMPANY_NAME'),
        ],
        'COSTS_IBLOCK_ID' => [
            'PARENT' => 'BASE',
            'NAME' => Loc::getMessage('PROMO_CALCULATOR_PARAM_COSTS_IBLOCK_ID'),
            'TYPE' => 'STRING',
            'DEFAULT' => '',
        ],
        'PROMO_TYPES' => [
            'PARENT' => 'BASE',
            'NAME' => Loc::getMessage('PROMO_CALCULATOR_PARAM_PROMO_TYPES'),
            'TYPE' => 'CUSTOM',
            'JS_FILE' => '',
            'JS_EVENT' => '',
            'JS_DATA' => '',
            'DEFAULT' => [
                Loc::getMessage('PROMO_CALCULATOR_DEFAULT_PROMO_TYPE_1') => 900,
                Loc::getMessage('PROMO_CALCULATOR_DEFAULT_PROMO_TYPE_2') => 1200,
                Loc::getMessage('PROMO_CALCULATOR_DEFAULT_PROMO_TYPE_3') => 1800,
            ],
        ],

        'REQUIRED_STAFF_OPTIONS' => [
            'PARENT' => 'BASE',
            'NAME' => Loc::getMessage('PROMO_CALCULATOR_PARAM_REQUIRED_STAFF_OPTIONS'),
            'TYPE' => 'CUSTOM',
            'JS_FILE' => '',
            'JS_EVENT' => '',
            'JS_DATA' => '',
            'DEFAULT' => [
                Loc::getMessage('PROMO_CALCULATOR_DEFAULT_REQUIRED_STAFF_1'),
                Loc::getMessage('PROMO_CALCULATOR_DEFAULT_REQUIRED_STAFF_2'),
                Loc::getMessage('PROMO_CALCULATOR_DEFAULT_REQUIRED_STAFF_3'),
                Loc::getMessage('PROMO_CALCULATOR_DEFAULT_REQUIRED_STAFF_4'),
            ],
        ],
    ],
];
