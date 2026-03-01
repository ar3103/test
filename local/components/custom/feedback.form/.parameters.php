<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$arComponentParameters = [
    'GROUPS' => [
        'INTEGRATIONS' => [
            'NAME' => Loc::getMessage('CUSTOM_FEEDBACK_FORM_GROUP_INTEGRATIONS'),
        ],
        'CAPTCHA' => [
            'NAME' => Loc::getMessage('CUSTOM_FEEDBACK_FORM_GROUP_CAPTCHA'),
        ],
    ],
    'PARAMETERS' => [
        'IBLOCK_ID' => [
            'PARENT' => 'BASE',
            'NAME' => Loc::getMessage('CUSTOM_FEEDBACK_FORM_PARAM_IBLOCK_ID'),
            'TYPE' => 'STRING',
            'DEFAULT' => '',
        ],
        'TELEGRAM_BOT_TOKEN' => [
            'PARENT' => 'INTEGRATIONS',
            'NAME' => Loc::getMessage('CUSTOM_FEEDBACK_FORM_PARAM_TELEGRAM_BOT_TOKEN'),
            'TYPE' => 'STRING',
            'DEFAULT' => '',
        ],
        'TELEGRAM_CHAT_ID' => [
            'PARENT' => 'INTEGRATIONS',
            'NAME' => Loc::getMessage('CUSTOM_FEEDBACK_FORM_PARAM_TELEGRAM_CHAT_ID'),
            'TYPE' => 'STRING',
            'DEFAULT' => '',
        ],
        'MAIL_EVENT_NAME' => [
            'PARENT' => 'INTEGRATIONS',
            'NAME' => Loc::getMessage('CUSTOM_FEEDBACK_FORM_PARAM_MAIL_EVENT_NAME'),
            'TYPE' => 'STRING',
            'DEFAULT' => 'CUSTOM_FEEDBACK_FORM',
        ],
        'GOOGLE_SHEETS_WEBHOOK_URL' => [
            'PARENT' => 'INTEGRATIONS',
            'NAME' => Loc::getMessage('CUSTOM_FEEDBACK_FORM_PARAM_GOOGLE_SHEETS_WEBHOOK_URL'),
            'TYPE' => 'STRING',
            'DEFAULT' => '',
        ],
        'YANDEX_SMARTCAPTCHA_SITE_KEY' => [
            'PARENT' => 'CAPTCHA',
            'NAME' => Loc::getMessage('CUSTOM_FEEDBACK_FORM_PARAM_YANDEX_SMARTCAPTCHA_SITE_KEY'),
            'TYPE' => 'STRING',
            'DEFAULT' => '',
        ],
        'YANDEX_SMARTCAPTCHA_SECRET_KEY' => [
            'PARENT' => 'CAPTCHA',
            'NAME' => Loc::getMessage('CUSTOM_FEEDBACK_FORM_PARAM_YANDEX_SMARTCAPTCHA_SECRET_KEY'),
            'TYPE' => 'STRING',
            'DEFAULT' => '',
        ],
        'GOOGLE_RECAPTCHA_SITE_KEY' => [
            'PARENT' => 'CAPTCHA',
            'NAME' => Loc::getMessage('CUSTOM_FEEDBACK_FORM_PARAM_GOOGLE_RECAPTCHA_SITE_KEY'),
            'TYPE' => 'STRING',
            'DEFAULT' => '',
        ],
        'GOOGLE_RECAPTCHA_SECRET_KEY' => [
            'PARENT' => 'CAPTCHA',
            'NAME' => Loc::getMessage('CUSTOM_FEEDBACK_FORM_PARAM_GOOGLE_RECAPTCHA_SECRET_KEY'),
            'TYPE' => 'STRING',
            'DEFAULT' => '',
        ],
        'CACHE_TIME' => ['DEFAULT' => 3600],
    ],
];
