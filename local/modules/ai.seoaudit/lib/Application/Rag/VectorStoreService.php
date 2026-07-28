<?php

namespace Ai\SeoAudit\Application\Rag;

use Ai\SeoAudit\Model\VectorDocumentTable;
use Bitrix\Main\Type\DateTime;

final class VectorStoreService
{
    public static function upsertDocument(int $tenantId, int $projectId, string $url, string $content): void
    {
        $embedding = EmbeddingService::embed($content);

        VectorDocumentTable::add([
            'TENANT_ID' => $tenantId,
            'PROJECT_ID' => $projectId,
            'URL' => $url,
            'CONTENT' => $content,
            'EMBEDDING' => json_encode($embedding, JSON_UNESCAPED_UNICODE),
            'CREATED_AT' => new DateTime(),
        ]);
    }

    public static function search(int $projectId, string $query, int $limit = 5): array
    {
        $q = EmbeddingService::embed($query);
        $rows = VectorDocumentTable::getList([
            'filter' => ['=PROJECT_ID' => $projectId],
            'select' => ['ID', 'URL', 'CONTENT', 'EMBEDDING'],
            'limit' => 500,
        ])->fetchAll();

        $scored = [];
        foreach ($rows as $row) {
            $vec = json_decode((string) $row['EMBEDDING'], true) ?: [];
            $score = self::cosine($q, $vec);
            $scored[] = ['score' => $score, 'url' => $row['URL'], 'content' => $row['CONTENT']];
        }

        usort($scored, static fn ($a, $b) => $b['score'] <=> $a['score']);
        return array_slice($scored, 0, $limit);
    }

    private static function cosine(array $a, array $b): float
    {
        $n = min(count($a), count($b));
        if ($n === 0) {
            return 0.0;
        }

        $dot = 0.0;
        $na = 0.0;
        $nb = 0.0;
        for ($i = 0; $i < $n; $i++) {
            $dot += ((float) $a[$i]) * ((float) $b[$i]);
            $na += ((float) $a[$i]) ** 2;
            $nb += ((float) $b[$i]) ** 2;
        }

        if ($na <= 0.0 || $nb <= 0.0) {
            return 0.0;
        }

        return $dot / (sqrt($na) * sqrt($nb));
    }
}
