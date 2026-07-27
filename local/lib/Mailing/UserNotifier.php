<?php

namespace Local\Mailing;

use CEvent;

class UserNotifier
{
    public static function sendEmail($siteId, $email, $link, $templateId)
    {
        CEvent::Send(
            'FEEDBACK_REQUEST',
            $siteId,
            [
                'EMAIL' => $email,
                'LINK' => $link,
            ],
            'Y',
            $templateId
        );
    }

    public static function sendSMS($phone, $link, $provider, $apiKey)
    {
        $message = 'Спасибо за заказ! Оставьте отзыв: ' . $link;

        if ($provider === 'sms.ru') {
            $url = 'https://sms.ru/sms/send?api_id=' . urlencode($apiKey)
                . '&to=' . urlencode($phone)
                . '&msg=' . urlencode($message);
            file_get_contents($url);
        }
    }

    public static function sendTelegram($chatId, $link, $botToken)
    {
        $message = 'Спасибо за заказ! Оставьте отзыв: ' . $link;
        $url = 'https://api.telegram.org/bot' . urlencode($botToken)
            . '/sendMessage?chat_id=' . urlencode($chatId)
            . '&text=' . urlencode($message);

        file_get_contents($url);
    }
}
