<?php

namespace Ai\SeoAudit\Application\Rag;

use Ai\SeoAudit\Application\Embedding\EmbeddingProviderFactory;

final class EmbeddingService
{
    public static function embed(string $text): array
    {
        $provider = EmbeddingProviderFactory::make();
        $vector = $provider->embed($text);

        return is_array($vector) ? $vector : [];
    }
}
