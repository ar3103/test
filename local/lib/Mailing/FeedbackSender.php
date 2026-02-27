<?php

namespace Local\Mailing;

use CIBlockElement;

class FeedbackSender
{
    public static function sendForCompletedOrders($siteId, array $config)
    {
        $propertyCodes = $config['PROPERTY_CODES'];

        $filter = [
            'IBLOCK_ID' => (int) $config['ORDER_IBLOCK_ID'],
            '<=PROPERTY_' . $propertyCodes['COMPLETED_AT'] => ConvertTimeStamp(false, 'FULL'),
            [
                'LOGIC' => 'OR',
                '=PROPERTY_' . $propertyCodes['SENT'] => false,
                '=PROPERTY_' . $propertyCodes['SENT'] => '',
            ],
        ];

        $select = [
            'ID',
            'IBLOCK_ID',
            'NAME',
            'PROPERTY_' . $propertyCodes['EMAIL'],
            'PROPERTY_' . $propertyCodes['PHONE'],
            'PROPERTY_' . $propertyCodes['TELEGRAM'],
        ];

        $result = CIBlockElement::GetList(['ID' => 'ASC'], $filter, false, false, $select);

        while ($order = $result->GetNext()) {
            $link = LinkTracker::generate((int) $order['ID'], $config['SHORT_LINK_DOMAIN'], (int) $config['LINK_LOG_IBLOCK_ID']);
            $sent = false;

            if (!empty($order['PROPERTY_' . $propertyCodes['EMAIL'] . '_VALUE'])) {
                UserNotifier::sendEmail(
                    $siteId,
                    $order['PROPERTY_' . $propertyCodes['EMAIL'] . '_VALUE'],
                    $link,
                    (int) $config['EMAIL_TEMPLATE_ID']
                );
                $sent = true;
            }

            if (!empty($order['PROPERTY_' . $propertyCodes['PHONE'] . '_VALUE'])) {
                UserNotifier::sendSMS(
                    $order['PROPERTY_' . $propertyCodes['PHONE'] . '_VALUE'],
                    $link,
                    $config['SMS_PROVIDER'],
                    $config['SMS_API_KEY']
                );
                $sent = true;
            }

            if (!empty($order['PROPERTY_' . $propertyCodes['TELEGRAM'] . '_VALUE'])) {
                UserNotifier::sendTelegram(
                    $order['PROPERTY_' . $propertyCodes['TELEGRAM'] . '_VALUE'],
                    $link,
                    $config['TELEGRAM_BOT_TOKEN']
                );
                $sent = true;
            }

            if ($sent) {
                CIBlockElement::SetPropertyValuesEx(
                    (int) $order['ID'],
                    (int) $config['ORDER_IBLOCK_ID'],
                    [$propertyCodes['SENT'] => ConvertTimeStamp(false, 'FULL')]
                );
            }
        }
    }
}
