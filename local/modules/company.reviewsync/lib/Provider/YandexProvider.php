<?php

declare(strict_types=1);

namespace Company\ReviewSync\Provider;

use Company\ReviewSync\Config\Option;
use Company\ReviewSync\DTO\ReviewItem;

final class YandexProvider extends AbstractHttpProvider
{
    public function getCode(): string
    {
        return 'yandex';
    }

    public function fetchNewReviews(?\DateTimeImmutable $since): array
    {
        $apiKey = Option::getString(Option::YANDEX_API_KEY);
        $orgId = Option::getString(Option::YANDEX_ORG_ID);

        if ($apiKey === '' || $orgId === '') {
            return [];
        }

        $url = sprintf('https://api.business.yandex.ru/v1/companies/%s/reviews', urlencode($orgId));
        $data = $this->getJson($url, ['Authorization' => 'Bearer ' . $apiKey]);

        $items = [];
        foreach (($data['reviews'] ?? []) as $row) {
            $publishedAt = new \DateTimeImmutable((string) ($row['date'] ?? 'now'));
            if ($since !== null && $publishedAt <= $since) {
                continue;
            }

            $items[] = new ReviewItem(
                platform: $this->getCode(),
                externalId: (string) ($row['id'] ?? ''),
                authorName: (string) ($row['author']['name'] ?? 'Гость'),
                authorRole: (string) ($row['author']['profession'] ?? ''),
                rating: (int) ($row['rating'] ?? 0),
                text: trim((string) ($row['text'] ?? '')),
                authorPhotoUrl: $row['author']['avatarUrl'] ?? null,
                attachmentUrl: null,
                publishedAt: $publishedAt
            );
        }

        return $items;
    }
}
