<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Iblock\ElementTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\Response\AjaxJson;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Mail\Event;
use Bitrix\Main\PhoneNumber\Parser;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\HttpClient;

Loc::loadMessages(__FILE__);

class CustomFeedbackFormComponent extends CBitrixComponent implements Controllerable
{
    private const OPTION_MODULE_ID = 'custom.feedback.form';
    private const MAX_FILES = 10;
    private const MAX_FILE_SIZE = 5242880;

    public function onPrepareComponentParams($params): array
    {
        return [
            'IBLOCK_ID' => (int)($params['IBLOCK_ID'] ?? 0),
            'TELEGRAM_CHAT_ID' => trim((string)($params['TELEGRAM_CHAT_ID'] ?? '')),
            'MAIL_EVENT_NAME' => trim((string)($params['MAIL_EVENT_NAME'] ?? 'CUSTOM_FEEDBACK_FORM')),
            'GOOGLE_SHEETS_WEBHOOK_URL' => trim((string)($params['GOOGLE_SHEETS_WEBHOOK_URL'] ?? '')),
            'YANDEX_SMARTCAPTCHA_SITE_KEY' => trim((string)($params['YANDEX_SMARTCAPTCHA_SITE_KEY'] ?? '')),
            'GOOGLE_RECAPTCHA_SITE_KEY' => trim((string)($params['GOOGLE_RECAPTCHA_SITE_KEY'] ?? '')),
            'CACHE_TIME' => (int)($params['CACHE_TIME'] ?? 3600),
        ];
    }

    protected function listKeysSignedParameters(): array
    {
        return [
            'IBLOCK_ID',
            'TELEGRAM_CHAT_ID',
            'MAIL_EVENT_NAME',
            'GOOGLE_SHEETS_WEBHOOK_URL',
            'YANDEX_SMARTCAPTCHA_SITE_KEY',
            'GOOGLE_RECAPTCHA_SITE_KEY',
            'CACHE_TIME',
        ];
    }

    public function configureActions(): array
    {
        return [
            'submit' => [
                'prefilters' => [
                    new ActionFilter\Csrf(),
                    new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST]),
                ],
            ],
        ];
    }

    public function executeComponent(): void
    {
        $this->arResult['FORM_ID'] = 'custom-feedback-form-' . $this->randString(6);
        $this->arResult['SITE_ID'] = Context::getCurrent()->getSite();
        $this->arResult['SIGNED_PARAMETERS'] = $this->getSignedParameters();
        $this->includeComponentTemplate();
    }

    public function submitAction(): AjaxJson
    {
        try {
            $request = Context::getCurrent()->getRequest();
            $post = $request->getPostList()->toArray();

            $formData = $this->sanitize($post);
            $this->validate($formData);
            $this->validateCaptcha($formData, $request->getRemoteAddress());
            $fileIds = $this->handleFiles($request->getFileList()->toArray());

            $leadId = $this->saveToIblock($formData, $fileIds);
            $this->sendToTelegram($formData, $leadId);
            $this->sendToMail($formData, $fileIds, $leadId);
            $this->sendToGoogleSheets($formData, $leadId);

            return AjaxJson::createSuccess([
                'leadId' => $leadId,
                'message' => Loc::getMessage('CUSTOM_FEEDBACK_FORM_SUCCESS'),
            ]);
        } catch (\Throwable $exception) {
            $this->addError(new Error($exception->getMessage()));

            return AjaxJson::createError([
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function sanitize(array $post): array
    {
        return [
            'NAME' => trim((string)($post['NAME'] ?? '')),
            'PHONE' => trim((string)($post['PHONE'] ?? '')),
            'EMAIL' => trim((string)($post['EMAIL'] ?? '')),
            'CONTACT_METHOD' => array_values(array_filter((array)($post['CONTACT_METHOD'] ?? []))),
            'MESSAGE' => trim((string)($post['MESSAGE'] ?? '')),
            'PAGE_URL' => trim((string)($post['PAGE_URL'] ?? '')),
            'PAGE_TITLE' => trim((string)($post['PAGE_TITLE'] ?? '')),
            'REFERER' => trim((string)($post['REFERER'] ?? '')),
            'UTM_SOURCE' => trim((string)($post['UTM_SOURCE'] ?? '')),
            'UTM_MEDIUM' => trim((string)($post['UTM_MEDIUM'] ?? '')),
            'UTM_CAMPAIGN' => trim((string)($post['UTM_CAMPAIGN'] ?? '')),
            'UTM_TERM' => trim((string)($post['UTM_TERM'] ?? '')),
            'UTM_CONTENT' => trim((string)($post['UTM_CONTENT'] ?? '')),
            'HP_FIELD' => trim((string)($post['HP_FIELD'] ?? '')),
            'YANDEX_CAPTCHA_TOKEN' => trim((string)($post['YANDEX_CAPTCHA_TOKEN'] ?? '')),
            'GOOGLE_CAPTCHA_TOKEN' => trim((string)($post['GOOGLE_CAPTCHA_TOKEN'] ?? '')),
        ];
    }

    private function validate(array $data): void
    {
        if ($data['HP_FIELD'] !== '') {
            throw new ArgumentException(Loc::getMessage('CUSTOM_FEEDBACK_FORM_ERROR_SPAM'));
        }

        if ($data['NAME'] === '') {
            throw new ArgumentException(Loc::getMessage('CUSTOM_FEEDBACK_FORM_ERROR_NAME'));
        }

        if ($data['PHONE'] === '' || !Parser::getInstance()->parse($data['PHONE'])) {
            throw new ArgumentException(Loc::getMessage('CUSTOM_FEEDBACK_FORM_ERROR_PHONE'));
        }

        if ($data['EMAIL'] === '' || !check_email($data['EMAIL'])) {
            throw new ArgumentException(Loc::getMessage('CUSTOM_FEEDBACK_FORM_ERROR_EMAIL'));
        }

        if (empty($data['CONTACT_METHOD'])) {
            throw new ArgumentException(Loc::getMessage('CUSTOM_FEEDBACK_FORM_ERROR_CONTACT_METHOD'));
        }
    }

    private function validateCaptcha(array $data, string $ip): void
    {
        $yandexSecret = $this->getSecretConfigValue(
            'YANDEX_SMARTCAPTCHA_SECRET_KEY',
            'YANDEX_SMARTCAPTCHA_SECRET_KEY',
            'yandex_smartcaptcha_secret_key'
        );

        if ($yandexSecret !== '') {
            $client = new HttpClient(['socketTimeout' => 3, 'streamTimeout' => 3]);
            $result = $client->post('https://smartcaptcha.yandexcloud.net/validate', [
                'secret' => $yandexSecret,
                'token' => $data['YANDEX_CAPTCHA_TOKEN'],
                'ip' => $ip,
            ]);

            $decoded = json_decode((string)$result, true);
            if (($decoded['status'] ?? '') !== 'ok') {
                throw new SystemException(Loc::getMessage('CUSTOM_FEEDBACK_FORM_ERROR_CAPTCHA'));
            }
        }

        $googleSecret = $this->getSecretConfigValue(
            'GOOGLE_RECAPTCHA_SECRET_KEY',
            'GOOGLE_RECAPTCHA_SECRET_KEY',
            'google_recaptcha_secret_key'
        );

        if ($googleSecret !== '') {
            $client = new HttpClient(['socketTimeout' => 3, 'streamTimeout' => 3]);
            $result = $client->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $googleSecret,
                'response' => $data['GOOGLE_CAPTCHA_TOKEN'],
                'remoteip' => $ip,
            ]);

            $decoded = json_decode((string)$result, true);
            if (!(bool)($decoded['success'] ?? false)) {
                throw new SystemException(Loc::getMessage('CUSTOM_FEEDBACK_FORM_ERROR_CAPTCHA'));
            }
        }
    }

    private function handleFiles(array $files): array
    {
        $result = [];
        $uploadedFiles = $files['FILES'] ?? [];

        if (!is_array($uploadedFiles) || empty($uploadedFiles['name'])) {
            return [];
        }

        $count = count((array)$uploadedFiles['name']);
        if ($count > self::MAX_FILES) {
            throw new ArgumentException(Loc::getMessage('CUSTOM_FEEDBACK_FORM_ERROR_FILE_COUNT'));
        }

        for ($i = 0; $i < $count; $i++) {
            if ((int)$uploadedFiles['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }

            if ((int)$uploadedFiles['size'][$i] > self::MAX_FILE_SIZE) {
                throw new ArgumentException(Loc::getMessage('CUSTOM_FEEDBACK_FORM_ERROR_FILE_SIZE'));
            }

            $fileArray = [
                'name' => $uploadedFiles['name'][$i],
                'type' => $uploadedFiles['type'][$i],
                'tmp_name' => $uploadedFiles['tmp_name'][$i],
                'error' => $uploadedFiles['error'][$i],
                'size' => $uploadedFiles['size'][$i],
            ];

            $fileId = CFile::SaveFile($fileArray, 'custom_feedback_form');
            if ($fileId) {
                $result[] = (int)$fileId;
            }
        }

        return $result;
    }

    private function saveToIblock(array $data, array $fileIds): int
    {
        if ($this->arParams['IBLOCK_ID'] <= 0 || !Loader::includeModule('iblock')) {
            return 0;
        }

        $el = new CIBlockElement();
        $fields = [
            'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
            'NAME' => $data['NAME'] . ' ' . date('d.m.Y H:i:s'),
            'ACTIVE' => 'Y',
            'PROPERTY_VALUES' => [
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
                'FILES' => array_map(static fn(int $id) => ['VALUE' => $id], $fileIds),
            ],
        ];

        $leadId = (int)$el->Add($fields);

        return $leadId > 0 ? $leadId : 0;
    }

    private function sendToTelegram(array $data, int $leadId): void
    {
        $telegramToken = $this->getSecretConfigValue('TELEGRAM_BOT_TOKEN', 'TELEGRAM_BOT_TOKEN', 'telegram_bot_token');

        if ($telegramToken === '' || $this->arParams['TELEGRAM_CHAT_ID'] === '') {
            return;
        }

        $message = sprintf(
            "%s\nID: %d\n%s: %s\n%s: %s\n%s: %s\n%s: %s\n%s: %s",
            Loc::getMessage('CUSTOM_FEEDBACK_FORM_TELEGRAM_TITLE'),
            $leadId,
            Loc::getMessage('CUSTOM_FEEDBACK_FORM_FIELD_NAME'),
            $data['NAME'],
            Loc::getMessage('CUSTOM_FEEDBACK_FORM_FIELD_PHONE'),
            $data['PHONE'],
            Loc::getMessage('CUSTOM_FEEDBACK_FORM_FIELD_EMAIL'),
            $data['EMAIL'],
            Loc::getMessage('CUSTOM_FEEDBACK_FORM_FIELD_CONTACT_METHOD'),
            implode(', ', $data['CONTACT_METHOD']),
            Loc::getMessage('CUSTOM_FEEDBACK_FORM_FIELD_MESSAGE'),
            $data['MESSAGE']
        );

        $client = new HttpClient(['socketTimeout' => 2, 'streamTimeout' => 2]);
        $client->post(
            'https://api.telegram.org/bot' . $telegramToken . '/sendMessage',
            [
                'chat_id' => $this->arParams['TELEGRAM_CHAT_ID'],
                'text' => $message,
            ]
        );
    }

    private function sendToMail(array $data, array $fileIds, int $leadId): void
    {
        Event::sendImmediate([
            'EVENT_NAME' => $this->arParams['MAIL_EVENT_NAME'],
            'LID' => Context::getCurrent()->getSite(),
            'C_FIELDS' => [
                'LEAD_ID' => $leadId,
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
            ],
            'FILE' => $fileIds,
        ]);
    }

    private function sendToGoogleSheets(array $data, int $leadId): void
    {
        if ($this->arParams['GOOGLE_SHEETS_WEBHOOK_URL'] === '') {
            return;
        }

        $payload = [
            'lead_id' => $leadId,
            'date' => date(DATE_ATOM),
            'name' => $data['NAME'],
            'phone' => $data['PHONE'],
            'email' => $data['EMAIL'],
            'contact_method' => implode(', ', $data['CONTACT_METHOD']),
            'message' => $data['MESSAGE'],
            'page_url' => $data['PAGE_URL'],
            'page_title' => $data['PAGE_TITLE'],
            'referer' => $data['REFERER'],
            'utm_source' => $data['UTM_SOURCE'],
            'utm_medium' => $data['UTM_MEDIUM'],
            'utm_campaign' => $data['UTM_CAMPAIGN'],
            'utm_term' => $data['UTM_TERM'],
            'utm_content' => $data['UTM_CONTENT'],
        ];

        $client = new HttpClient(['socketTimeout' => 2, 'streamTimeout' => 2]);
        $client->setHeader('Content-Type', 'application/json');
        $client->post($this->arParams['GOOGLE_SHEETS_WEBHOOK_URL'], json_encode($payload, JSON_UNESCAPED_UNICODE));
    }

    private function getSecretConfigValue(string $legacyParamKey, string $envKey, string $optionKey): string
    {
        $value = trim((string)getenv($envKey));
        if ($value !== '') {
            return $value;
        }

        $value = trim((string)Option::get(self::OPTION_MODULE_ID, $optionKey, ''));
        if ($value !== '') {
            return $value;
        }

        return trim((string)($this->arParams[$legacyParamKey] ?? ''));
    }
}
