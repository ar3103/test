<?php

use Bitrix\Main\Context;
use Bitrix\Main\Diag\Debug;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Mail\Event;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

Loc::loadMessages(__FILE__);

class PromoCalculatorComponent extends CBitrixComponent
{
    private const PERSONNEL_TAX_RATE = 0.60;
    private const AGENCY_PERCENT = 0.15;
    private const FINAL_TAX_RATE = 0.08;

    public function onPrepareComponentParams($params)
    {
        $params['IBLOCK_ID'] = (int)($params['IBLOCK_ID'] ?? 0);
        $params['EMAIL_TO'] = trim((string)($params['EMAIL_TO'] ?? ''));
        $params['TELEGRAM_BOT_TOKEN'] = trim((string)($params['TELEGRAM_BOT_TOKEN'] ?? ''));
        $params['TELEGRAM_CHAT_ID'] = trim((string)($params['TELEGRAM_CHAT_ID'] ?? ''));
        $params['MANAGEMENT_PERCENT'] = (float)($params['MANAGEMENT_PERCENT'] ?? 15);
        $params['COSTS_IBLOCK_ID'] = (int)($params['COSTS_IBLOCK_ID'] ?? 0);

        if (!is_array($params['PROMO_TYPES'] ?? null) || !$params['PROMO_TYPES']) {
            $params['PROMO_TYPES'] = [
                Loc::getMessage('PROMO_CALCULATOR_DEFAULT_PROMO_TYPE_1') => 900,
                Loc::getMessage('PROMO_CALCULATOR_DEFAULT_PROMO_TYPE_2') => 1200,
                Loc::getMessage('PROMO_CALCULATOR_DEFAULT_PROMO_TYPE_3') => 1800,
            ];
        }

        if (!is_array($params['REQUIRED_STAFF_OPTIONS'] ?? null) || !$params['REQUIRED_STAFF_OPTIONS']) {
            $params['REQUIRED_STAFF_OPTIONS'] = [
                Loc::getMessage('PROMO_CALCULATOR_DEFAULT_REQUIRED_STAFF_1'),
                Loc::getMessage('PROMO_CALCULATOR_DEFAULT_REQUIRED_STAFF_2'),
                Loc::getMessage('PROMO_CALCULATOR_DEFAULT_REQUIRED_STAFF_3'),
                Loc::getMessage('PROMO_CALCULATOR_DEFAULT_REQUIRED_STAFF_4'),
            ];
        }

        return $params;
    }

    public function executeComponent()
    {
        $this->arResult['PROMO_TYPES'] = $this->arParams['PROMO_TYPES'];
        $this->arResult['REQUIRED_STAFF_OPTIONS'] = $this->getRequiredStaffOptions();
        $this->arResult['MANAGEMENT_PERCENT'] = $this->arParams['MANAGEMENT_PERCENT'];

        if ($this->isAjaxRequest()) {
            $this->processAjax();
            return;
        }

        $this->includeComponentTemplate();
    }

    private function getRequiredStaffOptions(): array
    {
        if (
            $this->arParams['COSTS_IBLOCK_ID'] > 0
            && Loader::includeModule('iblock')
            && class_exists('CIBlockElement')
        ) {
            $options = [];
            $elements = CIBlockElement::GetList(
                ['SORT' => 'ASC', 'NAME' => 'ASC'],
                ['IBLOCK_ID' => $this->arParams['COSTS_IBLOCK_ID'], 'ACTIVE' => 'Y'],
                false,
                false,
                ['ID', 'NAME']
            );

            while ($element = $elements->Fetch()) {
                $name = trim((string)($element['NAME'] ?? ''));
                if ($name !== '') {
                    $options[] = $name;
                }
            }

            if ($options) {
                return array_values(array_unique($options));
            }
        }

        return $this->arParams['REQUIRED_STAFF_OPTIONS'];
    }

    private function isAjaxRequest(): bool
    {
        $request = Context::getCurrent()->getRequest();
        return $request->isPost() && $request->getPost('promo_calculator_action') === 'submit';
    }

    private function processAjax(): void
    {
        $request = Context::getCurrent()->getRequest();
        if (!check_bitrix_sessid()) {
            $this->sendJson(['success' => false, 'message' => Loc::getMessage('PROMO_CALCULATOR_ERROR_SESSION')]);
        }

        $data = [
            'promo_type' => trim((string)$request->getPost('promo_type')),
            'required_staff' => trim((string)$request->getPost('required_staff')),
            'people_count' => (int)$request->getPost('people_count'),
            'hours_count' => (int)$request->getPost('hours_count'),
            'days_count' => (int)$request->getPost('days_count'),
            'name' => trim((string)$request->getPost('name')),
            'phone' => trim((string)$request->getPost('phone')),
            'email' => trim((string)$request->getPost('email')),
            'message' => trim((string)$request->getPost('message')),
        ];

        $validationError = $this->validate($data);
        if ($validationError !== null) {
            $this->sendJson(['success' => false, 'message' => $validationError]);
        }

        $calculation = $this->calculate($data);
        $excelPath = $this->generateExcel($data, $calculation);

        $this->saveToIblock($data, $calculation, $excelPath);
        $this->sendEmail($data, $calculation, $excelPath);
        $this->sendTelegram($data, $calculation, $excelPath);

        $this->sendJson([
            'success' => true,
            'message' => Loc::getMessage('PROMO_CALCULATOR_SUCCESS'),
            'download_url' => $excelPath,
            'total' => $calculation['total'],
        ]);
    }

    private function validate(array $data): ?string
    {
        if ($data['promo_type'] === '' || !isset($this->arParams['PROMO_TYPES'][$data['promo_type']])) {
            return Loc::getMessage('PROMO_CALCULATOR_ERROR_PROMO_TYPE');
        }

        if ($data['required_staff'] === '' || !in_array($data['required_staff'], $this->arResult['REQUIRED_STAFF_OPTIONS'], true)) {
            return Loc::getMessage('PROMO_CALCULATOR_ERROR_REQUIRED_STAFF');
        }

        if ($data['people_count'] < 1 || $data['hours_count'] < 1 || $data['days_count'] < 1) {
            return Loc::getMessage('PROMO_CALCULATOR_ERROR_COUNTS');
        }

        if ($data['name'] === '' || $data['phone'] === '' || $data['email'] === '') {
            return Loc::getMessage('PROMO_CALCULATOR_ERROR_CONTACTS');
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return Loc::getMessage('PROMO_CALCULATOR_ERROR_EMAIL');
        }

        return null;
    }

    private function calculate(array $data): array
    {
        $ratePerDay = (float)$this->arParams['PROMO_TYPES'][$data['promo_type']];
        $basePersonnel = $ratePerDay * $data['people_count'] * $data['days_count'];
        $personnelTaxes = $basePersonnel * self::PERSONNEL_TAX_RATE;
        $personnelTotal = $basePersonnel + $personnelTaxes;

        $management = $personnelTotal * ((float)$this->arParams['MANAGEMENT_PERCENT'] / 100);
        $agency = $personnelTotal * self::AGENCY_PERCENT;
        $subtotal = $personnelTotal + $management + $agency;

        $taxes = $subtotal * self::FINAL_TAX_RATE;
        $total = $subtotal + $taxes;

        return [
            'rate_per_day' => round($ratePerDay, 2),
            'base_personnel' => round($basePersonnel, 2),
            'personnel_taxes' => round($personnelTaxes, 2),
            'personnel_total' => round($personnelTotal, 2),
            'management' => round($management, 2),
            'agency' => round($agency, 2),
            'subtotal' => round($subtotal, 2),
            'taxes' => round($taxes, 2),
            'total' => round($total, 2),
        ];
    }

    private function generateExcel(array $data, array $calculation): string
    {
        if (!class_exists(Spreadsheet::class)) {
            return '';
        }

        $dir = $_SERVER['DOCUMENT_ROOT'] . '/upload/promo_calculator';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $filename = 'kp_' . date('Ymd_His') . '_' . mt_rand(1000, 9999) . '.xlsx';
        $fullPath = $dir . '/' . $filename;

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(Loc::getMessage('PROMO_CALCULATOR_EXCEL_SHEET_TITLE'));

        $rows = [
            [Loc::getMessage('PROMO_CALCULATOR_EXCEL_TITLE'), ''],
            [Loc::getMessage('PROMO_CALCULATOR_EXCEL_PROMO_TYPE'), $data['promo_type']],
            [Loc::getMessage('PROMO_CALCULATOR_EXCEL_REQUIRED_STAFF'), $data['required_staff']],
            [Loc::getMessage('PROMO_CALCULATOR_EXCEL_PEOPLE_COUNT'), $data['people_count']],
            [Loc::getMessage('PROMO_CALCULATOR_EXCEL_HOURS_COUNT'), $data['hours_count']],
            [Loc::getMessage('PROMO_CALCULATOR_EXCEL_DAYS_COUNT'), $data['days_count']],
            [Loc::getMessage('PROMO_CALCULATOR_EXCEL_NAME'), $data['name']],
            [Loc::getMessage('PROMO_CALCULATOR_EXCEL_PHONE'), $data['phone']],
            [Loc::getMessage('PROMO_CALCULATOR_EXCEL_EMAIL'), $data['email']],
            [Loc::getMessage('PROMO_CALCULATOR_EXCEL_MESSAGE'), $data['message']],
            ['', ''],
            [Loc::getMessage('PROMO_CALCULATOR_EXCEL_BASE'), $calculation['base_personnel']],
            [Loc::getMessage('PROMO_CALCULATOR_EXCEL_PERSONNEL_TAX'), $calculation['personnel_taxes']],
            [Loc::getMessage('PROMO_CALCULATOR_EXCEL_PERSONNEL_TOTAL'), $calculation['personnel_total']],
            [Loc::getMessage('PROMO_CALCULATOR_EXCEL_MANAGEMENT'), $calculation['management']],
            [Loc::getMessage('PROMO_CALCULATOR_EXCEL_AGENCY'), $calculation['agency']],
            [Loc::getMessage('PROMO_CALCULATOR_EXCEL_SUBTOTAL'), $calculation['subtotal']],
            [Loc::getMessage('PROMO_CALCULATOR_EXCEL_TAXES'), $calculation['taxes']],
            [Loc::getMessage('PROMO_CALCULATOR_EXCEL_TOTAL'), $calculation['total']],
        ];

        $rowNum = 1;
        foreach ($rows as $row) {
            $sheet->setCellValue('A' . $rowNum, $row[0]);
            $sheet->setCellValue('B' . $rowNum, $row[1]);
            $rowNum++;
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($fullPath);

        return '/upload/promo_calculator/' . $filename;
    }

    private function saveToIblock(array $data, array $calculation, string $excelPath): void
    {
        if ($this->arParams['IBLOCK_ID'] <= 0 || !Loader::includeModule('iblock')) {
            return;
        }

        $element = new CIBlockElement();
        $fields = [
            'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
            'NAME' => Loc::getMessage('PROMO_CALCULATOR_IBLOCK_NAME_PREFIX') . ': ' . $data['name'] . ' (' . date('d.m.Y H:i') . ')',
            'ACTIVE' => 'Y',
            'PREVIEW_TEXT' => $data['message'],
            'PROPERTY_VALUES' => [
                'PROMO_TYPE' => $data['promo_type'],
                'REQUIRED_STAFF' => $data['required_staff'],
                'PEOPLE_COUNT' => $data['people_count'],
                'HOURS_COUNT' => $data['hours_count'],
                'DAYS_COUNT' => $data['days_count'],
                'PHONE' => $data['phone'],
                'EMAIL' => $data['email'],
                'TOTAL' => $calculation['total'],
                'EXCEL_FILE' => $excelPath,
            ],
        ];

        if (!$element->Add($fields)) {
            Debug::writeToFile($element->LAST_ERROR, 'promo_calculator_iblock_error', '/upload/promo_calculator.log');
        }
    }

    private function sendEmail(array $data, array $calculation, string $excelPath): void
    {
        if ($this->arParams['EMAIL_TO'] === '') {
            return;
        }

        Event::send([
            'EVENT_NAME' => 'PROMO_CALCULATOR_REQUEST',
            'LID' => SITE_ID,
            'C_FIELDS' => [
                'EMAIL_TO' => $this->arParams['EMAIL_TO'],
                'NAME' => $data['name'],
                'PHONE' => $data['phone'],
                'EMAIL' => $data['email'],
                'PROMO_TYPE' => $data['promo_type'],
                'TOTAL' => $calculation['total'],
                'MESSAGE' => $data['message'],
                'EXCEL_URL' => $excelPath,
            ],
        ]);
    }

    private function sendTelegram(array $data, array $calculation, string $excelPath): void
    {
        if ($this->arParams['TELEGRAM_BOT_TOKEN'] === '' || $this->arParams['TELEGRAM_CHAT_ID'] === '') {
            return;
        }

        $text = Loc::getMessage('PROMO_CALCULATOR_TELEGRAM_NEW_REQUEST') . '%0A'
            . Loc::getMessage('PROMO_CALCULATOR_TELEGRAM_NAME') . ': ' . $data['name'] . '%0A'
            . Loc::getMessage('PROMO_CALCULATOR_TELEGRAM_PHONE') . ': ' . $data['phone'] . '%0A'
            . Loc::getMessage('PROMO_CALCULATOR_TELEGRAM_EMAIL') . ': ' . $data['email'] . '%0A'
            . Loc::getMessage('PROMO_CALCULATOR_TELEGRAM_TYPE') . ': ' . $data['promo_type'] . '%0A'
            . Loc::getMessage('PROMO_CALCULATOR_TELEGRAM_TOTAL') . ': ' . $calculation['total'] . ' ' . Loc::getMessage('PROMO_CALCULATOR_CURRENCY_RUB') . '%0A'
            . Loc::getMessage('PROMO_CALCULATOR_TELEGRAM_FILE') . ': ' . $excelPath;

        $url = sprintf(
            'https://api.telegram.org/bot%s/sendMessage?chat_id=%s&text=%s',
            urlencode($this->arParams['TELEGRAM_BOT_TOKEN']),
            urlencode($this->arParams['TELEGRAM_CHAT_ID']),
            $text
        );

        @file_get_contents($url);
    }

    private function sendJson(array $payload): void
    {
        global $APPLICATION;
        $APPLICATION->RestartBuffer();
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        CMain::FinalActions();
        die();
    }
}
