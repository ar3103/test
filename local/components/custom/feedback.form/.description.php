<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$arComponentDescription = [
    'NAME' => Loc::getMessage('CUSTOM_FEEDBACK_FORM_COMPONENT_NAME'),
    'DESCRIPTION' => Loc::getMessage('CUSTOM_FEEDBACK_FORM_COMPONENT_DESC'),
    'PATH' => [
        'ID' => 'custom',
        'NAME' => Loc::getMessage('CUSTOM_FEEDBACK_FORM_COMPONENT_PATH_NAME'),
    ],
];
