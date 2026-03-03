<?php

namespace Ai\SeoAudit\Infrastructure\Api\Providers;

use Ai\SeoAudit\Infrastructure\Api\Contracts\SearchProviderInterface;

final class GoogleSearchConsoleProvider implements SearchProviderInterface
{
    public function __construct(private readonly string $credentialsJson = '')
    {
    }

    public function getCode(): string
    {
        return 'google_search_console';
    }

    public function isConfigured(): bool
    {
        return $this->credentialsJson !== '';
    }

    public function fetchSearchAnalytics(string $siteUrl, \DateTimeInterface $from, \DateTimeInterface $to): array
    {
        return [
            'provider' => $this->getCode(),
            'site' => $siteUrl,
            'from' => $from->format('Y-m-d'),
            'to' => $to->format('Y-m-d'),
            'rows' => [],
        ];
    }
}
