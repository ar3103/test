<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/europost/points.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/europost/sync.php';

// Заполняем значения свойства заказа EUROPOST_POINT актуальными отделениями Европочты.
AddEventHandler('sale', 'OnSaleComponentOrderProperties', static function (&$arFields) {
    if (empty($arFields['ORDER_PROP']['USER_PROPS_Y']) || !is_array($arFields['ORDER_PROP']['USER_PROPS_Y'])) {
        return;
    }

    $points = europostGetPoints();

    foreach ($arFields['ORDER_PROP']['USER_PROPS_Y'] as &$prop) {
        if (!isset($prop['CODE']) || $prop['CODE'] !== 'EUROPOST_POINT') {
            continue;
        }

        $prop['VALUES'] = [];

        foreach ($points as $point) {
            $prop['VALUES'][] = [
                'VALUE' => (string) $point['UF_XML_ID'],
                'NAME' => trim((string) $point['UF_NAME'] . ' — ' . (string) $point['UF_ADDRESS']),
            ];
        }
    }
    unset($prop);
});
