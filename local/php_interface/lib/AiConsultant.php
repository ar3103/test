<?php

namespace Local\AiConsultant;

use Bitrix\Main\Loader;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Config\Option;
use CEvent;
use CIBlockElement;

class Service
{
    private const MODULE_ID = 'local.ai_consultant';

    public static function chat(string $siteId, array $history, string $message): array
    {
        $apiKey = trim((string) Option::get(self::MODULE_ID, 'openai_api_key_' . $siteId, ''));
        $model = trim((string) Option::get(self::MODULE_ID, 'openai_model_' . $siteId, 'gpt-4o-mini'));
        $iblockId = (int) Option::get(self::MODULE_ID, 'iblock_id_' . $siteId, 0);

        if ($apiKey === '') {
            return ['ok' => false, 'error' => 'OpenAI API key не настроен для сайта ' . $siteId];
        }

        $preparedContext = self::getSiteKnowledge($siteId);
        $systemPrompt = self::buildSystemPrompt($preparedContext);

        $messages = [['role' => 'system', 'content' => $systemPrompt]];
        foreach ($history as $item) {
            if (!isset($item['role'], $item['content'])) {
                continue;
            }
            $messages[] = [
                'role' => in_array($item['role'], ['user', 'assistant'], true) ? $item['role'] : 'user',
                'content' => (string) $item['content'],
            ];
        }
        $messages[] = ['role' => 'user', 'content' => $message];

        $reply = self::requestOpenAi($apiKey, $model, $messages);
        if (!$reply['ok']) {
            return $reply;
        }

        return [
            'ok' => true,
            'answer' => $reply['answer'],
            'lead_required' => self::isLeadCollectionNeeded($history, $message),
            'iblock_id' => $iblockId,
        ];
    }

    public static function saveDialog(string $siteId, array $payload): array
    {
        if (!Loader::includeModule('iblock')) {
            return ['ok' => false, 'error' => 'Модуль iblock не подключен'];
        }

        $iblockId = (int) Option::get(self::MODULE_ID, 'iblock_id_' . $siteId, 0);
        if ($iblockId <= 0) {
            return ['ok' => false, 'error' => 'Не настроен инфоблок для сайта ' . $siteId];
        }

        $name = trim((string) ($payload['name'] ?? ''));
        $phone = trim((string) ($payload['phone'] ?? ''));
        $interest = trim((string) ($payload['interest'] ?? ''));
        $history = (array) ($payload['history'] ?? []);
        $rating = (int) ($payload['rating'] ?? 0);

        $dialogText = self::historyToText($history);

        $el = new CIBlockElement();
        $fields = [
            'IBLOCK_ID' => $iblockId,
            'NAME' => $name !== '' ? $name : 'Диалог ' . date('d.m.Y H:i'),
            'ACTIVE' => 'Y',
            'PREVIEW_TEXT' => $dialogText,
            'PROPERTY_VALUES' => [
                'PHONE' => $phone,
                'INTEREST' => $interest,
                'SITE_ID' => $siteId,
                'RATING' => $rating,
            ],
        ];

        $id = (int) $el->Add($fields);
        if ($id <= 0) {
            return ['ok' => false, 'error' => (string) $el->LAST_ERROR];
        }

        self::notifyMail($siteId, $name, $phone, $interest, $dialogText, $rating);
        self::notifyTelegram($siteId, $name, $phone, $interest, $dialogText, $rating);

        return [
            'ok' => true,
            'id' => $id,
            'auto_reply' => self::buildAutoReply($name),
        ];
    }

    private static function requestOpenAi(string $apiKey, string $model, array $messages): array
    {
        $http = new HttpClient();
        $http->setHeader('Authorization', 'Bearer ' . $apiKey);
        $http->setHeader('Content-Type', 'application/json');

        $response = $http->post(
            'https://api.openai.com/v1/chat/completions',
            json_encode([
                'model' => $model,
                'messages' => $messages,
                'temperature' => 0.4,
            ], JSON_UNESCAPED_UNICODE)
        );

        if (!$response) {
            return ['ok' => false, 'error' => 'Пустой ответ OpenAI'];
        }

        $data = json_decode($response, true);
        $answer = (string) ($data['choices'][0]['message']['content'] ?? '');
        if ($answer === '') {
            return ['ok' => false, 'error' => 'OpenAI не вернул текст ответа'];
        }

        return ['ok' => true, 'answer' => $answer];
    }

    private static function buildSystemPrompt(string $context): string
    {
        return "Ты опытный онлайн-консультант сайта. Отвечай как живой менеджер, кратко и понятно. "
            . "Не раскрывай, что ты AI. В диалоге обязательно запрашивай имя, телефон и что именно интересует клиента. "
            . "Если не хватает информации, задавай уточняющие вопросы. "
            . "Используй только данные из контекста и не придумывай факты.\n\n"
            . "Контекст сайта:\n"
            . $context;
    }

    private static function getSiteKnowledge(string $siteId): string
    {
        $prepared = trim((string) Option::get(self::MODULE_ID, 'knowledge_' . $siteId, ''));
        if ($prepared !== '') {
            return $prepared;
        }

        return 'База знаний не заполнена. Используй только общие уточняющие вопросы и предложи оставить контакт.';
    }

    private static function isLeadCollectionNeeded(array $history, string $currentMessage): bool
    {
        $allText = mb_strtolower(self::historyToText($history) . ' ' . $currentMessage);

        return !str_contains($allText, 'телефон') || !str_contains($allText, 'имя');
    }

    private static function historyToText(array $history): string
    {
        $lines = [];
        foreach ($history as $item) {
            $role = strtoupper((string) ($item['role'] ?? 'user'));
            $content = trim((string) ($item['content'] ?? ''));
            if ($content === '') {
                continue;
            }
            $lines[] = '[' . $role . '] ' . $content;
        }

        return implode(PHP_EOL, $lines);
    }

    private static function notifyMail(string $siteId, string $name, string $phone, string $interest, string $dialog, int $rating): void
    {
        CEvent::SendImmediate('AI_CONSULTANT_DIALOG', $siteId, [
            'NAME' => $name,
            'PHONE' => $phone,
            'INTEREST' => $interest,
            'DIALOG' => $dialog,
            'RATING' => $rating,
        ]);
    }

    private static function notifyTelegram(string $siteId, string $name, string $phone, string $interest, string $dialog, int $rating): void
    {
        $token = trim((string) Option::get(self::MODULE_ID, 'telegram_bot_token_' . $siteId, ''));
        $chatId = trim((string) Option::get(self::MODULE_ID, 'telegram_chat_id_' . $siteId, ''));
        if ($token === '' || $chatId === '') {
            return;
        }

        $message = "Новый диалог AI-консультанта\n"
            . "Сайт: {$siteId}\n"
            . "Имя: {$name}\n"
            . "Телефон: {$phone}\n"
            . "Интерес: {$interest}\n"
            . "Оценка: {$rating}\n\n"
            . "История:\n{$dialog}";

        $http = new HttpClient();
        $http->post(
            'https://api.telegram.org/bot' . $token . '/sendMessage',
            [
                'chat_id' => $chatId,
                'text' => $message,
            ]
        );
    }

    private static function buildAutoReply(string $name): string
    {
        $clientName = $name !== '' ? $name : 'клиент';

        return "{$clientName}, спасибо за обращение! Мы получили ваши данные. "
            . 'Специалист свяжется с вами в ближайшее время.';
    }
}
