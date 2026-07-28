<?php

declare(strict_types=1);

use Local\Integration\OrderGoogleSheetSync;

require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/lib/OrderGoogleSheetSync.php';

/**
 * Агент для запуска раз в N минут через cron/агенты.
 */
function SyncOrdersWithGoogleSheetAgent(): string
{
    $sync = new OrderGoogleSheetSync(
        $_SERVER['DOCUMENT_ROOT'] . '/local/secrets/google-service-account.json',
        'PUT_YOUR_SPREADSHEET_ID_HERE',
        'Orders'
    );

    $sync->sync();

    return __FUNCTION__ . '();';
}
