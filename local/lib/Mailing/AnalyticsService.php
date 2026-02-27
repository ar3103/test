<?php

declare(strict_types=1);

namespace Local\Mailing;

use Bitrix\Main\Config\Option;

final class AnalyticsService
{
    private const MODULE_ID = 'local.mailing';

    public static function log(string $siteId, string $type, array $data = []): void
    {
        $key = 'analytics_' . $siteId;
        $stats = self::read($siteId);
        $stats[] = [
            'type' => $type,
            'data' => $data,
            'at' => date('c'),
        ];

        Option::set(self::MODULE_ID, $key, json_encode($stats, JSON_UNESCAPED_UNICODE));
    }

    public static function report(string $siteId): array
    {
        $stats = self::read($siteId);
        $totals = ['sent' => 0, 'opened' => 0, 'clicked' => 0, 'bounced' => 0];

        foreach ($stats as $row) {
            if (isset($totals[$row['type']])) {
                $totals[$row['type']]++;
            }
        }

        $openRate = $totals['sent'] > 0 ? round($totals['opened'] / $totals['sent'] * 100, 2) : 0.0;
        $ctr = $totals['sent'] > 0 ? round($totals['clicked'] / $totals['sent'] * 100, 2) : 0.0;
        $bounceRate = $totals['sent'] > 0 ? round($totals['bounced'] / $totals['sent'] * 100, 2) : 0.0;

        return [
            'totals' => $totals,
            'open_rate' => $openRate,
            'ctr' => $ctr,
            'bounce_rate' => $bounceRate,
        ];
    }

    private static function read(string $siteId): array
    {
        $raw = (string)Option::get(self::MODULE_ID, 'analytics_' . $siteId, '[]');
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }
}
