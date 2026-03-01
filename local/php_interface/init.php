<?php

declare(strict_types=1);

use Local\Lib\BlogRecommendationFilter;

spl_autoload_register(static function (string $class): void {
    $prefix = 'Local\\Lib\\';
    if (strpos($class, $prefix) !== 0) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $path = $_SERVER['DOCUMENT_ROOT'] . '/local/lib/' . str_replace('\\', '/', $relativeClass) . '.php';
    if (is_file($path)) {
        require_once $path;
    }
});

/**
 * Пример инициализации глобального фильтра для bitrix:news.list в блоке «Рекомендуемые статьи».
 *
 * Использование в шаблоне/странице:
 * $APPLICATION->IncludeComponent('bitrix:news.list', 'recommended', [
 *     'FILTER_NAME' => 'arrFilterRecommendedArticles',
 *     ...
 * ]);
 */
$articleId = (int)($_REQUEST['ELEMENT_ID'] ?? 0);
$iblockId = 7;

$aiResolver = static function (array $source, array $candidate): float {
    // Здесь можно подключить любой AI-клиент и вернуть релевантность [0..1].
    // Ниже fallback-скoring без внешних запросов.
    $sourceText = mb_strtolower(trim(($source['NAME'] ?? '') . ' ' . ($source['PREVIEW_TEXT'] ?? '')));
    $candidateText = mb_strtolower(trim(($candidate['NAME'] ?? '') . ' ' . ($candidate['PREVIEW_TEXT'] ?? '')));

    similar_text($sourceText, $candidateText, $percent);

    return $percent / 100;
};

$GLOBALS['arrFilterRecommendedArticles'] = BlogRecommendationFilter::build($articleId, $iblockId, $aiResolver);
