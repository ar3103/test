<?php

declare(strict_types=1);

namespace MultiRegion;

final class Config
{
    /**
     * Конфигурация стран/городов/отделений.
     *
     * regionCode:
     * - country: ISO-код страны
     * - city: город
     * - host: поддомен/домен региона
     * - folder: подпапка региона
     * - lang: язык
     * - currency: валюта
     * - markupPercent: наценка региона
     * - branches: отделения
     */
    public static function regions(): array
    {
        return [
            'ru-moscow' => [
                'country' => 'RU',
                'city' => 'Moscow',
                'host' => 'msk.example.com',
                'folder' => '/ru/moscow/',
                'lang' => 'ru',
                'currency' => 'RUB',
                'markupPercent' => 7.5,
                'branches' => [
                    ['name' => 'Центральный офис', 'address' => 'ул. Тверская, 1'],
                    ['name' => 'Склад Север', 'address' => 'Ленинградское ш., 25'],
                ],
                'seo' => [
                    'title' => 'Интернет-магазин в Москве',
                    'description' => 'Каталог и цены для Москвы',
                    'keywords' => 'москва, купить, цены',
                ],
            ],
            'kz-almaty' => [
                'country' => 'KZ',
                'city' => 'Almaty',
                'host' => 'kz.example.com',
                'folder' => '/kz/almaty/',
                'lang' => 'ru',
                'currency' => 'KZT',
                'markupPercent' => 5.0,
                'branches' => [
                    ['name' => 'Алматы офис', 'address' => 'пр. Аль-Фараби, 17'],
                ],
                'seo' => [
                    'title' => 'Интернет-магазин в Алматы',
                    'description' => 'Региональные цены и доставка по Казахстану',
                    'keywords' => 'алматы, казахстан, магазин',
                ],
            ],
            'en-berlin' => [
                'country' => 'DE',
                'city' => 'Berlin',
                'host' => 'de.example.com',
                'folder' => '/de/berlin/',
                'lang' => 'en',
                'currency' => 'EUR',
                'markupPercent' => 12.0,
                'branches' => [
                    ['name' => 'Berlin Branch', 'address' => 'Alexanderplatz 4'],
                ],
                'seo' => [
                    'title' => 'Online store in Berlin',
                    'description' => 'Products and regional prices for Germany',
                    'keywords' => 'berlin, germany, ecommerce',
                ],
            ],
        ];
    }

    public static function defaultRegionCode(): string
    {
        return 'ru-moscow';
    }

    public static function geolocationCountryMap(): array
    {
        return [
            'RU' => 'ru-moscow',
            'KZ' => 'kz-almaty',
            'DE' => 'en-berlin',
        ];
    }
}
