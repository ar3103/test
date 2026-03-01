<?php

declare(strict_types=1);

namespace Company\ReviewSync\Provider;

use Company\ReviewSync\DTO\ReviewItem;

interface ReviewProviderInterface
{
    public function getCode(): string;

    /** @return ReviewItem[] */
    public function fetchNewReviews(?\DateTimeImmutable $since): array;
}
