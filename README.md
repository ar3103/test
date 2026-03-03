# AI SEO Audit для 1С-Битрикс

Модуль для Битрикс Маркетплейса с multi-tenant SEO платформой: мониторинг, аудит, AI-рекомендации, очереди, отчеты, RAG.

## Реализовано по замечаниям

### 1) OAuth refresh flow для GSC и Яндекс

- Добавлен `OAuthTokenManager` для централизованного хранения OAuth параметров.
- `GoogleSearchConsoleProvider` и `YandexWebmasterProvider` автоматически обновляют `access_token` через `refresh_token`.
- В `Setup Wizard` добавлены поля `client_id/client_secret/refresh_token` для обеих платформ.

### 2) Retry + circuit breaker для REST-клиентов

- `RestClient` теперь выполняет retry (до 3 попыток) с backoff.
- Добавлен circuit breaker:
  - после серии ошибок endpoint временно блокируется;
  - состояние хранится в `Bitrix Option`.

### 3) Внешняя embedding-модель (с fallback)

- Добавлен слой embedding-провайдеров:
  - `OpenAiEmbeddingProvider` (внешняя модель `text-embedding-3-small` или другая);
  - `HashEmbeddingFallbackProvider` (fallback при отсутствии ключа).
- `EmbeddingService` теперь использует фабрику провайдеров `EmbeddingProviderFactory`.

### 4) PDF рендер через внешний движок

- Реализованы PDF-движки:
  - `WkhtmltopdfEngine`
  - `DompdfEngine`
  - `TcpdfEngine`
- `PdfEngineFactory` выбирает движок по настройке `pdf_engine`.
- При недоступности внешнего движка используется встроенный fallback PDF.

### 5) Переводы на несколько языков

Добавлены локализации `ru/en/de` для:
- `admin/menu.php`
- `admin/ai_seoaudit_dashboard.php`
- `admin/ai_seoaudit_wizard.php`

## Важно для production

1. Для OAuth необходимы валидные `client_id/client_secret/refresh_token`.
2. Для внешнего PDF-рендера установите соответствующий движок на сервере.
3. Для внешних embeddings укажите `openai_key` и `openai_embedding_model`.
4. Можно расширить i18n на остальные админ-страницы и сообщения в сервисах.
