<?php

define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main\Context;
use Bitrix\Main\Web\Json;
use Local\AiConsultant\Service;

$request = Context::getCurrent()->getRequest();
$action = (string) $request->getPost('action');
$siteId = (string) $request->getPost('siteId');

if ($siteId === '') {
    $siteId = SITE_ID;
}

$result = ['ok' => false, 'error' => 'Unknown action'];

if ($action === 'chat') {
    $history = Json::decode((string) $request->getPost('history') ?: '[]');
    $message = (string) $request->getPost('message');
    $result = Service::chat($siteId, (array) $history, $message);
}

if ($action === 'save') {
    $payload = Json::decode((string) $request->getPost('payload') ?: '{}');
    $result = Service::saveDialog($siteId, (array) $payload);
}

header('Content-Type: application/json; charset=UTF-8');
echo Json::encode($result);
