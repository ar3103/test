<?php

declare(strict_types=1);

namespace Company\ReviewSync\DTO;

final class ReviewItem
{
    public function __construct(
        public readonly string $platform,
        public readonly string $externalId,
        public readonly string $authorName,
        public readonly string $authorRole,
        public readonly int $rating,
        public readonly string $text,
        public readonly ?string $authorPhotoUrl,
        public readonly ?string $attachmentUrl,
        public readonly \DateTimeImmutable $publishedAt
    ) {
    }

    public function getUniqueXmlId(): string
    {
        return sprintf('%s:%s', $this->platform, $this->externalId);
    }
}
