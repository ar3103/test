# AI SEO Audit для 1С-Битрикс

Модуль для Битрикс Маркетплейса с multi-tenant SEO платформой: мониторинг, аудит, AI-рекомендации, очереди, отчеты, RAG.

## Реализовано по замечаниям

### 1) Полноценные REST-интеграции Яндекс/Google

- Добавлен HTTP-клиент `RestClient` на `Bitrix\Main\Web\HttpClient`.
- `YandexWebmasterProvider` теперь выполняет реальный REST GET к API Webmaster.
- `GoogleSearchConsoleProvider` выполняет реальный REST POST к Search Analytics API.
- Провайдеры получают креды из `Bitrix\Main\Config\Option` через `ApiProviderRegistry`.

### 2) UI управления tenant/project/task

Добавлены страницы админки:
- `Entities`: создание и просмотр tenant/project/task.
- `Dashboard`: KPI по тенантам/проектам/очереди + статус API-провайдеров.
- `Setup Wizard`: настройка токенов провайдеров.

### 3) Генерация PDF/HTML SEO-отчетов

- `ReportService` генерирует:
  - HTML отчет;
  - PDF отчет (минимальный валидный PDF-генератор без внешних зависимостей).
- Отчеты сохраняются в `/upload/ai_seoaudit/reports` и фиксируются в таблице `b_ai_seo_report`.

### 4) Векторное хранилище + RAG knowledge base

- Добавлена таблица `b_ai_seo_vector_document`.
- Реализовано:
  - `EmbeddingService` (hash-based embeddings);
  - `VectorStoreService` (индексация + cosine search);
  - `RagService` (выдача релевантного контекста и draft answer).
- В админке добавлена страница `Reports & RAG` для индексации, RAG-вопросов и генерации отчетов.

## Структура БД

- `b_ai_seo_tenant`
- `b_ai_seo_project`
- `b_ai_seo_task`
- `b_ai_seo_api_credential`
- `b_ai_seo_report`
- `b_ai_seo_vector_document`

## Что осталось для production-hardening

1. OAuth refresh flow для GSC и Яндекс.
2. Retry/circuit breaker для REST-клиентов.
3. Переход с hash-embedding на внешнюю embedding-модель.
4. Рендер PDF через внешний движок (wkhtmltopdf / dompdf / tcpdf) при необходимости сложной верстки.
