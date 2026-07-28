<?php

namespace Ai\SeoAudit\Application\Embedding;

interface EmbeddingProviderInterface
{
    public function embed(string $text): array;
}
