<?php

use Bitrix\Sale\Delivery\CalculationResult;
use Bitrix\Sale\Delivery\Services\Base;
use Bitrix\Sale\Shipment;

require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/europost/tariff.php';

class EuropostDelivery extends Base
{
    public static function getClassTitle()
    {
        return 'Европочта';
    }

    public static function getClassDescription()
    {
        return 'Доставка Европочтой с выбором отделения';
    }

    protected function calculateConcrete(Shipment $shipment)
    {
        $result = new CalculationResult();

        // Вес в Bitrix хранится в граммах.
        $weightKg = $shipment->getWeight() / 1000;
        $price = europostCalculatePriceByWeight($weightKg);

        if ($price === false) {
            $result->addError(new \Bitrix\Main\Error('Вес должен быть больше 0 кг и не превышать 200 кг'));
            return $result;
        }

        $result->setDeliveryPrice($price);

        return $result;
    }

    public function isCalculatePriceImmediately()
    {
        return true;
    }
}
