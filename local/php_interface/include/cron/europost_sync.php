<?php

declare(strict_types=1);

$_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/../../../..');

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
require $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/europost_sync.php';

try {
    $stats = EuropostSyncService::sync();
    echo sprintf("[OK] Загружено: %d, активно: %d\n", $stats['loaded'], $stats['active']);
} catch (Throwable $e) {
    echo '[ERROR] ' . $e->getMessage() . PHP_EOL;
    exit(1);
}
