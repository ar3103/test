<?php

namespace Local\Mailing;

class Analytics
{
    public static function log(string $siteId, string $eventType, array $data, array $config): void
    {
        $iblockId = (int)($config['ANALYTICS']['IBLOCK_ID'] ?? 0);
        if ($iblockId <= 0) {
            return;
        }

        $element = new \CIBlockElement();
        $element->Add([
            'IBLOCK_ID' => $iblockId,
            'NAME' => $eventType . ' ' . date('d.m.Y H:i:s'),
            'ACTIVE' => 'Y',
            'PROPERTY_VALUES' => [
                'SITE_ID' => $siteId,
                'EVENT_TYPE' => $eventType,
                'PAYLOAD' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ],
        ]);
    }

    public static function markEmailOpen(string $siteId, int $userId, int $mailingId, array $config): void
    {
        self::log($siteId, 'email_open', ['user_id' => $userId, 'mailing_id' => $mailingId], $config);
    }

    public static function markClick(string $siteId, int $userId, string $url, array $config): void
    {
        self::log($siteId, 'email_click', ['user_id' => $userId, 'url' => $url], $config);
    }

    public static function markBounce(string $siteId, string $email, string $reason, array $config): void
    {
        self::log($siteId, 'email_bounce', ['email' => $email, 'reason' => $reason], $config);
    }
}
