<?php

namespace Local\AiConsultant;

use CEvent;

class NotificationService
{
    public function sendTelegram(string $token, string $chatId, string $message): void
    {
        if ($token === '' || $chatId === '') {
            return;
        }

        $url = 'https://api.telegram.org/bot' . $token . '/sendMessage';
        $payload = http_build_query([
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'HTML',
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
        ]);
        curl_exec($ch);
        curl_close($ch);
    }

    public function sendEmail(string $siteId, string $emailTo, array $lead): void
    {
        if ($emailTo === '') {
            return;
        }

        CEvent::Send('AI_CONSULTANT_LEAD', $siteId, [
            'EMAIL_TO' => $emailTo,
            'CLIENT_NAME' => $lead['name'] ?? '',
            'PHONE' => $lead['phone'] ?? '',
            'INTEREST' => $lead['interest'] ?? '',
            'HISTORY' => $lead['history_text'] ?? '',
        ]);
    }
}
