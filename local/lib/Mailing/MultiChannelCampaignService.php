<?php

declare(strict_types=1);

namespace Local\Mailing;

use Bitrix\Main\Web\HttpClient;

final class MultiChannelCampaignService
{
    public static function runCampaign(array $config, array $contact, array $payload): void
    {
        self::sendEmail($config, $contact, $payload);
        self::sendSms($config, $contact, $payload);
        self::sendTelegram($config, $contact, $payload);
    }

    private static function sendEmail(array $config, array $contact, array $payload): void
    {
        if (empty($contact['EMAIL']) || empty($config['EVENTS']['MULTI_CHANNEL'])) {
            return;
        }

        \CEvent::Send(
            $config['EVENTS']['MULTI_CHANNEL'],
            $config['SITE_ID'],
            [
                'EMAIL_TO' => $contact['EMAIL'],
                'NAME' => (string)($contact['NAME'] ?? ''),
                'MESSAGE' => (string)($payload['message'] ?? ''),
                'SHORT_URL' => (string)($payload['short_url'] ?? ''),
            ]
        );
    }

    private static function sendSms(array $config, array $contact, array $payload): void
    {
        if (empty($contact['PHONE']) || empty($config['SMS']['WEBHOOK'])) {
            return;
        }

        $client = new HttpClient();
        $client->setHeader('Content-Type', 'application/json');
        $client->post($config['SMS']['WEBHOOK'], json_encode([
            'phone' => $contact['PHONE'],
            'text' => (string)($payload['sms'] ?? $payload['message'] ?? ''),
        ], JSON_UNESCAPED_UNICODE));
    }

    private static function sendTelegram(array $config, array $contact, array $payload): void
    {
        if (empty($contact['TELEGRAM_CHAT_ID']) || empty($config['TELEGRAM']['BOT_TOKEN'])) {
            return;
        }

        $url = sprintf(
            'https://api.telegram.org/bot%s/sendMessage',
            $config['TELEGRAM']['BOT_TOKEN']
        );

        $client = new HttpClient();
        $client->post($url, [
            'chat_id' => $contact['TELEGRAM_CHAT_ID'],
            'text' => (string)($payload['telegram'] ?? $payload['message'] ?? ''),
        ]);
    }
}
