<?php

namespace Ai\SeoAudit\Infrastructure\Api;

use Ai\SeoAudit\Infrastructure\Api\Contracts\LlmProviderInterface;
use Ai\SeoAudit\Infrastructure\Api\Contracts\SearchProviderInterface;
use Ai\SeoAudit\Infrastructure\Api\Providers\GoogleSearchConsoleProvider;
use Ai\SeoAudit\Infrastructure\Api\Providers\LocalLlmProvider;
use Ai\SeoAudit\Infrastructure\Api\Providers\OpenAiProvider;
use Ai\SeoAudit\Infrastructure\Api\Providers\YandexWebmasterProvider;

final class ApiProviderRegistry
{
    /**
     * @return SearchProviderInterface[]
     */
    public static function searchProviders(): array
    {
        return [
            new YandexWebmasterProvider((string) getenv('AI_SEO_YANDEX_TOKEN')),
            new GoogleSearchConsoleProvider((string) getenv('AI_SEO_GSC_CREDENTIALS')),
        ];
    }

    /**
     * @return LlmProviderInterface[]
     */
    public static function llmProviders(): array
    {
        return [
            new OpenAiProvider((string) getenv('AI_SEO_OPENAI_KEY')),
            new LocalLlmProvider((string) getenv('AI_SEO_LOCAL_LLM_ENDPOINT')),
        ];
    }
}
