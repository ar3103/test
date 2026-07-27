<?php

declare(strict_types=1);

use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;

Loader::includeModule('company.reviewsync');

$eventManager = EventManager::getInstance();
$eventManager->addEventHandler(
    'iblock',
    'OnAfterIBlockElementUpdate',
    ['Company\\ReviewSync\\Event\\ReviewPublishHandler', 'onAfterIBlockElementUpdate']
);
