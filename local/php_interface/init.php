<?php

declare(strict_types=1);

use MultiRegion\RegionManager;

spl_autoload_register(static function (string $class): void {
    $prefix = 'MultiRegion\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = str_replace($prefix, '', $class);
    $file = __DIR__ . '/classes/MultiRegion/' . $relative . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});

$regionManager = new RegionManager();

$geoCountryCode = $_SERVER['HTTP_X_COUNTRY_CODE'] ?? null;
$context = $regionManager->bootstrap($_SERVER, $_COOKIE, $geoCountryCode);

$GLOBALS['SITE_REGION_CONTEXT'] = $context;
