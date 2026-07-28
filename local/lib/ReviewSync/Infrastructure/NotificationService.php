<?php

declare(strict_types=1);

namespace Company\ReviewSync\Infrastructure;

use Bitrix\Main\Mail\Event;
use Bitrix\Main\Web\HttpClient;
use Company\ReviewSync\Config;
use Company\ReviewSync\Domain\ReviewDto;

final class NotificationService
{
    public function notifyAboutModeration(ReviewDto $review, int $elementId): void
    {
        $message = sprintf(
            "Новый отзыв из %s ожидает модерации. ID элемента: %d, автор: %s, рейтинг: %d\n%s",
            strtoupper($review->provider),
            $elementId,
            $review->authorName,
            $review->rating,
            $review->text
        );

        $email = Config::getNotificationEmail();
        if ($email !== '') {
            Event::send([
                'EVENT_NAME' => 'NEW_EXTERNAL_REVIEW_FOR_MODERATION',
                'LID' => 's1',
                'C_FIELDS' => [
                    'EMAIL_TO' => $email,
                    'MESSAGE' => $message,
                ],
            ]);
        }

        $token = Config::getTelegramBotToken();
        $chatId = Config::getTelegramChatId();
        if ($token !== '' && $chatId !== '') {
            $httpClient = new HttpClient(['socketTimeout' => 5, 'streamTimeout' => 5]);
            $httpClient->post(sprintf('https://api.telegram.org/bot%s/sendMessage', $token), [
                'chat_id' => $chatId,
                'text' => $message,
                'disable_web_page_preview' => 'true',
            ]);
        }
    }
}
