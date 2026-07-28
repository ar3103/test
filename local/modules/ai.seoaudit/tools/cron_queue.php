<?php

$_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/../../../../..');
$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

if (\Bitrix\Main\Loader::includeModule('ai.seoaudit')) {
    \Ai\SeoAudit\Application\QueueService::processDueTasks(200);
}
