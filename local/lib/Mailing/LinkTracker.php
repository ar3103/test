<?php

namespace Local\Mailing;

use CIBlockElement;

class LinkTracker
{
    public static function generate(int $orderId, array $config): string
    {
        $domain = rtrim((string) ($config['SHORT_LINK_DOMAIN'] ?? ''), '/');
        $hash = substr(hash('sha256', $orderId . '|' . microtime(true)), 0, 10);
        $shortUrl = $domain . '/?o=' . $orderId . '&h=' . $hash;

        if (!empty($config['LINK_LOG_IBLOCK_ID'])) {
            CIBlockElement::Add([
                'IBLOCK_ID' => (int) $config['LINK_LOG_IBLOCK_ID'],
                'NAME' => 'Order #' . $orderId,
                'PROPERTY_VALUES' => [
                    'ORDER_ID' => $orderId,
                    'SHORT_URL' => $shortUrl,
                    'HASH' => $hash,
                    'CREATED_AT' => date('d.m.Y H:i:s'),
                ],
            ]);
        }

        return $shortUrl;
    }

    public static function trackClick(int $orderId, string $hash, int $linkLogIblockId): void
    {
        $res = CIBlockElement::GetList(
            [],
            [
                'IBLOCK_ID' => $linkLogIblockId,
                'PROPERTY_ORDER_ID' => $orderId,
                'PROPERTY_HASH' => $hash,
            ],
            false,
            false,
            ['ID', 'PROPERTY_CLICKS']
        );

        if ($el = $res->GetNext()) {
            $clicks = (int) ($el['PROPERTY_CLICKS_VALUE'] ?? 0);
            CIBlockElement::SetPropertyValuesEx((int) $el['ID'], $linkLogIblockId, [
                'CLICKS' => $clicks + 1,
                'LAST_CLICK_AT' => date('d.m.Y H:i:s'),
            ]);
        }
    }
}
