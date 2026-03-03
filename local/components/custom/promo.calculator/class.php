<?php

use Bitrix\Main\Context;
use Bitrix\Main\Diag\Debug;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Mail\Event;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

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
        $params['EXCEL_CONTACT_WEBSITE'] = trim((string)($params['EXCEL_CONTACT_WEBSITE'] ?? Loc::getMessage('PROMO_CALCULATOR_DEFAULT_EXCEL_CONTACT_WEBSITE')));
        $params['EXCEL_CONTACT_EMAIL'] = trim((string)($params['EXCEL_CONTACT_EMAIL'] ?? Loc::getMessage('PROMO_CALCULATOR_DEFAULT_EXCEL_CONTACT_EMAIL')));
        $params['EXCEL_CONTACT_PHONE'] = trim((string)($params['EXCEL_CONTACT_PHONE'] ?? Loc::getMessage('PROMO_CALCULATOR_DEFAULT_EXCEL_CONTACT_PHONE')));
        $params['EXCEL_COMPANY_NAME'] = trim((string)($params['EXCEL_COMPANY_NAME'] ?? Loc::getMessage('PROMO_CALCULATOR_DEFAULT_EXCEL_COMPANY_NAME')));

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
            'required_staff' => $request->getPost('required_staff'),
            'people_count' => (int)$request->getPost('people_count'),
            'hours_count' => (int)$request->getPost('hours_count'),
            'days_count' => (int)$request->getPost('days_count'),
            'name' => trim((string)$request->getPost('name')),
            'phone' => trim((string)$request->getPost('phone')),
            'email' => trim((string)$request->getPost('email')),
            'message' => trim((string)$request->getPost('message')),
        ];


        if (!is_array($data['required_staff'])) {
            $data['required_staff'] = [$data['required_staff']];
        }

        $data['required_staff'] = array_values(array_filter(array_map('trim', $data['required_staff']), static function ($value) {
            return $value !== '';
        }));

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

        if (!$data['required_staff']) {
            return Loc::getMessage('PROMO_CALCULATOR_ERROR_REQUIRED_STAFF');
        }

        foreach ($data['required_staff'] as $requiredStaff) {
            if (!in_array($requiredStaff, $this->arResult['REQUIRED_STAFF_OPTIONS'], true)) {
                return Loc::getMessage('PROMO_CALCULATOR_ERROR_REQUIRED_STAFF');
            }
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
        $basePersonnel = $ratePerDay * $data['people_count'] * $data['days_count'] * $data['hours_count'];
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

        $sheet->getDefaultColumnDimension()->setWidth(14);
        $sheet->getColumnDimension('C')->setWidth(38);
        $sheet->getColumnDimension('I')->setWidth(30);
        $sheet->getColumnDimension('J')->setWidth(18);

        $estimateTitle = Loc::getMessage('PROMO_CALCULATOR_EXCEL_ESTIMATE_TITLE') . ' ' . date('d.m.Y H:i');
        $sheet->mergeCells('A1:J1');
        $sheet->setCellValue('A1', $estimateTitle);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $sheet->mergeCells('B2:D4');
        $sheet->setCellValue('B2', $this->arParams['EXCEL_COMPANY_NAME']);
        $sheet->getStyle('B2:D4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('B2:D4')->getFont()->setBold(true)->setSize(24);

        $sheet->setCellValue('E2', $this->arParams['EXCEL_CONTACT_WEBSITE']);
        $sheet->setCellValue('E3', $this->arParams['EXCEL_CONTACT_EMAIL']);
        $sheet->setCellValue('E4', $this->arParams['EXCEL_CONTACT_PHONE']);
        $sheet->getStyle('E2:E4')->getFont()->setBold(true);

        $headers = [
            'A5' => Loc::getMessage('PROMO_CALCULATOR_EXCEL_COL_MECHANIC'),
            'B5' => Loc::getMessage('PROMO_CALCULATOR_EXCEL_COL_LOCATION_TYPE'),
            'C5' => Loc::getMessage('PROMO_CALCULATOR_EXCEL_COL_PROMO_STAFF'),
            'D5' => Loc::getMessage('PROMO_CALCULATOR_EXCEL_COL_RATE_HOUR'),
            'E5' => Loc::getMessage('PROMO_CALCULATOR_EXCEL_COL_TOTAL_STAFF'),
            'F5' => Loc::getMessage('PROMO_CALCULATOR_EXCEL_COL_WORK_DAYS'),
            'G5' => Loc::getMessage('PROMO_CALCULATOR_EXCEL_COL_OUTLETS'),
            'H5' => Loc::getMessage('PROMO_CALCULATOR_EXCEL_COL_HOURS_PER_DAY'),
            'I5' => Loc::getMessage('PROMO_CALCULATOR_EXCEL_COL_ADDITIONAL_REQUIREMENTS'),
            'J5' => Loc::getMessage('PROMO_CALCULATOR_EXCEL_COL_ROW_TOTAL'),
        ];

        foreach ($headers as $cell => $label) {
            $sheet->setCellValue($cell, $label);
        }

        $sheet->getStyle('A5:J5')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D9D9D9'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        $sheet->setCellValue('A6', Loc::getMessage('PROMO_CALCULATOR_EXCEL_ROW_MECHANIC_DEFAULT'));
        $sheet->setCellValue('B6', Loc::getMessage('PROMO_CALCULATOR_EXCEL_ROW_LOCATION_DEFAULT'));
        $sheet->setCellValue('C6', implode(', ', $data['required_staff']));
        $sheet->setCellValue('D6', $calculation['rate_per_day']);
        $sheet->setCellValue('E6', $data['people_count']);
        $sheet->setCellValue('F6', $data['days_count']);
        $sheet->setCellValue('G6', 1);
        $sheet->setCellValue('H6', $data['hours_count']);
        $sheet->setCellValue('I6', $data['message'] !== '' ? $data['message'] : Loc::getMessage('PROMO_CALCULATOR_EXCEL_ROW_NO_REQUIREMENTS'));
        $sheet->setCellValue('J6', $calculation['base_personnel']);

        $sheet->getStyle('A6:J6')->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        foreach (['D6', 'J6', 'J9', 'J10', 'J11', 'J12', 'J13', 'J14', 'J15'] as $moneyCell) {
            $sheet->getStyle($moneyCell)->getNumberFormat()->setFormatCode('#,##0.00');
        }

        $summaryRows = [
            9 => [Loc::getMessage('PROMO_CALCULATOR_EXCEL_TOTAL'), $calculation['base_personnel']],
            10 => [Loc::getMessage('PROMO_CALCULATOR_EXCEL_PERSONNEL_TAX'), $calculation['personnel_taxes']],
            11 => [Loc::getMessage('PROMO_CALCULATOR_EXCEL_PERSONNEL_TOTAL'), $calculation['personnel_total']],
            12 => [Loc::getMessage('PROMO_CALCULATOR_EXCEL_MANAGEMENT'), $calculation['management']],
            13 => [Loc::getMessage('PROMO_CALCULATOR_EXCEL_AGENCY'), $calculation['agency']],
            14 => [Loc::getMessage('PROMO_CALCULATOR_EXCEL_SUBTOTAL'), $calculation['subtotal']],
            15 => [Loc::getMessage('PROMO_CALCULATOR_EXCEL_TOTAL_WITH_TAX'), $calculation['total']],
        ];

        foreach ($summaryRows as $rowNum => $summaryRow) {
            $sheet->setCellValue('I' . $rowNum, $summaryRow[0]);
            $sheet->setCellValue('J' . $rowNum, $summaryRow[1]);
        }

        $sheet->getStyle('I9:J15')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_RIGHT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);
        $sheet->getStyle('I10:I10')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('92D050');
        $sheet->getStyle('J10:J10')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('9DC3E6');
        $sheet->getStyle('I13:I13')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('92D050');
        $sheet->getStyle('J13:J13')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('9DC3E6');

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
                'REQUIRED_STAFF' => implode(', ', $data['required_staff']),
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
                'MESSAGE' => $data['message'] . ' | ' . Loc::getMessage('PROMO_CALCULATOR_REQUIRED_STAFF_LABEL') . ': ' . implode(', ', $data['required_staff']),
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
            . Loc::getMessage('PROMO_CALCULATOR_REQUIRED_STAFF_LABEL') . ': ' . implode(', ', $data['required_staff']) . '%0A'
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
