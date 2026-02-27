<?php

declare(strict_types=1);

namespace Local\Mailing;

use Bitrix\Main\Config\Option;

final class ShortLinkService
{
    private const MODULE_ID = 'local.mailing';

    public static function create(string $siteId, string $targetUrl, int $contactId): string
    {
        $hash = substr(md5($siteId . '|' . $targetUrl . '|' . $contactId . '|' . microtime(true)), 0, 10);
        $storage = self::storage($siteId);
        $storage[$hash] = [
            'url' => $targetUrl,
            'contact_id' => $contactId,
            'clicks' => 0,
            'last_click_at' => null,
        ];

        Option::set(self::MODULE_ID, 'short_links_' . $siteId, json_encode($storage, JSON_UNESCAPED_UNICODE));
        return $hash;
    }

    public static function resolve(string $siteId, string $code): ?array
    {
        $storage = self::storage($siteId);
        return $storage[$code] ?? null;
    }

    public static function registerClick(string $siteId, string $code): void
    {
        $storage = self::storage($siteId);
        if (!isset($storage[$code])) {
            return;
        }

        $storage[$code]['clicks'] = (int)$storage[$code]['clicks'] + 1;
        $storage[$code]['last_click_at'] = date('c');

        Option::set(self::MODULE_ID, 'short_links_' . $siteId, json_encode($storage, JSON_UNESCAPED_UNICODE));
    }

    public static function getStats(string $siteId): array
    {
        return self::storage($siteId);
    }

    private static function storage(string $siteId): array
    {
        $raw = (string)Option::get(self::MODULE_ID, 'short_links_' . $siteId, '{}');
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }
}
