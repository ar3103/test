<?php

namespace Ai\SeoAudit\Infrastructure\Api\Providers;

use Ai\SeoAudit\Application\Auth\OAuthTokenManager;
use Ai\SeoAudit\Infrastructure\Api\Contracts\SearchProviderInterface;
use Ai\SeoAudit\Infrastructure\Http\RestClient;

final class GoogleSearchConsoleProvider implements SearchProviderInterface
{
    public function __construct(private readonly ?RestClient $restClient = null)
    {
    }

    public function getCode(): string
    {
        return 'google_search_console';
    }

    public function isConfigured(): bool
    {
        return OAuthTokenManager::get('gsc', 'refresh_token') !== '';
    }

    public function fetchSearchAnalytics(string $siteUrl, \DateTimeInterface $from, \DateTimeInterface $to): array
    {
        if (!$this->isConfigured()) {
            return ['provider' => $this->getCode(), 'rows' => [], 'error' => 'not_configured'];
        }

        $accessToken = OAuthTokenManager::get('gsc', 'access_token');
        if ($accessToken === '' || OAuthTokenManager::isExpired('gsc')) {
            $refresh = $this->refreshAccessToken();
            if (!$refresh['ok']) {
                return ['provider' => $this->getCode(), 'rows' => [], 'error' => 'token_refresh_failed'];
            }
            $accessToken = OAuthTokenManager::get('gsc', 'access_token');
        }

        $response = ($this->restClient ?? new RestClient())->post(
            'https://searchconsole.googleapis.com/webmasters/v3/sites/' . rawurlencode($siteUrl) . '/searchAnalytics/query',
            ['Authorization' => 'Bearer ' . $accessToken, 'Content-Type' => 'application/json'],
            [
                'startDate' => $from->format('Y-m-d'),
                'endDate' => $to->format('Y-m-d'),
                'dimensions' => ['query', 'page'],
                'rowLimit' => 1000,
            ],
            'gsc_search_analytics'
        );

        return ['provider' => $this->getCode(), 'status' => $response['status'], 'raw' => json_decode($response['body'], true) ?: []];
    }

    private function refreshAccessToken(): array
    {
        $clientId = OAuthTokenManager::get('gsc', 'client_id');
        $clientSecret = OAuthTokenManager::get('gsc', 'client_secret');
        $refreshToken = OAuthTokenManager::get('gsc', 'refresh_token');

        if ($clientId === '' || $clientSecret === '' || $refreshToken === '') {
            return ['ok' => false];
        }

        $response = ($this->restClient ?? new RestClient())->post(
            'https://oauth2.googleapis.com/token',
            ['Content-Type' => 'application/json'],
            [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'refresh_token' => $refreshToken,
                'grant_type' => 'refresh_token',
            ],
            'gsc_oauth_refresh'
        );

        $raw = json_decode($response['body'], true) ?: [];
        if (empty($raw['access_token'])) {
            return ['ok' => false, 'raw' => $raw];
        }

        OAuthTokenManager::set('gsc', 'access_token', (string) $raw['access_token']);
        OAuthTokenManager::set('gsc', 'expires_at', (string) (time() + (int) ($raw['expires_in'] ?? 3600)));
        return ['ok' => true];
    }
}
