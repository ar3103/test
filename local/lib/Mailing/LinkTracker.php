<?php

namespace Local\Mailing;

use CIBlockElement;

class LinkTracker
{
    public static function generate($orderId, $domain, $logIblockId)
    {
        $hash = substr(md5($orderId . '|' . microtime(true)), 0, 8);
        $shortUrl = rtrim($domain, '/') . '/?o=' . $orderId . '&h=' . $hash;

        $element = new CIBlockElement();
        $element->Add([
            'IBLOCK_ID' => $logIblockId,
            'NAME' => 'Order #' . $orderId,
            'ACTIVE' => 'Y',
            'PROPERTY_VALUES' => [
                'ORDER_ID' => $orderId,
                'SHORT_URL' => $shortUrl,
                'HASH' => $hash,
                'CREATED_AT' => ConvertTimeStamp(false, 'FULL'),
                'CLICKS' => 0,
            ],
        ]);

        return $shortUrl;
    }

    public static function trackClick($orderId, $hash, $logIblockId)
    {
        $result = CIBlockElement::GetList([], [
            'IBLOCK_ID' => $logIblockId,
            'PROPERTY_ORDER_ID' => $orderId,
            'PROPERTY_HASH' => $hash,
        ], false, false, ['ID', 'IBLOCK_ID', 'PROPERTY_CLICKS']);

        if ($row = $result->GetNext()) {
            $clicks = (int) $row['PROPERTY_CLICKS_VALUE'];
            CIBlockElement::SetPropertyValuesEx((int) $row['ID'], $logIblockId, ['CLICKS' => $clicks + 1]);
        }
    }
}
