<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Loader;
use Bitrix\Main\Context;

if (!Loader::includeModule('iblock')) {
    return;
}

$iblockId = 30;
$request = Context::getCurrent()->getRequest();
$requestSiteId = trim((string) $request->getQuery('SITE_ID'));
$paramSiteId = trim((string) ($arParams['SITE_ID'] ?? ''));
$siteFilter = $requestSiteId !== '' ? $requestSiteId : ($paramSiteId !== '' ? $paramSiteId : SITE_ID);

$res = CIBlockElement::GetList(
    ['PROPERTY_CREATED_AT' => 'DESC'],
    [
        'IBLOCK_ID' => $iblockId,
        'PROPERTY_SITE_ID' => $siteFilter,
    ],
    false,
    false,
    [
        'ID',
        'NAME',
        'PROPERTY_ORDER_ID',
        'PROPERTY_SHORT_URL',
        'PROPERTY_CLICKS',
        'PROPERTY_FILLED',
        'PROPERTY_CREATED_AT',
        'PROPERTY_SITE_ID',
    ]
);

$stats = [];
while ($el = $res->GetNext()) {
    $stats[] = [
        'ORDER_ID' => $el['PROPERTY_ORDER_ID_VALUE'],
        'SHORT_URL' => $el['PROPERTY_SHORT_URL_VALUE'],
        'CLICKS' => (int) $el['PROPERTY_CLICKS_VALUE'],
        'FILLED' => (int) $el['PROPERTY_FILLED_VALUE'],
        'CREATED_AT' => $el['PROPERTY_CREATED_AT_VALUE'],
        'SITE_ID' => $el['PROPERTY_SITE_ID_VALUE'],
    ];
}

$this->arResult['STATS'] = $stats;
$this->arResult['SITE_ID'] = $siteFilter;

$this->IncludeComponentTemplate();
