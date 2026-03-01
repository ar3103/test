<?php

namespace Local\Mailing;

use CIBlockElement;

class FeedbackSender
{
    public static function sendForCompletedOrders(string $siteId, array $config): void
    {
        if (empty($config['ORDER_IBLOCK_ID'])) {
            return;
        }

        $res = CIBlockElement::GetList(
            ['DATE_ACTIVE_FROM' => 'ASC'],
            [
                'IBLOCK_ID' => (int) $config['ORDER_IBLOCK_ID'],
                '<=PROPERTY_DATE_SERVICE_COMPLETED' => date('d.m.Y H:i:s'),
                'PROPERTY_FEEDBACK_SENT' => false,
            ],
            false,
            false,
            [
                'ID',
                'IBLOCK_ID',
                'NAME',
                'PROPERTY_CLIENT_EMAIL',
                'PROPERTY_CLIENT_PHONE',
                'PROPERTY_CLIENT_TELEGRAM_CHAT_ID',
            ]
        );

        while ($order = $res->GetNext()) {
            $channelsUsed = false;
            $link = LinkTracker::generate((int) $order['ID'], $config);

            if (!empty($order['PROPERTY_CLIENT_EMAIL_VALUE'])) {
                UserNotifier::sendEmail($siteId, $order['PROPERTY_CLIENT_EMAIL_VALUE'], $link, $config);
                $channelsUsed = true;
            }

            if (!empty($order['PROPERTY_CLIENT_PHONE_VALUE'])) {
                UserNotifier::sendSMS($order['PROPERTY_CLIENT_PHONE_VALUE'], $link, $config);
                $channelsUsed = true;
            }

            if (!empty($order['PROPERTY_CLIENT_TELEGRAM_CHAT_ID_VALUE'])) {
                UserNotifier::sendTelegram($order['PROPERTY_CLIENT_TELEGRAM_CHAT_ID_VALUE'], $link, $config);
                $channelsUsed = true;
            }

            if ($channelsUsed) {
                CIBlockElement::SetPropertyValuesEx((int) $order['ID'], (int) $config['ORDER_IBLOCK_ID'], [
                    'FEEDBACK_SENT' => 'Y',
                    'FEEDBACK_SENT_AT' => date('d.m.Y H:i:s'),
                ]);
            }
        }
    }
}
