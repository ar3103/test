<?php

declare(strict_types=1);

namespace MultiRegion;

final class SitemapRobotsGenerator
{
    public function generateRegionalSitemap(array $urls): string
    {
        $regions = Config::regions();
        $xml = ['<?xml version="1.0" encoding="UTF-8"?>', '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'];

        foreach ($regions as $region) {
            foreach ($urls as $urlPath) {
                $loc = 'https://' . $region['host'] . '/' . ltrim($urlPath, '/');
                $xml[] = '<url><loc>' . htmlspecialchars($loc, ENT_XML1) . '</loc></url>';
            }
        }

        $xml[] = '</urlset>';
        return implode(PHP_EOL, $xml);
    }

    public function generateRobotsTxt(): string
    {
        $lines = [
            'User-agent: *',
            'Disallow: /bitrix/',
        ];

        foreach (Config::regions() as $region) {
            $lines[] = 'Sitemap: https://' . $region['host'] . '/sitemap.xml';
        }

        return implode(PHP_EOL, $lines) . PHP_EOL;
    }
}
