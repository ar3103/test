<?php

declare(strict_types=1);

use Bitrix\Main\Context;
use Local\Mailing\AnalyticsService;
use Local\Mailing\ShortLinkService;

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/lib/Mailing/ShortLinkService.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/lib/Mailing/AnalyticsService.php';

$siteId = (string)Context::getCurrent()->getSite();
$code = (string)Context::getCurrent()->getRequest()->get('code');

if ($code === '') {
    http_response_code(400);
    echo 'Empty short code';
    exit;
}

$link = ShortLinkService::resolve($siteId, $code);
if ($link === null) {
    http_response_code(404);
    echo 'Short link not found';
    exit;
}

ShortLinkService::registerClick($siteId, $code);
AnalyticsService::log($siteId, 'clicked', [
    'code' => $code,
    'contact_id' => $link['contact_id'] ?? null,
    'url' => $link['url'],
]);

LocalRedirect((string)$link['url']);
