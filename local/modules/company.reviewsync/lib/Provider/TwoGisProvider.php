<?php

declare(strict_types=1);

namespace Company\ReviewSync\Provider;

use Company\ReviewSync\Config\Option;
use Company\ReviewSync\DTO\ReviewItem;

final class TwoGisProvider extends AbstractHttpProvider
{
    public function getCode(): string
    {
        return '2gis';
    }

    public function fetchNewReviews(?\DateTimeImmutable $since): array
    {
        $apiKey = Option::getString(Option::TWOGIS_API_KEY);
        $branchId = Option::getString(Option::TWOGIS_BRANCH_ID);

        if ($apiKey === '' || $branchId === '') {
            return [];
        }

        $url = sprintf('https://catalog.api.2gis.com/3.0/items/%s/reviews?key=%s', urlencode($branchId), urlencode($apiKey));
        $data = $this->getJson($url);
        $items = [];

        foreach (($data['result']['items'] ?? []) as $row) {
            $publishedAt = new \DateTimeImmutable((string) ($row['created'] ?? 'now'));
            if ($since !== null && $publishedAt <= $since) {
                continue;
            }

            $items[] = new ReviewItem(
                platform: $this->getCode(),
                externalId: (string) ($row['id'] ?? ''),
                authorName: (string) ($row['user']['name'] ?? 'Гость'),
                authorRole: '',
                rating: (int) ($row['rating'] ?? 0),
                text: trim((string) ($row['text'] ?? '')),
                authorPhotoUrl: $row['user']['photo'] ?? null,
                attachmentUrl: $row['photos'][0]['url'] ?? null,
                publishedAt: $publishedAt
            );
        }

        return $items;
    }
}
