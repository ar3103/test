<?php

use Bitrix\Highloadblock as HL;
use Bitrix\Main\Loader;

/**
 * @return array<int, array<string, mixed>>
 */
function europostGetPoints()
{
    $result = [];

    if (!Loader::includeModule('highloadblock')) {
        return $result;
    }

    $hlblock = HL\HighloadBlockTable::getList([
        'filter' => ['=NAME' => 'EuropostPoints'],
    ])->fetch();

    if (!$hlblock) {
        return $result;
    }

    $entity = HL\HighloadBlockTable::compileEntity($hlblock);
    $class = $entity->getDataClass();

    $list = $class::getList([
        'filter' => ['UF_ACTIVE' => 1],
        'order' => ['UF_NAME' => 'ASC'],
    ]);

    while ($row = $list->fetch()) {
        $result[] = $row;
    }

    return $result;
}
