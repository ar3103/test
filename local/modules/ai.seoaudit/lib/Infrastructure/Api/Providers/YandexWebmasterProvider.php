<?php

namespace Ai\SeoAudit\Infrastructure\Api\Providers;

use Ai\SeoAudit\Infrastructure\Api\Contracts\SearchProviderInterface;

final class YandexWebmasterProvider implements SearchProviderInterface
{
    public function __construct(private readonly string $token = '')
    {
    }

    public function getCode(): string
    {
        return 'yandex_webmaster';
    }

    public function isConfigured(): bool
    {
        return $this->token !== '';
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
