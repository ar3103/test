<?php

declare(strict_types=1);

namespace Company\ReviewSync\Providers;

use Company\ReviewSync\Domain\ReviewDto;

final class YandexProvider extends AbstractHttpProvider
{
    public function getCode(): string
    {
        return 'yandex';
    }

    protected function mapReview(array $item): ?ReviewDto
    {
        if (empty($item['id']) || empty($item['text'])) {
            return null;
        }

        return new ReviewDto(
            (string)$item['id'],
            $this->getCode(),
            (string)($item['author']['name'] ?? 'Пользователь Яндекс'),
            (string)($item['author']['position'] ?? ''),
            (int)($item['rating'] ?? 0),
            (string)$item['text'],
            (string)($item['author']['avatar'] ?? ''),
            (string)($item['attachment'] ?? ''),
            (string)($item['published_at'] ?? date(DATE_ATOM))
        );
    }
}
