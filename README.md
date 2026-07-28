# Рекомендательный фильтр для `bitrix:news.list`

Реализован глобальный фильтр `$GLOBALS['arrFilterRecommendedArticles']`, который собирает рекомендации автоматически:

1. По части слов из заголовка текущей статьи.
2. По совпадению раздела (с учетом подразделов).
3. По части слов из текста (`PREVIEW_TEXT` / `DETAIL_TEXT`).
4. По AI-score через callback (в примере — fallback через `similar_text`).

Основная логика находится в `local/lib/BlogRecommendationFilter.php`, подключение и пример использования — в `local/php_interface/init.php`.
