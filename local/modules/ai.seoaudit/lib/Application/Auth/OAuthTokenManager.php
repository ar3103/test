<?php

namespace Ai\SeoAudit\Application\Auth;

use Bitrix\Main\Config\Option;

final class OAuthTokenManager
{
    private const MODULE_ID = 'ai.seoaudit';

    public static function get(string $provider, string $key, string $default = ''): string
    {
        return (string) Option::get(self::MODULE_ID, self::buildKey($provider, $key), $default);
    }

    public static function set(string $provider, string $key, string $value): void
    {
        Option::set(self::MODULE_ID, self::buildKey($provider, $key), $value);
    }

    public static function isExpired(string $provider): bool
    {
        $expiresAt = (int) self::get($provider, 'expires_at', '0');
        return $expiresAt > 0 && $expiresAt <= time() + 30;
    }

    private static function buildKey(string $provider, string $key): string
    {
        return sprintf('oauth_%s_%s', $provider, $key);
    }
}
