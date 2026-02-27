<?php

declare(strict_types=1);

use Bitrix\Main\Loader;
use Bitrix\Sale\Internals\OrderPropsTable;
use Bitrix\Sale\Internals\OrderPropsVariantTable;

require_once __DIR__ . '/europost_repository.php';

final class EuropostSyncService
{
    /**
     * URL можно поменять на актуальный endpoint Европочты.
     */
    public const API_URL = 'https://evropochta.by/api/points';

    /**
     * Символьный код свойства заказа (тип LIST) для выпадающего списка отделений.
     */
    public const ORDER_PROP_CODE = 'EUROPOST_OFFICE';

    public static function sync(): array
    {
        EuropostOfficeRepository::installSchema();

        $raw = self::fetchOfficesFromApi();
        $offices = self::normalize($raw);

        EuropostOfficeRepository::upsertBatch($offices);
        EuropostOfficeRepository::markInactiveMissing(array_column($offices, 'external_id'));

        self::syncOrderPropertyVariants();

        return [
            'loaded' => count($offices),
            'active' => count(EuropostOfficeRepository::getActiveOffices()),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function fetchOfficesFromApi(): array
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 20,
                'header' => [
                    'Accept: application/json',
                ],
            ],
        ]);

        $response = @file_get_contents(self::API_URL, false, $context);
        if ($response === false) {
            throw new RuntimeException('Не удалось получить список отделений Европочты по API.');
        }

        $decoded = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($decoded)) {
            throw new RuntimeException('API Европочты вернул некорректный формат.');
        }

        return $decoded;
    }

    /**
     * @param array<int, array<string, mixed>> $raw
     * @return array<int, array<string, mixed>>
     */
    private static function normalize(array $raw): array
    {
        $result = [];

        foreach ($raw as $item) {
            $externalId = (string)($item['id'] ?? '');
            if ($externalId === '') {
                continue;
            }

            $result[] = [
                'external_id' => $externalId,
                'title' => (string)($item['name'] ?? ('Отделение #' . $externalId)),
                'address' => (string)($item['address'] ?? ''),
                'city' => (string)($item['city'] ?? ''),
                'postal_code' => (string)($item['post_code'] ?? ''),
                'phone' => (string)($item['phone'] ?? ''),
                'work_time' => (string)($item['work_time'] ?? ''),
                'gps_lat' => $item['latitude'] ?? null,
                'gps_lon' => $item['longitude'] ?? null,
            ];
        }

        return $result;
    }

    private static function syncOrderPropertyVariants(): void
    {
        if (!Loader::includeModule('sale')) {
            throw new RuntimeException('Не удалось подключить модуль sale.');
        }

        $property = OrderPropsTable::getList([
            'select' => ['ID'],
            'filter' => ['=CODE' => self::ORDER_PROP_CODE],
            'limit' => 1,
        ])->fetch();

        if (!$property) {
            return;
        }

        $propId = (int)$property['ID'];
        $offices = EuropostOfficeRepository::getActiveOffices();

        $existing = [];
        $variantRows = OrderPropsVariantTable::getList([
            'select' => ['ID', 'VALUE', 'NAME'],
            'filter' => ['=ORDER_PROPS_ID' => $propId],
        ]);

        while ($variant = $variantRows->fetch()) {
            $existing[(string)$variant['VALUE']] = $variant;
        }

        foreach ($offices as $office) {
            $value = $office['EXTERNAL_ID'];
            $name = sprintf('%s — %s (%s)', $office['CITY'], $office['TITLE'], $office['ADDRESS']);

            if (isset($existing[$value])) {
                OrderPropsVariantTable::update((int)$existing[$value]['ID'], ['NAME' => $name]);
                unset($existing[$value]);
                continue;
            }

            OrderPropsVariantTable::add([
                'ORDER_PROPS_ID' => $propId,
                'NAME' => $name,
                'VALUE' => $value,
                'SORT' => 100,
                'DESCRIPTION' => '',
            ]);
        }

        // Удаляем варианты, которых больше нет у Европочты.
        foreach ($existing as $variant) {
            OrderPropsVariantTable::delete((int)$variant['ID']);
        }
    }
}
