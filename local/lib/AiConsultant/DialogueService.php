<?php

namespace Local\AiConsultant;

class DialogueService
{
    public function __construct(
        private readonly KnowledgeBase $knowledgeBase,
        private readonly GptClient $gptClient,
        private readonly LeadRepository $leadRepository,
        private readonly NotificationService $notificationService
    ) {
    }

    public function reply(string $siteId, string $sessionId, string $message, array $contact): array
    {
        $config = SiteResolver::resolveConfig($siteId);
        $history = $contact['history'] ?? [];

        $systemPrompt = $this->buildPrompt($config, $contact);
        $context = $this->knowledgeBase->getContext((int)$config['knowledge_iblock_id'], $message);

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'system', 'content' => "Контекст сайта:\n" . $context],
        ];

        foreach ($history as $item) {
            $messages[] = [
                'role' => $item['role'] ?? 'user',
                'content' => $item['content'] ?? '',
            ];
        }

        $messages[] = ['role' => 'user', 'content' => $message];
        $answer = $this->gptClient->ask((string)$config['openai_api_key'], (string)$config['openai_model'], $messages);

        $history[] = ['role' => 'user', 'content' => $message];
        $history[] = ['role' => 'assistant', 'content' => $answer];

        $this->leadRepository->saveConversation((int)$config['dialog_iblock_id'], $siteId, $sessionId, [
            'name' => $contact['name'] ?? '',
            'phone' => $contact['phone'] ?? '',
            'interest' => $contact['interest'] ?? '',
            'history' => $history,
        ]);

        return [
            'answer' => $answer,
            'history' => $history,
            'need_contact' => empty($contact['name']) || empty($contact['phone']) || empty($contact['interest']),
        ];
    }

    public function closeDialog(string $siteId, string $sessionId, array $payload): array
    {
        $config = SiteResolver::resolveConfig($siteId);
        $this->leadRepository->saveConversation((int)$config['dialog_iblock_id'], $siteId, $sessionId, $payload);

        $historyText = '';
        foreach (($payload['history'] ?? []) as $item) {
            $historyText .= ($item['role'] ?? '-') . ': ' . ($item['content'] ?? '') . PHP_EOL;
        }

        $lead = [
            'name' => $payload['name'] ?? '',
            'phone' => $payload['phone'] ?? '',
            'interest' => $payload['interest'] ?? '',
            'history_text' => $historyText,
        ];

        $tgText = "Новый диалог AI консультанта\n"
            . "Сайт: {$siteId}\n"
            . "Имя: {$lead['name']}\n"
            . "Телефон: {$lead['phone']}\n"
            . "Интерес: {$lead['interest']}\n\n"
            . "История:\n{$historyText}";

        $this->notificationService->sendTelegram((string)$config['telegram_bot_token'], (string)$config['telegram_chat_id'], $tgText);
        $this->notificationService->sendEmail($siteId, (string)$config['email_to'], $lead);

        return [
            'auto_reply' => (string)$config['auto_reply'],
            'ask_rating' => true,
        ];
    }

    private function buildPrompt(array $config, array $contact): string
    {
        return 'Ты опытный онлайн-консультант сайта и отвечаешь естественно, как человек. '
            . 'Твои ответы короткие, полезные и дружелюбные. '
            . 'Используй только данные из контекста сайта и готовых ответов. '
            . 'Если данных недостаточно — честно скажи, что уточнишь у менеджера. '
            . 'В диалоге обязательно запроси имя, телефон и что именно интересует клиента, если этих данных нет. '
            . 'Уже известные данные: имя=' . ($contact['name'] ?? 'нет')
            . ', телефон=' . ($contact['phone'] ?? 'нет')
            . ', интерес=' . ($contact['interest'] ?? 'нет') . '.';
    }
}
