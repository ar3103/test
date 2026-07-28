<?php

namespace Local\AiConsultant;

class GptClient
{
    public function ask(string $apiKey, string $model, array $messages): string
    {
        if ($apiKey === '') {
            return 'Сервис консультанта временно недоступен. Пожалуйста, оставьте контакты, и менеджер свяжется с вами.';
        }

        $payload = json_encode([
            'model' => $model,
            'messages' => $messages,
            'temperature' => 0.5,
        ], JSON_UNESCAPED_UNICODE);

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        if (!is_string($response) || $response === '') {
            return 'Не удалось получить ответ. Оставьте, пожалуйста, ваш телефон — мы перезвоним.';
        }

        $decoded = json_decode($response, true);

        return (string)($decoded['choices'][0]['message']['content'] ?? 'Я уточню информацию и вернусь с ответом.');
    }
}
