<?php

declare(strict_types=1);

namespace Company\ReviewSync\Domain;

final class ReviewDto
{
    public function __construct(
        public readonly string $externalId,
        public readonly string $provider,
        public readonly string $authorName,
        public readonly string $authorPosition,
        public readonly int $rating,
        public readonly string $text,
        public readonly string $photoUrl,
        public readonly string $attachmentUrl,
        public readonly string $publishedAt
    ) {
    }

    public function getHash(): string
    {
        return sha1(implode('|', [
            $this->provider,
            $this->externalId,
            $this->authorName,
            $this->rating,
            $this->text,
        ]));
    }
}
