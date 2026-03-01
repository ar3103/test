<?php

declare(strict_types=1);

namespace MultiRegion;

final class PriceManager
{
    public function resolvePrice(float $basePrice, string $regionCode, array $regionalPrices = []): array
    {
        $region = Config::regions()[$regionCode] ?? null;
        if ($region === null) {
            throw new \InvalidArgumentException('Unknown region: ' . $regionCode);
        }

        $price = $regionalPrices[$regionCode] ?? $basePrice;
        $markupPercent = (float)($region['markupPercent'] ?? 0.0);
        $priceWithMarkup = $price + ($price * $markupPercent / 100);

        return [
            'amount' => round($priceWithMarkup, 2),
            'currency' => $region['currency'],
            'base' => $basePrice,
            'markupPercent' => $markupPercent,
        ];
    }
}
