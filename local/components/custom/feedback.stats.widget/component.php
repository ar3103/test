<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Iblock\ElementTable;
use CIBlockElement;
use Bitrix\Main\Loader;

Loader::includeModule("iblock");

// ID инфоблока с логами ссылок
$iblockId = 30;

// Получаем выбранный сайт для фильтра (по умолчанию текущий)
$siteFilter = $arParams['SITE_ID'] ?? SITE_ID;

$res = CIBlockElement::GetList(
    ["PROPERTY_CREATED_AT" => "DESC"],
    [
        "IBLOCK_ID" => $iblockId,
        "PROPERTY_SITE_ID" => $siteFilter // фильтр по сайту
    ],
    false, false,
    ["ID","NAME","PROPERTY_ORDER_ID","PROPERTY_SHORT_URL","PROPERTY_CLICKS","PROPERTY_FILLED","PROPERTY_CREATED_AT","PROPERTY_SITE_ID"]
);

$stats = [];
while ($el = $res->GetNext()) {
    $stats[] = [
        "ORDER_ID" => $el["PROPERTY_ORDER_ID_VALUE"],
        "SHORT_URL" => $el["PROPERTY_SHORT_URL_VALUE"],
        "CLICKS" => intval($el["PROPERTY_CLICKS_VALUE"]),
        "FILLED" => intval($el["PROPERTY_FILLED_VALUE"]),
        "CREATED_AT" => $el["PROPERTY_CREATED_AT_VALUE"],
        "SITE_ID" => $el["PROPERTY_SITE_ID_VALUE"]
    ];
}

$this->arResult['STATS'] = $stats;
$this->arResult['SITE_ID'] = $siteFilter;

$this->IncludeComponentTemplate();
?>
