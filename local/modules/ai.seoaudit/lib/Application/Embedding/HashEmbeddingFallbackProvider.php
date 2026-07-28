<?php

namespace Ai\SeoAudit\Application\Embedding;

final class HashEmbeddingFallbackProvider implements EmbeddingProviderInterface
{
    public function __construct(private readonly int $dims = 64)
    {
    }

    public function embed(string $text): array
    {
        $vector = array_fill(0, $this->dims, 0.0);
        $tokens = preg_split('/\s+/u', mb_strtolower(trim($text))) ?: [];

        foreach ($tokens as $token) {
            if ($token === '') {
                continue;
            }
            $idx = abs(crc32($token)) % $this->dims;
            $vector[$idx] += 1.0;
        }

        $norm = sqrt(array_sum(array_map(static fn ($v) => $v * $v, $vector)));
        if ($norm > 0.0) {
            $vector = array_map(static fn ($v) => $v / $norm, $vector);
        }

        return $vector;
    }
}
