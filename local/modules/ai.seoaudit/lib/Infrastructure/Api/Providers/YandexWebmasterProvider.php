<?php

namespace Ai\SeoAudit\Infrastructure\Api\Providers;

use Ai\SeoAudit\Application\Auth\OAuthTokenManager;
use Ai\SeoAudit\Infrastructure\Api\Contracts\SearchProviderInterface;
use Ai\SeoAudit\Infrastructure\Http\RestClient;

final class YandexWebmasterProvider implements SearchProviderInterface
{
    public function __construct(private readonly ?RestClient $restClient = null)
    {
    }

    public function getCode(): string
    {
        return 'yandex_webmaster';
    }

    public function isConfigured(): bool
    {
        return OAuthTokenManager::get('yandex', 'refresh_token') !== ''
            && OAuthTokenManager::get('yandex', 'user_id') !== '';
    }

    public function fetchSearchAnalytics(string $siteUrl, \DateTimeInterface $from, \DateTimeInterface $to): array
    {
        if (!$this->isConfigured()) {
            return ['provider' => $this->getCode(), 'rows' => [], 'error' => 'not_configured'];
        }

        $accessToken = OAuthTokenManager::get('yandex', 'access_token');
        if ($accessToken === '' || OAuthTokenManager::isExpired('yandex')) {
            $refresh = $this->refreshAccessToken();
            if (!$refresh['ok']) {
                return ['provider' => $this->getCode(), 'rows' => [], 'error' => 'token_refresh_failed'];
            }
            $accessToken = OAuthTokenManager::get('yandex', 'access_token');
        }

        $userId = OAuthTokenManager::get('yandex', 'user_id');
        $hostUrl = rawurlencode($siteUrl);

        $response = ($this->restClient ?? new RestClient())->get(
            sprintf('https://api.webmaster.yandex.net/v4/user/%s/hosts/%s/search-queries/popular', $userId, $hostUrl),
            ['Authorization' => 'OAuth ' . $accessToken],
            ['date_from' => $from->format('Y-m-d'), 'date_to' => $to->format('Y-m-d')],
            'yandex_webmaster_popular'
        );

        return ['provider' => $this->getCode(), 'status' => $response['status'], 'raw' => json_decode($response['body'], true) ?: []];
    }

    private function refreshAccessToken(): array
    {
        $clientId = OAuthTokenManager::get('yandex', 'client_id');
        $clientSecret = OAuthTokenManager::get('yandex', 'client_secret');
        $refreshToken = OAuthTokenManager::get('yandex', 'refresh_token');

        if ($clientId === '' || $clientSecret === '' || $refreshToken === '') {
            return ['ok' => false];
        }

        $response = ($this->restClient ?? new RestClient())->post(
            'https://oauth.yandex.ru/token',
            ['Content-Type' => 'application/json'],
            [
                'grant_type' => 'refresh_token',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'refresh_token' => $refreshToken,
            ],
            'yandex_oauth_refresh'
        );

        $raw = json_decode($response['body'], true) ?: [];
        if (empty($raw['access_token'])) {
            return ['ok' => false, 'raw' => $raw];
        }

        OAuthTokenManager::set('yandex', 'access_token', (string) $raw['access_token']);
        OAuthTokenManager::set('yandex', 'expires_at', (string) (time() + (int) ($raw['expires_in'] ?? 3600)));
        return ['ok' => true];
    }
}
