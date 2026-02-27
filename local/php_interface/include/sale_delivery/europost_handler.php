<?php

declare(strict_types=1);

namespace Sale\Handlers\Delivery;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Delivery\CalculationResult;
use Bitrix\Sale\Delivery\Services\Base;
use Bitrix\Sale\Shipment;

Loc::loadMessages(__FILE__);

/**
 * Кастомная служба доставки Европочты без отдельного модуля.
 * Стоимость настраивается шагом в 2 кг.
 */
class EuropostHandler extends Base
{
    private const MAX_WEIGHT_GRAMS = 200000;

    /**
     * Тарифы за каждый блок 2 кг.
     * Ключ = номер блока (1 => 0-2 кг, 2 => 2-4 кг, ...)
     */
    private const PRICE_GRID = [
        1 => 5.00,
        2 => 6.50,
        3 => 8.00,
        4 => 9.50,
        5 => 11.00,
        // ... дополните под свои тарифы.
    ];

    public static function getClassTitle(): string
    {
        return 'Европочта (отделение)';
    }

    public static function getClassDescription(): string
    {
        return 'Доставка в отделение Европочты с выбором ПВЗ из выпадающего списка';
    }

    protected function calculateConcrete(Shipment $shipment = null): CalculationResult
    {
        $result = new CalculationResult();

        if ($shipment === null) {
            $result->addError(new Error('Не передан объект Shipment.'));
            return $result;
        }

        $weightGrams = (float)$shipment->getWeight();

        if ($weightGrams <= 0) {
            $result->addError(new Error('Вес отправления должен быть больше 0.'));
            return $result;
        }

        if ($weightGrams > self::MAX_WEIGHT_GRAMS) {
            $result->addError(new Error('Максимальный вес для Европочты: 200 кг.'));
            return $result;
        }

        $price = $this->resolvePrice($weightGrams);
        $result->setDeliveryPrice($price);

        return $result;
    }

    private function resolvePrice(float $weightGrams): float
    {
        $block = (int)ceil($weightGrams / 2000);

        if (isset(self::PRICE_GRID[$block])) {
            return (float)self::PRICE_GRID[$block];
        }

        $lastPrice = end(self::PRICE_GRID);
        return (float)$lastPrice;
    }

    protected static function getConfigStructure(): array
    {
        return [];
    }
}
