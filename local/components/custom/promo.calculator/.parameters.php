<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$arComponentParameters = [
    'PARAMETERS' => [
        'IBLOCK_ID' => [
            'PARENT' => 'BASE',
            'NAME' => 'ID инфоблока заявок',
            'TYPE' => 'STRING',
            'DEFAULT' => '',
        ],
        'EMAIL_TO' => [
            'PARENT' => 'BASE',
            'NAME' => 'Email получателя',
            'TYPE' => 'STRING',
            'DEFAULT' => '',
        ],
        'TELEGRAM_BOT_TOKEN' => [
            'PARENT' => 'BASE',
            'NAME' => 'Telegram Bot Token',
            'TYPE' => 'STRING',
            'DEFAULT' => '',
        ],
        'TELEGRAM_CHAT_ID' => [
            'PARENT' => 'BASE',
            'NAME' => 'Telegram Chat ID',
            'TYPE' => 'STRING',
            'DEFAULT' => '',
        ],
        'MANAGEMENT_PERCENT' => [
            'PARENT' => 'BASE',
            'NAME' => 'Процент менеджмента',
            'TYPE' => 'STRING',
            'DEFAULT' => '15',
        ],

        'COSTS_IBLOCK_ID' => [
            'PARENT' => 'BASE',
            'NAME' => 'ID инфоблока "Стоимость" (для списка требуемого персонала)',
            'TYPE' => 'STRING',
            'DEFAULT' => '',
        ],
        'PROMO_TYPES' => [
            'PARENT' => 'BASE',
            'NAME' => 'Типы промо (массив: название => ставка/день)',
            'TYPE' => 'CUSTOM',
            'JS_FILE' => '',
            'JS_EVENT' => '',
            'JS_DATA' => '',
            'DEFAULT' => [
                'Промоутер без особых требований' => 900,
                'Промоутер с опытом' => 1200,
                'Супервайзер' => 1800,
            ],
        ],

        'REQUIRED_STAFF_OPTIONS' => [
            'PARENT' => 'BASE',
            'NAME' => 'Варианты требуемого персонала',
            'TYPE' => 'CUSTOM',
            'JS_FILE' => '',
            'JS_EVENT' => '',
            'JS_DATA' => '',
            'DEFAULT' => [
                'Промоутер',
                'Промоутер с опытом продаж',
                'Супервайзер',
                'Консультант',
            ],
        ],
    ],
];
