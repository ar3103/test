<?php

use Bitrix\Main\Context;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Loader;
use Bitrix\Main\Mail\Event;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web\HttpClient;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class AcmeLeadFormComponent extends CBitrixComponent
{
    private const HONEYPOT_FIELD = 'company_name';

    public function onPrepareComponentParams($arParams): array
    {
        $arParams['IBLOCK_ID'] = (int)($arParams['IBLOCK_ID'] ?? 0);
        $arParams['TELEGRAM_BOT_TOKEN'] = trim((string)($arParams['TELEGRAM_BOT_TOKEN'] ?? ''));
        $arParams['TELEGRAM_CHAT_ID'] = trim((string)($arParams['TELEGRAM_CHAT_ID'] ?? ''));
        $arParams['YANDEX_SMARTCAPTCHA_SECRET_KEY'] = trim((string)($arParams['YANDEX_SMARTCAPTCHA_SECRET_KEY'] ?? ''));
        $arParams['GOOGLE_RECAPTCHA_SECRET_KEY'] = trim((string)($arParams['GOOGLE_RECAPTCHA_SECRET_KEY'] ?? ''));
        $arParams['GOOGLE_SHEETS_WEBHOOK_URL'] = trim((string)($arParams['GOOGLE_SHEETS_WEBHOOK_URL'] ?? ''));
        $arParams['EMAIL_TO'] = trim((string)($arParams['EMAIL_TO'] ?? ''));

        return $arParams;
    }

    public function executeComponent(): void
    {
        $request = Context::getCurrent()->getRequest();

        if ($request->isPost() && $request->getPost('ACME_LEAD_FORM_AJAX') === 'Y') {
            $this->processAjax();
            return;
        }

        $this->arResult['FORM_ID'] = 'acme-lead-form-' . $this->randString(6);
        $this->includeComponentTemplate();
    }

    private function processAjax(): void
    {
        global $APPLICATION;

        $APPLICATION->RestartBuffer();
        header('Content-Type: application/json; charset=UTF-8');

        if (!check_bitrix_sessid()) {
            $this->sendJson(['success' => false, 'message' => GetMessage('ACME_LEAD_FORM_ERROR_SESSID')]);
        }

        if (!$this->validateSpamProtection()) {
            $this->sendJson(['success' => false, 'message' => GetMessage('ACME_LEAD_FORM_ERROR_SPAM')]);
        }

        $data = $this->collectData();
        $errors = $this->validateData($data);

        if (!empty($errors)) {
            $this->sendJson(['success' => false, 'message' => implode(PHP_EOL, $errors)]);
        }

        $savedFiles = $this->saveFiles();
        $leadId = $this->saveToIBlock($data);

        $this->sendToTelegram($data, $leadId);
        $this->sendToEmail($data, $savedFiles, $leadId);
        $this->sendToGoogleSheets($data, $leadId);

        $this->sendJson([
            'success' => true,
            'message' => GetMessage('ACME_LEAD_FORM_SUCCESS'),
            'lead_id' => $leadId,
        ]);
    }

    private function validateSpamProtection(): bool
    {
        $request = Context::getCurrent()->getRequest();
        if (trim((string)$request->getPost(self::HONEYPOT_FIELD)) !== '') {
            return false;
        }

        return $this->checkYandexCaptcha() && $this->checkGoogleCaptcha();
    }

    private function checkYandexCaptcha(): bool
    {
        $secret = $this->arParams['YANDEX_SMARTCAPTCHA_SECRET_KEY'];
        if ($secret === '') {
            return true;
        }

        $token = trim((string)Context::getCurrent()->getRequest()->getPost('smart-token'));
        if ($token === '') {
            return false;
        }

        $client = new HttpClient(['socketTimeout' => 5, 'streamTimeout' => 5]);
        $response = $client->post('https://smartcaptcha.yandexcloud.net/validate', [
            'secret' => $secret,
            'token' => $token,
            'ip' => Context::getCurrent()->getRequest()->getRemoteAddress(),
        ]);

        if ($response === false) {
            return false;
        }

        $decoded = \Bitrix\Main\Web\Json::decode($response);

        return ($decoded['status'] ?? '') === 'ok';
    }

    private function checkGoogleCaptcha(): bool
    {
        $secret = $this->arParams['GOOGLE_RECAPTCHA_SECRET_KEY'];
        if ($secret === '') {
            return true;
        }

        $token = trim((string)Context::getCurrent()->getRequest()->getPost('g-recaptcha-response'));
        if ($token === '') {
            return false;
        }

        $client = new HttpClient(['socketTimeout' => 5, 'streamTimeout' => 5]);
        $response = $client->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $secret,
            'response' => $token,
            'remoteip' => Context::getCurrent()->getRequest()->getRemoteAddress(),
        ]);

        if ($response === false) {
            return false;
        }

        $decoded = \Bitrix\Main\Web\Json::decode($response);

        return (bool)($decoded['success'] ?? false);
    }

    private function collectData(): array
    {
        $request = Context::getCurrent()->getRequest();

        return [
            'NAME' => trim((string)$request->getPost('NAME')),
            'PHONE' => trim((string)$request->getPost('PHONE')),
            'EMAIL' => trim((string)$request->getPost('EMAIL')),
            'CONTACT_METHOD' => (array)$request->getPost('CONTACT_METHOD'),
            'MESSAGE' => trim((string)$request->getPost('MESSAGE')),
            'PAGE_URL' => trim((string)$request->getPost('PAGE_URL')),
            'PAGE_TITLE' => trim((string)$request->getPost('PAGE_TITLE')),
            'REFERER' => trim((string)$request->getPost('REFERER')),
            'UTM_SOURCE' => trim((string)$request->getPost('UTM_SOURCE')),
            'UTM_MEDIUM' => trim((string)$request->getPost('UTM_MEDIUM')),
            'UTM_CAMPAIGN' => trim((string)$request->getPost('UTM_CAMPAIGN')),
            'UTM_TERM' => trim((string)$request->getPost('UTM_TERM')),
            'UTM_CONTENT' => trim((string)$request->getPost('UTM_CONTENT')),
            'SITE_ID' => SITE_ID,
            'USER_ID' => (int)CurrentUser::get()->getId(),
        ];
    }

    private function validateData(array $data): array
    {
        $errors = [];

        if ($data['NAME'] === '') {
            $errors[] = GetMessage('ACME_LEAD_FORM_ERROR_NAME');
        }

        if ($data['PHONE'] === '') {
            $errors[] = GetMessage('ACME_LEAD_FORM_ERROR_PHONE');
        }

        if ($data['EMAIL'] !== '' && !check_email($data['EMAIL'])) {
            $errors[] = GetMessage('ACME_LEAD_FORM_ERROR_EMAIL');
        }

        if (mb_strlen($data['MESSAGE']) > 5000) {
            $errors[] = GetMessage('ACME_LEAD_FORM_ERROR_MESSAGE_LENGTH');
        }

        return $errors;
    }

    private function saveFiles(): array
    {
        $savedFileIds = [];

        if (empty($_FILES['ATTACHMENTS']) || !is_array($_FILES['ATTACHMENTS']['name'])) {
            return $savedFileIds;
        }

        $fileCount = count($_FILES['ATTACHMENTS']['name']);

        for ($i = 0; $i < $fileCount; $i++) {
            if ((int)$_FILES['ATTACHMENTS']['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }

            $file = [
                'name' => $_FILES['ATTACHMENTS']['name'][$i],
                'type' => $_FILES['ATTACHMENTS']['type'][$i],
                'tmp_name' => $_FILES['ATTACHMENTS']['tmp_name'][$i],
                'error' => $_FILES['ATTACHMENTS']['error'][$i],
                'size' => $_FILES['ATTACHMENTS']['size'][$i],
                'MODULE_ID' => 'iblock',
            ];

            $fileId = CFile::SaveFile($file, 'acme_lead_form');
            if ($fileId) {
                $savedFileIds[] = (int)$fileId;
            }
        }

        return $savedFileIds;
    }

    private function saveToIBlock(array $data): int
    {
        if ($this->arParams['IBLOCK_ID'] <= 0 || !Loader::includeModule('iblock')) {
            return 0;
        }

        $text = [
            'Name: ' . $data['NAME'],
            'Phone: ' . $data['PHONE'],
            'Email: ' . $data['EMAIL'],
            'Contact method: ' . implode(', ', $data['CONTACT_METHOD']),
            'Message: ' . $data['MESSAGE'],
            'Page URL: ' . $data['PAGE_URL'],
            'Page title: ' . $data['PAGE_TITLE'],
            'Referer: ' . $data['REFERER'],
            'UTM source: ' . $data['UTM_SOURCE'],
            'UTM medium: ' . $data['UTM_MEDIUM'],
            'UTM campaign: ' . $data['UTM_CAMPAIGN'],
            'UTM term: ' . $data['UTM_TERM'],
            'UTM content: ' . $data['UTM_CONTENT'],
            'Site ID: ' . $data['SITE_ID'],
        ];

        $el = new CIBlockElement();
        $id = $el->Add([
            'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
            'ACTIVE' => 'Y',
            'NAME' => $data['NAME'] . ' [' . $data['PHONE'] . ']',
            'PREVIEW_TEXT' => implode(PHP_EOL, $text),
            'PREVIEW_TEXT_TYPE' => 'text',
            'DATE_ACTIVE_FROM' => ConvertTimeStamp(time(), 'FULL'),
        ]);

        return $id ? (int)$id : 0;
    }

    private function sendToTelegram(array $data, int $leadId): void
    {
        if ($this->arParams['TELEGRAM_BOT_TOKEN'] === '' || $this->arParams['TELEGRAM_CHAT_ID'] === '') {
            return;
        }

        $message = [
            '📩 ' . GetMessage('ACME_LEAD_FORM_TELEGRAM_TITLE'),
            'ID: ' . $leadId,
            'Date: ' . (new DateTime())->toString(),
            'Name: ' . $data['NAME'],
            'Phone: ' . $data['PHONE'],
            'Email: ' . $data['EMAIL'],
            'Contact: ' . implode(', ', $data['CONTACT_METHOD']),
            'Message: ' . $data['MESSAGE'],
            'Page: ' . $data['PAGE_TITLE'] . ' (' . $data['PAGE_URL'] . ')',
            'Referer: ' . $data['REFERER'],
            'UTM: ' . implode(', ', [
                $data['UTM_SOURCE'],
                $data['UTM_MEDIUM'],
                $data['UTM_CAMPAIGN'],
                $data['UTM_TERM'],
                $data['UTM_CONTENT'],
            ]),
            'Site: ' . $data['SITE_ID'],
        ];

        $client = new HttpClient(['socketTimeout' => 4, 'streamTimeout' => 4]);
        $client->setHeader('Content-Type', 'application/json', true);
        $client->post(
            'https://api.telegram.org/bot' . $this->arParams['TELEGRAM_BOT_TOKEN'] . '/sendMessage',
            \Bitrix\Main\Web\Json::encode([
                'chat_id' => $this->arParams['TELEGRAM_CHAT_ID'],
                'text' => implode("\n", $message),
            ])
        );
    }

    private function sendToEmail(array $data, array $savedFileIds, int $leadId): void
    {
        if ($this->arParams['EMAIL_TO'] === '') {
            return;
        }

        Event::sendImmediate([
            'EVENT_NAME' => 'ACME_LEAD_FORM',
            'LID' => $data['SITE_ID'],
            'C_FIELDS' => [
                'LEAD_ID' => $leadId,
                'DATE_CREATE' => (new DateTime())->toString(),
                'NAME' => $data['NAME'],
                'PHONE' => $data['PHONE'],
                'EMAIL' => $data['EMAIL'],
                'CONTACT_METHOD' => implode(', ', $data['CONTACT_METHOD']),
                'MESSAGE' => $data['MESSAGE'],
                'PAGE_URL' => $data['PAGE_URL'],
                'PAGE_TITLE' => $data['PAGE_TITLE'],
                'REFERER' => $data['REFERER'],
                'UTM_SOURCE' => $data['UTM_SOURCE'],
                'UTM_MEDIUM' => $data['UTM_MEDIUM'],
                'UTM_CAMPAIGN' => $data['UTM_CAMPAIGN'],
                'UTM_TERM' => $data['UTM_TERM'],
                'UTM_CONTENT' => $data['UTM_CONTENT'],
                'EMAIL_TO' => $this->arParams['EMAIL_TO'],
            ],
            'FILE' => $savedFileIds,
        ]);
    }

    private function sendToGoogleSheets(array $data, int $leadId): void
    {
        if ($this->arParams['GOOGLE_SHEETS_WEBHOOK_URL'] === '') {
            return;
        }

        $payload = [
            'headers' => [
                'Номер заявки',
                'Дата заявки',
                'ФИО',
                'Телефон',
                'Email',
                'Способ связи',
                'Сообщение',
                'Страница оформления заявки',
                'Referer',
                'UTM source',
                'UTM medium',
                'UTM campaign',
                'UTM term',
                'UTM content',
            ],
            'row' => [
                $leadId,
                (new DateTime())->toString(),
                $data['NAME'],
                $data['PHONE'],
                $data['EMAIL'],
                implode(', ', $data['CONTACT_METHOD']),
                $data['MESSAGE'],
                $data['PAGE_URL'],
                $data['REFERER'],
                $data['UTM_SOURCE'],
                $data['UTM_MEDIUM'],
                $data['UTM_CAMPAIGN'],
                $data['UTM_TERM'],
                $data['UTM_CONTENT'],
            ],
        ];

        $client = new HttpClient(['socketTimeout' => 4, 'streamTimeout' => 4]);
        $client->setHeader('Content-Type', 'application/json', true);
        $client->post($this->arParams['GOOGLE_SHEETS_WEBHOOK_URL'], \Bitrix\Main\Web\Json::encode($payload));
    }

    private function sendJson(array $payload): void
    {
        echo \Bitrix\Main\Web\Json::encode($payload);
        CMain::FinalActions();
        die();
    }
}
