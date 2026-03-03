<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$arComponentDescription = [
    'NAME' => Loc::getMessage('PROMO_CALCULATOR_COMPONENT_NAME'),
    'DESCRIPTION' => Loc::getMessage('PROMO_CALCULATOR_COMPONENT_DESCRIPTION'),
    'PATH' => [
        'ID' => 'custom',
        'NAME' => Loc::getMessage('PROMO_CALCULATOR_COMPONENT_PATH_NAME'),
    ],
];
