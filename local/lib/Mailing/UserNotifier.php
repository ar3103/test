<?php

namespace Local\Mailing;

use CEvent;

class UserNotifier
{
    public static function sendEmail(string $siteId, string $email, string $link, array $config): void
    {
        $eventName = $config['FEEDBACK_EVENT_NAME'] ?? 'FEEDBACK_REQUEST';
        $templateId = $config['EMAIL_TEMPLATE_ID'] ?? '';

        CEvent::Send(
            $eventName,
            $siteId,
            [
                'EMAIL' => $email,
                'LINK' => $link,
            ],
            'Y',
            $templateId
        );
    }

    public static function sendSMS(string $phone, string $link, array $config): void
    {
        if (($config['SMS_PROVIDER'] ?? '') !== 'sms.ru' || empty($config['SMS_API_KEY'])) {
            return;
        }

        $query = http_build_query([
            'api_id' => $config['SMS_API_KEY'],
            'to' => $phone,
            'msg' => 'Спасибо за заказ! Оставьте отзыв: ' . $link,
        ]);

        @file_get_contents('https://sms.ru/sms/send?' . $query);
    }

    public static function sendTelegram(string $chatId, string $link, array $config): void
    {
        if (empty($config['TELEGRAM_BOT_TOKEN'])) {
            return;
        }

        $query = http_build_query([
            'chat_id' => $chatId,
            'text' => 'Спасибо за заказ! Оставьте отзыв: ' . $link,
        ]);

        @file_get_contents('https://api.telegram.org/bot' . $config['TELEGRAM_BOT_TOKEN'] . '/sendMessage?' . $query);
    }
}
