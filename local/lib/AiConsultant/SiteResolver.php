<?php

namespace Local\AiConsultant;

class SiteResolver
{
    public static function resolveConfig(string $siteId): array
    {
        $all = include $_SERVER['DOCUMENT_ROOT'] . '/local/config/ai_consultant.php';
        $default = $all['default'] ?? [];
        $siteSpecific = $all[$siteId] ?? [];

        return array_merge($default, $siteSpecific);
    }
}
