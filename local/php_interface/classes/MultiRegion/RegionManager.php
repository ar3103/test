<?php

declare(strict_types=1);

namespace MultiRegion;

final class RegionManager
{
    private RegionResolver $resolver;
    private PriceManager $priceManager;
    private SeoManager $seoManager;
    private LanguageManager $languageManager;

    public function __construct()
    {
        $this->resolver = new RegionResolver();
        $this->priceManager = new PriceManager();
        $this->seoManager = new SeoManager();
        $this->languageManager = new LanguageManager();
    }

    public function bootstrap(array $server, array $cookie, ?string $geoCountryCode = null): array
    {
        $detected = $this->resolver->detectRegion($server, $cookie, $geoCountryCode);
        $regionCode = $detected['code'];
        $region = Config::regions()[$regionCode];
        $lang = $region['lang'] ?? 'en';

        if (($cookie['SITE_REGION'] ?? null) !== $regionCode) {
            $this->resolver->setRegionCookie($regionCode);
        }

        return [
            'regionCode' => $regionCode,
            'region' => $region,
            'source' => $detected['source'],
            'popupRequired' => ($detected['source'] === 'geo'),
            'popupLabel' => $this->languageManager->translate('choose_region', $lang),
            'ui' => [
                'confirmButtonText' => $this->languageManager->translate('confirm_region', $lang),
                'changeRegionText' => $this->languageManager->translate('change_region', $lang),
            ],
            'translatedFields' => $this->languageManager->translateFields([
                'NAME' => 'field_name',
                'PHONE' => 'field_phone',
                'EMAIL' => 'field_email',
                'MESSAGE' => 'field_message',
                'REGION' => 'field_region',
                'CITY' => 'field_city',
            ], $lang),
        ];
    }

    public function resolveProductPrice(float $basePrice, string $regionCode, array $regionalPrices = []): array
    {
        return $this->priceManager->resolvePrice($basePrice, $regionCode, $regionalPrices);
    }

    public function applySeo(string $regionCode, callable $setter): void
    {
        $this->seoManager->applyRegionSeo($regionCode, $setter);
    }
}
