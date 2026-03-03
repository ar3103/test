<?php

namespace Ai\SeoAudit\Infrastructure\Api\Providers;

use Ai\SeoAudit\Infrastructure\Api\Contracts\SearchProviderInterface;
use Ai\SeoAudit\Infrastructure\Http\RestClient;

final class YandexWebmasterProvider implements SearchProviderInterface
{
    public function __construct(
        private readonly string $token = '',
        private readonly string $userId = '',
        private readonly ?RestClient $restClient = null
    ) {
    }

    public function getCode(): string
    {
        return 'yandex_webmaster';
    }

    public function isConfigured(): bool
    {
        return $this->token !== '' && $this->userId !== '';
    }

    public function fetchSearchAnalytics(string $siteUrl, \DateTimeInterface $from, \DateTimeInterface $to): array
    {
        if (!$this->isConfigured()) {
            return ['provider' => $this->getCode(), 'rows' => [], 'error' => 'not_configured'];
        }

        $hostUrl = rawurlencode($siteUrl);
        $response = ($this->restClient ?? new RestClient())->get(
            sprintf('https://api.webmaster.yandex.net/v4/user/%s/hosts/%s/search-queries/popular', $this->userId, $hostUrl),
            ['Authorization' => 'OAuth ' . $this->token],
            ['date_from' => $from->format('Y-m-d'), 'date_to' => $to->format('Y-m-d')]
        );

        return [
            'provider' => $this->getCode(),
            'status' => $response['status'],
            'raw' => json_decode($response['body'], true) ?: [],
        ];
    }
}
