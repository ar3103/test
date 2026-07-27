<?php

declare(strict_types=1);

namespace Company\ReviewSync\Provider;

use Company\ReviewSync\Config\Option;
use Company\ReviewSync\DTO\ReviewItem;

final class GoogleProvider extends AbstractHttpProvider
{
    public function getCode(): string
    {
        return 'google';
    }

    public function fetchNewReviews(?\DateTimeImmutable $since): array
    {
        $apiKey = Option::getString(Option::GOOGLE_API_KEY);
        $placeId = Option::getString(Option::GOOGLE_PLACE_ID);

        if ($apiKey === '' || $placeId === '') {
            return [];
        }

        $url = sprintf(
            'https://maps.googleapis.com/maps/api/place/details/json?place_id=%s&fields=reviews&language=ru&key=%s',
            urlencode($placeId),
            urlencode($apiKey)
        );

        $data = $this->getJson($url);
        $items = [];

        foreach (($data['result']['reviews'] ?? []) as $row) {
            $timestamp = (int) ($row['time'] ?? 0);
            if ($timestamp <= 0) {
                continue;
            }

            $publishedAt = (new \DateTimeImmutable())->setTimestamp($timestamp);
            if ($since !== null && $publishedAt <= $since) {
                continue;
            }

            $items[] = new ReviewItem(
                platform: $this->getCode(),
                externalId: sha1((string) ($row['author_url'] ?? '') . '#' . $timestamp),
                authorName: (string) ($row['author_name'] ?? 'Гость'),
                authorRole: '',
                rating: (int) ($row['rating'] ?? 0),
                text: trim((string) ($row['text'] ?? '')),
                authorPhotoUrl: $row['profile_photo_url'] ?? null,
                attachmentUrl: null,
                publishedAt: $publishedAt
            );
        }

        return $items;
    }
}
