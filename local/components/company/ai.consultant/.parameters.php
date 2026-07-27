<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$arComponentParameters = [
    'PARAMETERS' => [
        'DIALOG_IBLOCK_CODE' => [
            'PARENT' => 'BASE',
            'NAME' => 'Код инфоблока для диалогов',
            'TYPE' => 'STRING',
            'DEFAULT' => 'ai_dialogs',
        ],
        'FAQ_IBLOCK_CODE' => [
            'PARENT' => 'BASE',
            'NAME' => 'Код инфоблока готовых ответов',
            'TYPE' => 'STRING',
            'DEFAULT' => 'ai_faq',
        ],
        'TELEGRAM_CHAT_ID' => [
            'PARENT' => 'BASE',
            'NAME' => 'Telegram chat_id для уведомлений',
            'TYPE' => 'STRING',
            'DEFAULT' => '',
        ],
        'CACHE_TIME' => [
            'DEFAULT' => 3600,
        ],
    ],
];
