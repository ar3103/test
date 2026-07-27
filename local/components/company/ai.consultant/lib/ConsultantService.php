<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Web\HttpClient;

class ConsultantService
{
    private string $siteId;
    private string $dialogIblockCode;
    private string $faqIblockCode;
    private string $telegramChatId;

    public function __construct(string $siteId, string $dialogIblockCode, string $faqIblockCode, string $telegramChatId)
    {
        $this->siteId = $siteId;
        $this->dialogIblockCode = $dialogIblockCode;
        $this->faqIblockCode = $faqIblockCode;
        $this->telegramChatId = $telegramChatId;
    }

    public function processMessage(string $sessionId, string $message, array $contacts = []): array
    {
        $dialog = $this->getDialogBySession($sessionId);
        if (!$dialog) {
            $dialog = $this->createDialog($sessionId, $contacts);
        }

        $contacts = $this->mergeContacts($dialog['CONTACTS'] ?? [], $contacts);

        $history = $dialog['HISTORY'] ?? [];
        $history[] = ['role' => 'user', 'content' => $message, 'date' => date('c')];

        $faqAnswer = $this->findFaqAnswer($message);
        $aiAnswer = $faqAnswer ?: $this->requestOpenAi($history, $contacts);

        $history[] = ['role' => 'assistant', 'content' => $aiAnswer, 'date' => date('c')];

        $this->updateDialog($dialog['ID'], $history, $contacts, $message);
        $this->sendNotifications($dialog['ID'], $sessionId, $contacts, $message, $aiAnswer);

        return [
            'answer' => $aiAnswer,
            'needContacts' => empty($contacts['name']) || empty($contacts['phone']) || empty($contacts['topic']),
            'history' => $history,
        ];
    }

    public function rateDialog(string $sessionId, int $rating, string $comment = ''): bool
    {
        $dialog = $this->getDialogBySession($sessionId);
        if (!$dialog) {
            return false;
        }

        CIBlockElement::SetPropertyValuesEx((int)$dialog['ID'], (int)$dialog['IBLOCK_ID'], [
            'RATING' => $rating,
            'RATING_COMMENT' => $comment,
            'STATUS' => 'CLOSED',
            'CLOSED_AT' => date('d.m.Y H:i:s'),
        ]);

        return true;
    }

    private function requestOpenAi(array $history, array $contacts): string
    {
        $apiKey = (string)COption::GetOptionString('company.ai_consultant', 'openai_api_key', '');
        if ($apiKey === '') {
            return 'Сейчас оператор недоступен, но я уже передал ваш запрос. Оставьте, пожалуйста, имя, телефон и что вас интересует.';
        }

        $systemPrompt = 'Ты онлайн-консультант сайта. Отвечай естественно и кратко, как живой менеджер. '
            . 'Обязательно уточни имя, телефон и что интересует клиента, если этих данных нет.';

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
        ];

        if (!empty($contacts['name']) || !empty($contacts['phone']) || !empty($contacts['topic'])) {
            $messages[] = [
                'role' => 'system',
                'content' => 'Уже известные контакты: ' . json_encode($contacts, JSON_UNESCAPED_UNICODE),
            ];
        }

        foreach ($history as $entry) {
            $messages[] = [
                'role' => $entry['role'],
                'content' => $entry['content'],
            ];
        }

        $client = new HttpClient(['socketTimeout' => 20]);
        $client->setHeader('Authorization', 'Bearer ' . $apiKey);
        $client->setHeader('Content-Type', 'application/json');
        $response = $client->post('https://api.openai.com/v1/chat/completions', json_encode([
            'model' => COption::GetOptionString('company.ai_consultant', 'openai_model', 'gpt-4o-mini'),
            'messages' => $messages,
            'temperature' => 0.7,
        ], JSON_UNESCAPED_UNICODE));

        if (!$response) {
            return 'Не получилось сразу ответить. Напишите, пожалуйста, ваш номер телефона — менеджер свяжется с вами в ближайшее время.';
        }

        $data = json_decode($response, true);
        return (string)($data['choices'][0]['message']['content'] ?? 'Спасибо! Передал ваш запрос менеджеру.');
    }

    private function findFaqAnswer(string $message): string
    {
        if (!Loader::includeModule('iblock')) {
            return '';
        }

        $iblockId = $this->findIblockIdByCode($this->faqIblockCode);
        if (!$iblockId) {
            return '';
        }

        $result = CIBlockElement::GetList(
            ['SORT' => 'ASC'],
            [
                'IBLOCK_ID' => $iblockId,
                'ACTIVE' => 'Y',
                '%SEARCHABLE_CONTENT' => $message,
            ],
            false,
            ['nTopCount' => 1],
            ['ID', 'NAME', 'DETAIL_TEXT']
        );

        if ($item = $result->GetNext()) {
            return trim((string)$item['DETAIL_TEXT']);
        }

        return '';
    }

    private function createDialog(string $sessionId, array $contacts): array
    {
        Loader::includeModule('iblock');
        $iblockId = $this->findIblockIdByCode($this->dialogIblockCode);

        $el = new CIBlockElement();
        $dialogId = (int)$el->Add([
            'IBLOCK_ID' => $iblockId,
            'NAME' => 'Диалог ' . $sessionId,
            'ACTIVE' => 'Y',
            'PROPERTY_VALUES' => [
                'SESSION_ID' => $sessionId,
                'CONTACT_NAME' => (string)($contacts['name'] ?? ''),
                'PHONE' => (string)($contacts['phone'] ?? ''),
                'TOPIC' => (string)($contacts['topic'] ?? ''),
                'HISTORY' => json_encode([], JSON_UNESCAPED_UNICODE),
                'STATUS' => 'OPEN',
            ],
        ]);

        return [
            'ID' => $dialogId,
            'IBLOCK_ID' => $iblockId,
            'HISTORY' => [],
        ];
    }

    private function updateDialog(int $dialogId, array $history, array $contacts, string $topic): void
    {
        $iblockId = $this->findIblockIdByCode($this->dialogIblockCode);

        CIBlockElement::SetPropertyValuesEx($dialogId, $iblockId, [
            'HISTORY' => json_encode($history, JSON_UNESCAPED_UNICODE),
            'CONTACT_NAME' => (string)($contacts['name'] ?? ''),
            'PHONE' => (string)($contacts['phone'] ?? ''),
            'TOPIC' => (string)($contacts['topic'] ?? $topic),
            'UPDATED_AT' => date('d.m.Y H:i:s'),
        ]);
    }

    private function sendNotifications(int $dialogId, string $sessionId, array $contacts, string $question, string $answer): void
    {
        $payload = [
            'DIALOG_ID' => $dialogId,
            'SESSION_ID' => $sessionId,
            'NAME' => (string)($contacts['name'] ?? ''),
            'PHONE' => (string)($contacts['phone'] ?? ''),
            'TOPIC' => (string)($contacts['topic'] ?? ''),
            'QUESTION' => $question,
            'ANSWER' => $answer,
        ];

        CEvent::SendImmediate('AI_DIALOG_EVENT', $this->siteId, $payload);

        $botToken = (string)COption::GetOptionString('company.ai_consultant', 'telegram_bot_token', '');
        if ($botToken === '' || $this->telegramChatId === '') {
            return;
        }

        $text = "Новый диалог #{$dialogId}\n"
            . "Сессия: {$sessionId}\n"
            . "Имя: " . ($payload['NAME'] ?: '-') . "\n"
            . "Телефон: " . ($payload['PHONE'] ?: '-') . "\n"
            . "Интерес: " . ($payload['TOPIC'] ?: '-') . "\n"
            . "Вопрос: {$question}\n"
            . "Ответ: {$answer}";

        $client = new HttpClient(['socketTimeout' => 10]);
        $client->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
            'chat_id' => $this->telegramChatId,
            'text' => Encoding::convertEncodingToCurrent($text),
        ]);
    }

    private function getDialogBySession(string $sessionId): ?array
    {
        if (!Loader::includeModule('iblock')) {
            return null;
        }

        $iblockId = $this->findIblockIdByCode($this->dialogIblockCode);
        if (!$iblockId) {
            return null;
        }

        $result = CIBlockElement::GetList(
            ['ID' => 'DESC'],
            ['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y', 'PROPERTY_SESSION_ID' => $sessionId],
            false,
            ['nTopCount' => 1],
            [
                'ID',
                'IBLOCK_ID',
                'PROPERTY_HISTORY',
                'PROPERTY_CONTACT_NAME',
                'PROPERTY_PHONE',
                'PROPERTY_TOPIC',
            ]
        );

        if ($item = $result->GetNext()) {
            return [
                'ID' => (int)$item['ID'],
                'IBLOCK_ID' => (int)$item['IBLOCK_ID'],
                'HISTORY' => json_decode((string)$item['PROPERTY_HISTORY_VALUE'], true) ?: [],
                'CONTACTS' => [
                    'name' => (string)($item['PROPERTY_CONTACT_NAME_VALUE'] ?? ''),
                    'phone' => (string)($item['PROPERTY_PHONE_VALUE'] ?? ''),
                    'topic' => (string)($item['PROPERTY_TOPIC_VALUE'] ?? ''),
                ],
            ];
        }

        return null;
    }

    private function findIblockIdByCode(string $code): int
    {
        static $cache = [];

        if (isset($cache[$this->siteId . ':' . $code])) {
            return $cache[$this->siteId . ':' . $code];
        }

        $result = CIBlock::GetList([], ['CODE' => $code, 'SITE_ID' => $this->siteId]);
        $iblock = $result->Fetch();

        $cache[$this->siteId . ':' . $code] = (int)($iblock['ID'] ?? 0);

        return $cache[$this->siteId . ':' . $code];
    }

    private function mergeContacts(array $existingContacts, array $newContacts): array
    {
        $result = [];

        foreach (['name', 'phone', 'topic'] as $field) {
            $incomingValue = trim((string)($newContacts[$field] ?? ''));
            $result[$field] = $incomingValue !== ''
                ? $incomingValue
                : trim((string)($existingContacts[$field] ?? ''));
        }

        return $result;
    }
}
