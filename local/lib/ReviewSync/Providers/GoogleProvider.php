<?php

declare(strict_types=1);

namespace Company\ReviewSync\Providers;

use Company\ReviewSync\Domain\ReviewDto;

final class GoogleProvider extends AbstractHttpProvider
{
    public function getCode(): string
    {
        return 'google';
    }

    protected function mapReview(array $item): ?ReviewDto
    {
        if (empty($item['review_id']) || empty($item['comment'])) {
            return null;
        }

        return new ReviewDto(
            (string)$item['review_id'],
            $this->getCode(),
            (string)($item['author_name'] ?? 'Google User'),
            (string)($item['author_title'] ?? ''),
            (int)($item['stars'] ?? 0),
            (string)$item['comment'],
            (string)($item['author_photo_url'] ?? ''),
            (string)($item['file_url'] ?? ''),
            (string)($item['created_at'] ?? date(DATE_ATOM))
        );
    }
}
