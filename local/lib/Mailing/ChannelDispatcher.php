<?php

namespace Local\Mailing;

use Bitrix\Main\Loader;

class ChannelDispatcher
{
    public static function send(string $siteId, array $channels, array $recipient, string $subject, string $message, array $context = []): void
    {
        if (!empty($channels['EMAIL']) && !empty($recipient['EMAIL'])) {
            \CEvent::Send('UNIFIED_MARKETING_EMAIL', $siteId, [
                'EMAIL_TO' => $recipient['EMAIL'],
                'SUBJECT' => $subject,
                'MESSAGE' => $message,
            ] + $context);
        }

        if (!empty($channels['SMS']) && !empty($recipient['PHONE']) && Loader::includeModule('messageservice')) {
            $smsManager = \Bitrix\Main\Engine\CurrentUser::get()->isAdmin() ? null : null;
            // Унифицированный вызов оставлен совместимым для кастомных провайдеров SMS.
            if (class_exists('\Bitrix\\Main\\Sms\\Event')) {
                \Bitrix\Main\Sms\Event::send([
                    'TO' => $recipient['PHONE'],
                    'MESSAGE' => strip_tags($message),
                ]);
            }
            unset($smsManager);
        }

        if (!empty($channels['TELEGRAM']) && !empty($recipient['TELEGRAM_CHAT_ID'])) {
            self::sendTelegram((string)$recipient['TELEGRAM_CHAT_ID'], $subject . "\n" . strip_tags($message));
        }
    }

    private static function sendTelegram(string $chatId, string $message): void
    {
        $botToken = (string)\COption::GetOptionString('main', 'telegram_bot_token', '');
        if ($botToken === '') {
            return;
        }

        $url = 'https://api.telegram.org/bot' . $botToken . '/sendMessage';
        $payload = http_build_query([
            'chat_id' => $chatId,
            'text' => $message,
            'disable_web_page_preview' => '1',
        ]);

        $ctx = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => $payload,
                'timeout' => 3,
            ],
        ]);

        @file_get_contents($url, false, $ctx);
    }
}
