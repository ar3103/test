<?php

namespace Ai\SeoAudit\Application\Rag;

final class EmbeddingService
{
    public static function embed(string $text, int $dims = 64): array
    {
        $vector = array_fill(0, $dims, 0.0);
        $tokens = preg_split('/\s+/u', mb_strtolower(trim($text))) ?: [];

        foreach ($tokens as $token) {
            if ($token === '') {
                continue;
            }
            $idx = abs(crc32($token)) % $dims;
            $vector[$idx] += 1.0;
        }

        $norm = sqrt(array_sum(array_map(static fn ($v) => $v * $v, $vector)));
        if ($norm > 0.0) {
            $vector = array_map(static fn ($v) => $v / $norm, $vector);
        }

        return $vector;
    }
}
