<?php

declare(strict_types=1);

namespace Local\Mailing;

use Bitrix\Main\Context;
use RuntimeException;

final class ConfigResolver
{
    public static function forSite(?string $siteId = null): array
    {
        $siteId = $siteId ?: (string)Context::getCurrent()->getSite();
        $path = $_SERVER['DOCUMENT_ROOT'] . '/local/sites/' . $siteId . '/mailing.php';

        if (!is_file($path)) {
            throw new RuntimeException('Не найден site-specific конфиг рассылок: ' . $path);
        }

        $config = require $path;
        if (!is_array($config)) {
            throw new RuntimeException('Конфиг рассылок должен возвращать массив: ' . $path);
        }

        $config['SITE_ID'] = $siteId;
        return $config;
    }
}
