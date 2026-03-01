<?php

declare(strict_types=1);

namespace Local\Lib;

use Bitrix\Main\Loader;

final class BlogRecommendationFilter
{
    private const DEFAULT_WINDOW = 14;

    /**
     * Builds a global filter array for bitrix:news.list based on article title fragments,
     * section, text fragments and optional AI score callback.
     *
     * @param int $articleId Source article id.
     * @param int $iblockId Blog iblock id.
     * @param callable|null $aiScoreResolver function(array $source, array $candidate): float
     */
    public static function build(int $articleId, int $iblockId, ?callable $aiScoreResolver = null): array
    {
        if ($articleId <= 0 || $iblockId <= 0) {
            return ['ID' => 0];
        }

        if (!Loader::includeModule('iblock')) {
            return ['ID' => 0];
        }

        $source = self::loadSourceArticle($articleId, $iblockId);
        if ($source === null) {
            return ['ID' => 0];
        }

        $wordFragments = self::extractWordFragments($source['NAME'] ?? '');
        $textFragments = self::extractTextFragments($source['PREVIEW_TEXT'] ?? '', $source['DETAIL_TEXT'] ?? '');
        $candidateIds = self::fetchCandidates($source, $wordFragments, $textFragments);

        if ($candidateIds === []) {
            return ['ID' => 0];
        }

        $rankedIds = self::rankCandidates($candidateIds, $source, $aiScoreResolver);

        return [
            'IBLOCK_ID' => $iblockId,
            'ACTIVE' => 'Y',
            'ID' => $rankedIds,
        ];
    }

    private static function loadSourceArticle(int $articleId, int $iblockId): ?array
    {
        $select = [
            'ID',
            'IBLOCK_ID',
            'IBLOCK_SECTION_ID',
            'NAME',
            'PREVIEW_TEXT',
            'DETAIL_TEXT',
            'TAGS',
            'DATE_ACTIVE_FROM',
        ];

        $res = \CIBlockElement::GetList([], [
            'ID' => $articleId,
            'IBLOCK_ID' => $iblockId,
            'ACTIVE' => 'Y',
        ], false, ['nTopCount' => 1], $select);

        $row = $res->GetNext();

        return $row ?: null;
    }

    private static function extractWordFragments(string $title): array
    {
        $parts = preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($title), -1, PREG_SPLIT_NO_EMPTY);
        if (!is_array($parts)) {
            return [];
        }

        $fragments = [];
        foreach ($parts as $part) {
            if (mb_strlen($part) < 5) {
                continue;
            }

            $fragments[] = mb_substr($part, 0, 4);
            $fragments[] = mb_substr($part, 0, 5);
        }

        return array_values(array_unique($fragments));
    }

    private static function extractTextFragments(string $preview, string $detail): array
    {
        $text = trim($preview . ' ' . strip_tags($detail));
        $tokens = preg_split('/\s+/u', mb_strtolower($text), -1, PREG_SPLIT_NO_EMPTY);
        if (!is_array($tokens)) {
            return [];
        }

        $tokens = array_values(array_filter($tokens, static fn(string $token): bool => mb_strlen($token) >= 7));
        if ($tokens === []) {
            return [];
        }

        $sample = array_slice($tokens, 0, 15);

        return array_values(array_unique(array_map(static fn(string $token): string => mb_substr($token, 0, 6), $sample)));
    }

    private static function fetchCandidates(array $source, array $wordFragments, array $textFragments): array
    {
        $orLogic = ['LOGIC' => 'OR'];

        foreach ($wordFragments as $fragment) {
            $orLogic[] = ['%NAME' => $fragment];
        }

        foreach ($textFragments as $fragment) {
            $orLogic[] = ['%PREVIEW_TEXT' => $fragment];
            $orLogic[] = ['%DETAIL_TEXT' => $fragment];
        }

        if (!empty($source['IBLOCK_SECTION_ID'])) {
            $orLogic[] = ['SECTION_ID' => (int)$source['IBLOCK_SECTION_ID'], 'INCLUDE_SUBSECTIONS' => 'Y'];
        }

        $filter = [
            'IBLOCK_ID' => (int)$source['IBLOCK_ID'],
            'ACTIVE' => 'Y',
            '!ID' => (int)$source['ID'],
            '>DATE_ACTIVE_FROM' => date('d.m.Y H:i:s', strtotime('-' . self::DEFAULT_WINDOW . ' days')),
            $orLogic,
        ];

        $res = \CIBlockElement::GetList(
            ['DATE_ACTIVE_FROM' => 'DESC'],
            $filter,
            false,
            ['nTopCount' => 100],
            ['ID']
        );

        $ids = [];
        while ($row = $res->Fetch()) {
            $ids[] = (int)$row['ID'];
        }

        return $ids;
    }

    private static function rankCandidates(array $candidateIds, array $source, ?callable $aiScoreResolver): array
    {
        if ($aiScoreResolver === null) {
            return array_slice($candidateIds, 0, 10);
        }

        $scored = [];
        foreach ($candidateIds as $id) {
            $candidate = self::loadCandidateCard($id);
            if ($candidate === null) {
                continue;
            }

            $score = (float)$aiScoreResolver($source, $candidate);
            $scored[] = [
                'id' => $id,
                'score' => $score,
            ];
        }

        usort($scored, static fn(array $left, array $right): int => $right['score'] <=> $left['score']);

        return array_slice(array_column($scored, 'id'), 0, 10);
    }

    private static function loadCandidateCard(int $articleId): ?array
    {
        $res = \CIBlockElement::GetList([], ['ID' => $articleId, 'ACTIVE' => 'Y'], false, ['nTopCount' => 1], [
            'ID',
            'NAME',
            'PREVIEW_TEXT',
            'DETAIL_TEXT',
            'TAGS',
            'IBLOCK_SECTION_ID',
        ]);

        $row = $res->GetNext();

        return $row ?: null;
    }
}
