<?php

namespace Ai\SeoAudit\Infrastructure\Api\Providers;

use Ai\SeoAudit\Infrastructure\Api\Contracts\LlmProviderInterface;

final class OpenAiProvider implements LlmProviderInterface
{
    public function __construct(private readonly string $apiKey = '')
    {
    }

    public function getCode(): string
    {
        return 'openai';
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '';
    }

    public function generateMetaTags(string $content, array $keywords): array
    {
        return [
            'title' => 'AI title: ' . mb_substr(trim($content), 0, 55),
            'description' => 'AI description: ' . mb_substr(trim($content), 0, 145),
            'h1' => !empty($keywords) ? (string) $keywords[0] : 'Generated H1',
        ];
    }
}
