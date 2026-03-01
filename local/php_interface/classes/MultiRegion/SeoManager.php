<?php

declare(strict_types=1);

namespace MultiRegion;

final class SeoManager
{
    public function applyRegionSeo(string $regionCode, callable $setter): void
    {
        $region = Config::regions()[$regionCode] ?? null;
        if ($region === null) {
            return;
        }

        $seo = $region['seo'] ?? [];

        if (!empty($seo['title'])) {
            $setter('title', $seo['title']);
        }
        if (!empty($seo['description'])) {
            $setter('description', $seo['description']);
        }
        if (!empty($seo['keywords'])) {
            $setter('keywords', $seo['keywords']);
        }
    }
}
