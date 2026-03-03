<?php

namespace Ai\SeoAudit\Infrastructure\Api\Providers;

use Ai\SeoAudit\Infrastructure\Api\Contracts\SearchProviderInterface;
use Ai\SeoAudit\Infrastructure\Http\RestClient;

final class GoogleSearchConsoleProvider implements SearchProviderInterface
{
    public function __construct(
        private readonly string $accessToken = '',
        private readonly ?RestClient $restClient = null
    ) {
    }

    public function getCode(): string
    {
        return 'google_search_console';
    }

    public function isConfigured(): bool
    {
        return $this->accessToken !== '';
    }

    public function fetchSearchAnalytics(string $siteUrl, \DateTimeInterface $from, \DateTimeInterface $to): array
    {
        if (!$this->isConfigured()) {
            return ['provider' => $this->getCode(), 'rows' => [], 'error' => 'not_configured'];
        }

        $response = ($this->restClient ?? new RestClient())->post(
            'https://searchconsole.googleapis.com/webmasters/v3/sites/' . rawurlencode($siteUrl) . '/searchAnalytics/query',
            [
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
            ],
            [
                'startDate' => $from->format('Y-m-d'),
                'endDate' => $to->format('Y-m-d'),
                'dimensions' => ['query', 'page'],
                'rowLimit' => 1000,
            ]
        );

        return [
            'provider' => $this->getCode(),
            'status' => $response['status'],
            'raw' => json_decode($response['body'], true) ?: [],
        ];
    }
}
