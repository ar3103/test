<?php

namespace Ai\SeoAudit\Application\Embedding;

use Bitrix\Main\Config\Option;

final class EmbeddingProviderFactory
{
    private const MODULE_ID = 'ai.seoaudit';

    public static function make(): EmbeddingProviderInterface
    {
        $provider = (string) Option::get(self::MODULE_ID, 'embedding_provider', 'openai');
        if ($provider === 'openai') {
            $apiKey = (string) Option::get(self::MODULE_ID, 'openai_key', '');
            if ($apiKey !== '') {
                return new OpenAiEmbeddingProvider($apiKey, (string) Option::get(self::MODULE_ID, 'openai_embedding_model', 'text-embedding-3-small'));
            }
        }

        return new HashEmbeddingFallbackProvider(64);
    }
}
