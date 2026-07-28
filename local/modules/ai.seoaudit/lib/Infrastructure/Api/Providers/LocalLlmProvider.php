<?php

namespace Ai\SeoAudit\Infrastructure\Api\Providers;

use Ai\SeoAudit\Infrastructure\Api\Contracts\LlmProviderInterface;

final class LocalLlmProvider implements LlmProviderInterface
{
    public function __construct(private readonly string $endpoint = '')
    {
    }

    public function getCode(): string
    {
        return 'local_llm';
    }

    public function isConfigured(): bool
    {
        return $this->endpoint !== '';
    }

    public function generateMetaTags(string $content, array $keywords): array
    {
        return [
            'title' => 'Local AI: ' . mb_substr(trim($content), 0, 55),
            'description' => 'Local model summary for SEO snippet.',
            'h1' => !empty($keywords) ? (string) $keywords[0] : 'Local generated heading',
        ];
    }
}
