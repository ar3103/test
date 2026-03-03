<?php

namespace Ai\SeoAudit\Infrastructure\Api\Contracts;

interface SearchProviderInterface
{
    public function getCode(): string;

    public function isConfigured(): bool;

    public function fetchSearchAnalytics(string $siteUrl, \DateTimeInterface $from, \DateTimeInterface $to): array;
}
