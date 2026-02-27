<?php

declare(strict_types=1);

use Local\Mailing\AnalyticsService;

require_once __DIR__ . '/../local/lib/Mailing/AnalyticsService.php';

$siteId = $argv[1] ?? 's1';
$report = AnalyticsService::report($siteId);

echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
