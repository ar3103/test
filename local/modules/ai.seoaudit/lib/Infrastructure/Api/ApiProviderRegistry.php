<?php

namespace Ai\SeoAudit\Infrastructure\Api;

use Ai\SeoAudit\Infrastructure\Api\Contracts\LlmProviderInterface;
use Ai\SeoAudit\Infrastructure\Api\Contracts\SearchProviderInterface;
use Ai\SeoAudit\Infrastructure\Api\Providers\GoogleSearchConsoleProvider;
use Ai\SeoAudit\Infrastructure\Api\Providers\LocalLlmProvider;
use Ai\SeoAudit\Infrastructure\Api\Providers\OpenAiProvider;
use Ai\SeoAudit\Infrastructure\Api\Providers\YandexWebmasterProvider;
use Bitrix\Main\Config\Option;

final class ApiProviderRegistry
{
    private const MODULE_ID = 'ai.seoaudit';

    /**
     * @return SearchProviderInterface[]
     */
    public static function searchProviders(): array
    {
        return [
            new YandexWebmasterProvider(),
            new GoogleSearchConsoleProvider(),
        ];
    }

    /**
     * @return LlmProviderInterface[]
     */
    public static function llmProviders(): array
    {
        return [
            new OpenAiProvider((string) Option::get(self::MODULE_ID, 'openai_key', '')),
            new LocalLlmProvider((string) Option::get(self::MODULE_ID, 'local_llm_endpoint', '')),
        ];
    }
}
