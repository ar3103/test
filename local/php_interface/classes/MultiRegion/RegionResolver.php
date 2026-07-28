<?php

declare(strict_types=1);

namespace MultiRegion;

final class RegionResolver
{
    public function detectRegion(array $server, array $cookie, ?string $geoCountryCode = null): array
    {
        $regions = Config::regions();
        $host = $server['HTTP_HOST'] ?? '';
        $requestPath = parse_url($server['REQUEST_URI'] ?? '/', \PHP_URL_PATH) ?: '/';

        foreach ($regions as $code => $region) {
            if (($region['host'] ?? '') === $host) {
                return ['code' => $code, 'source' => 'host'];
            }

            if (!empty($region['folder']) && str_starts_with($requestPath, $region['folder'])) {
                return ['code' => $code, 'source' => 'folder'];
            }
        }

        if (!empty($cookie['SITE_REGION']) && isset($regions[$cookie['SITE_REGION']])) {
            return ['code' => $cookie['SITE_REGION'], 'source' => 'cookie'];
        }

        if ($geoCountryCode !== null) {
            $map = Config::geolocationCountryMap();
            if (isset($map[$geoCountryCode])) {
                return ['code' => $map[$geoCountryCode], 'source' => 'geo'];
            }
        }

        return ['code' => Config::defaultRegionCode(), 'source' => 'default'];
    }

    public function buildRedirectUrl(string $regionCode, string $path = '/'): string
    {
        $region = Config::regions()[$regionCode] ?? null;
        if ($region === null) {
            throw new \InvalidArgumentException('Unknown region: ' . $regionCode);
        }

        if (!empty($region['host'])) {
            return 'https://' . $region['host'] . $path;
        }

        return $region['folder'] . ltrim($path, '/');
    }

    public function setRegionCookie(string $regionCode): void
    {
        setcookie('SITE_REGION', $regionCode, [
            'expires' => time() + 60 * 60 * 24 * 365,
            'path' => '/',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => false,
            'samesite' => 'Lax',
        ]);
    }
}
