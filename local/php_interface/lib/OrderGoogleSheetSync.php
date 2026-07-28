<?php

declare(strict_types=1);

namespace Local\Integration;

use Bitrix\Main\Loader;
use Bitrix\Sale\Order;
use Bitrix\Sale\Internals\OrderTable;
use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\BatchUpdateSpreadsheetRequest;
use Google\Service\Sheets\ValueRange;

/**
 * Синхронизация заказов Bitrix <-> Google Sheets.
 */
final class OrderGoogleSheetSync
{
    private const HEADER = [
        'Id',
        'Номер заказа',
        'Позиции заказа',
        'Вес',
        'Скидка',
        'Сумма без скидки',
        'Сумма со скидкой',
        'Фамилия',
        'Имя',
        'Отчество',
        'Телефон',
        'Способ доставки',
        'Адрес доставки',
        'Номер отделения',
    ];

    /**
     * Коды свойств заказа. Измените под свои коды в Bitrix.
     */
    private const PROPERTY_CODES = [
        'LAST_NAME' => 'LAST_NAME',
        'NAME' => 'NAME',
        'SECOND_NAME' => 'SECOND_NAME',
        'PHONE' => 'PHONE',
        'ADDRESS' => 'ADDRESS',
        'BRANCH_NUMBER' => 'BRANCH_NUMBER',
    ];

    private Sheets $sheets;
    private string $spreadsheetId;
    private string $sheetName;

    public function __construct(string $credentialsPath, string $spreadsheetId, string $sheetName = 'Orders')
    {
        if (!Loader::includeModule('sale')) {
            throw new \RuntimeException('Не удалось подключить модуль sale');
        }

        $client = new Client();
        $client->setApplicationName('Bitrix Order Sync');
        $client->setAuthConfig($credentialsPath);
        $client->setScopes([Sheets::SPREADSHEETS]);

        $this->sheets = new Sheets($client);
        $this->spreadsheetId = $spreadsheetId;
        $this->sheetName = $sheetName;
    }

    /**
     * Полная синхронизация в обе стороны.
     */
    public function sync(): void
    {
        $this->ensureHeader();
        $this->exportOrdersToSheet();
        $this->importSheetChangesToBitrix();
    }

    /**
     * Выгрузка заказов из Bitrix в Google Sheet.
     */
    public function exportOrdersToSheet(): void
    {
        $rows = [self::HEADER];

        $orderResult = OrderTable::getList([
            'select' => ['ID', 'ACCOUNT_NUMBER'],
            'order' => ['ID' => 'ASC'],
        ]);

        while ($orderData = $orderResult->fetch()) {
            $order = Order::load((int)$orderData['ID']);
            if (!$order) {
                continue;
            }

            $rows[] = $this->mapOrderToRow($order);
        }

        $range = sprintf('%s!A1:N', $this->sheetName);
        $this->sheets->spreadsheets_values->update(
            $this->spreadsheetId,
            $range,
            new ValueRange(['values' => $rows]),
            ['valueInputOption' => 'USER_ENTERED']
        );
    }

    /**
     * Импорт изменений из таблицы обратно в Bitrix.
     */
    public function importSheetChangesToBitrix(): void
    {
        $range = sprintf('%s!A2:N', $this->sheetName);
        $response = $this->sheets->spreadsheets_values->get($this->spreadsheetId, $range);
        $rows = $response->getValues() ?? [];

        foreach ($rows as $row) {
            $row = array_pad($row, 14, '');
            $orderId = (int)$row[0];
            if ($orderId <= 0) {
                continue;
            }

            $order = Order::load($orderId);
            if (!$order) {
                continue;
            }

            $this->applyRowToOrder($order, $row);
            $saveResult = $order->save();
            if (!$saveResult->isSuccess()) {
                throw new \RuntimeException(
                    sprintf('Ошибка сохранения заказа #%d: %s', $orderId, implode('; ', $saveResult->getErrorMessages()))
                );
            }
        }
    }

    private function ensureHeader(): void
    {
        $range = sprintf('%s!A1:N1', $this->sheetName);
        $existing = $this->sheets->spreadsheets_values->get($this->spreadsheetId, $range)->getValues();

        if (!empty($existing) && $existing[0] === self::HEADER) {
            return;
        }

        $this->sheets->spreadsheets_values->update(
            $this->spreadsheetId,
            $range,
            new ValueRange(['values' => [self::HEADER]]),
            ['valueInputOption' => 'RAW']
        );

        $request = new BatchUpdateSpreadsheetRequest([
            'requests' => [[
                'repeatCell' => [
                    'range' => [
                        'sheetId' => 0,
                        'startRowIndex' => 0,
                        'endRowIndex' => 1,
                    ],
                    'cell' => [
                        'userEnteredFormat' => [
                            'textFormat' => ['bold' => true],
                        ],
                    ],
                    'fields' => 'userEnteredFormat.textFormat.bold',
                ],
            ]],
        ]);

        $this->sheets->spreadsheets->batchUpdate($this->spreadsheetId, $request);
    }

    /**
     * @return array<int, string|int|float>
     */
    private function mapOrderToRow(Order $order): array
    {
        $basket = $order->getBasket();
        $items = [];
        $sumWithoutDiscount = 0.0;
        $totalWeight = 0.0;

        if ($basket) {
            /** @var \Bitrix\Sale\BasketItem $item */
            foreach ($basket as $item) {
                $name = (string)$item->getField('NAME');
                $quantity = (float)$item->getQuantity();
                $price = (float)$item->getPrice();
                $basePrice = (float)$item->getBasePrice();
                $weight = (float)$item->getWeight();

                $items[] = sprintf('%s x %s', $name, $quantity);
                $sumWithoutDiscount += $basePrice * $quantity;
                $totalWeight += $weight * $quantity;
            }
        }

        $sumWithDiscount = (float)$order->getPrice();
        $discountValue = $sumWithoutDiscount - $sumWithDiscount;

        return [
            (int)$order->getId(),
            (string)$order->getField('ACCOUNT_NUMBER'),
            implode('; ', $items),
            $totalWeight,
            $discountValue,
            $sumWithoutDiscount,
            $sumWithDiscount,
            $this->getProperty($order, self::PROPERTY_CODES['LAST_NAME']),
            $this->getProperty($order, self::PROPERTY_CODES['NAME']),
            $this->getProperty($order, self::PROPERTY_CODES['SECOND_NAME']),
            $this->getProperty($order, self::PROPERTY_CODES['PHONE']),
            $this->getDeliveryName($order),
            $this->getProperty($order, self::PROPERTY_CODES['ADDRESS']),
            $this->getProperty($order, self::PROPERTY_CODES['BRANCH_NUMBER']),
        ];
    }

    /**
     * @param array<int, string> $row
     */
    private function applyRowToOrder(Order $order, array $row): void
    {
        $propertyCollection = $order->getPropertyCollection();

        $this->setProperty($propertyCollection, self::PROPERTY_CODES['LAST_NAME'], $row[7]);
        $this->setProperty($propertyCollection, self::PROPERTY_CODES['NAME'], $row[8]);
        $this->setProperty($propertyCollection, self::PROPERTY_CODES['SECOND_NAME'], $row[9]);
        $this->setProperty($propertyCollection, self::PROPERTY_CODES['PHONE'], $row[10]);
        $this->setProperty($propertyCollection, self::PROPERTY_CODES['ADDRESS'], $row[12]);
        $this->setProperty($propertyCollection, self::PROPERTY_CODES['BRANCH_NUMBER'], $row[13]);

        if (!empty($row[11])) {
            $shipmentCollection = $order->getShipmentCollection();
            foreach ($shipmentCollection as $shipment) {
                if ($shipment->isSystem()) {
                    continue;
                }
                $shipment->setField('DELIVERY_NAME', $row[11]);
            }
        }
    }

    private function getDeliveryName(Order $order): string
    {
        $shipmentCollection = $order->getShipmentCollection();
        foreach ($shipmentCollection as $shipment) {
            if ($shipment->isSystem()) {
                continue;
            }

            return (string)$shipment->getField('DELIVERY_NAME');
        }

        return '';
    }

    private function getProperty(Order $order, string $code): string
    {
        $property = $order->getPropertyCollection()->getItemByOrderPropertyCode($code);

        return $property ? (string)$property->getValue() : '';
    }

    private function setProperty(\Bitrix\Sale\PropertyValueCollection $collection, string $code, string $value): void
    {
        $property = $collection->getItemByOrderPropertyCode($code);
        if ($property) {
            $property->setValue($value);
        }
    }
}
