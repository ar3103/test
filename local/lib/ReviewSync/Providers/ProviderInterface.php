<?php

declare(strict_types=1);

namespace Company\ReviewSync\Providers;

use Company\ReviewSync\Domain\ReviewDto;

interface ProviderInterface
{
    public function getCode(): string;

    /** @return ReviewDto[] */
    public function fetch(int $limit): array;
}
