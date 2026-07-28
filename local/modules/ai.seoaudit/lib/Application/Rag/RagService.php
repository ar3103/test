<?php

namespace Ai\SeoAudit\Application\Rag;

final class RagService
{
    public static function answer(int $projectId, string $question): array
    {
        $contexts = VectorStoreService::search($projectId, $question, 3);
        $summary = array_map(
            static fn ($item) => sprintf('[%s] %s', $item['url'], mb_substr($item['content'], 0, 200)),
            $contexts
        );

        return [
            'question' => $question,
            'contexts' => $contexts,
            'draft_answer' => "На основе базы знаний: \n" . implode("\n", $summary),
        ];
    }
}
