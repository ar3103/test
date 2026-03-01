<?php

declare(strict_types=1);

namespace Company\ReviewSync\Notifier;

use Bitrix\Main\Mail\Event;
use Bitrix\Main\Web\HttpClient;
use Company\ReviewSync\Config\Option;
use Company\ReviewSync\DTO\ReviewItem;

final class NotificationService
{
    public function notifyNewReview(ReviewItem $item, int $elementId): void
    {
        $message = sprintf(
            "Новый отзыв (%s) отправлен на модерацию.\nID элемента: %d\nАвтор: %s\nОценка: %d\nТекст: %s",
            $item->platform,
            $elementId,
            $item->authorName,
            $item->rating,
            $item->text
        );

        $this->notifyByEmail($message);
        $this->notifyByTelegram($message);
    }

    private function notifyByEmail(string $message): void
    {
        $email = Option::getString(Option::NOTIFY_EMAIL);
        if ($email === '') {
            return;
        }

        Event::send([
            'EVENT_NAME' => 'REVIEW_ON_MODERATION',
            'LID' => SITE_ID,
            'C_FIELDS' => [
                'EMAIL_TO' => $email,
                'TEXT' => $message,
            ],
        ]);
    }

    private function notifyByTelegram(string $message): void
    {
        $token = Option::getString(Option::TELEGRAM_BOT_TOKEN);
        $chatId = Option::getString(Option::TELEGRAM_CHAT_ID);
        if ($token === '' || $chatId === '') {
            return;
        }

        $httpClient = new HttpClient(['socketTimeout' => 5, 'streamTimeout' => 5]);
        $httpClient->post(
            sprintf('https://api.telegram.org/bot%s/sendMessage', urlencode($token)),
            [
                'chat_id' => $chatId,
                'text' => $message,
                'disable_web_page_preview' => 'true',
            ]
        );
    }
}
