<?php

declare(strict_types=1);

namespace Company\ReviewSync\Providers;

use Company\ReviewSync\Domain\ReviewDto;

final class TwoGisProvider extends AbstractHttpProvider
{
    public function getCode(): string
    {
        return 'twogis';
    }

    protected function mapReview(array $item): ?ReviewDto
    {
        if (empty($item['id']) || empty($item['text'])) {
            return null;
        }

        return new ReviewDto(
            (string)$item['id'],
            $this->getCode(),
            (string)($item['user_name'] ?? 'Пользователь 2ГИС'),
            (string)($item['user_position'] ?? ''),
            (int)($item['rating'] ?? 0),
            (string)$item['text'],
            (string)($item['user_photo'] ?? ''),
            (string)($item['attachment_url'] ?? ''),
            (string)($item['created_at'] ?? date(DATE_ATOM))
        );
    }
}
