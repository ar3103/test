# AI SEO Audit для 1С-Битрикс

Готовый модуль под маркетплейс Битрикс для запуска SaaS/On-Premise SEO платформы с AI-агентами, пакетами лицензий и визуальным интерфейсом.

## Что реализовано в текущем коде

- Подключены SQL миграции и D7 ORM сущности (`Tenant`, `Project`, `Task`, `ApiCredential`).
- Добавлены агенты/очереди и cron entrypoint для фоновой обработки.
- Реализована админка: меню, дашборд, мастер настройки API.
- Подключен каркас API-провайдеров: Яндекс, Google Search Console, OpenAI, локальная LLM.
- Добавлен каталог пакетных тарифов (`Start`, `Pro`, `Enterprise`, `Autonomous`).

## Архитектура

### 1) Слои

- **Presentation/UI**: страницы админки Битрикс + wizard настройки.
- **Application**: scheduler, очередь задач, менеджер миграций, менеджер агентов.
- **Domain**: пакетные планы, фичи и лимиты.
- **Infrastructure**: API-коннекторы поисковиков и LLM-провайдеров.
- **Data/ORM**: таблицы и D7 сущности.

### 2) Основные bounded-context'ы

- `Core SEO` — семантика, кластеры, SERP, позиции.
- `Audit` — технический аудит, сниппеты, мета-теги.
- `Competitor Intel` — конкуренты, backlink, benchmark.
- `AI Lab` — embeddings, intent, NLP similarity, прогнозы.
- `Automation` — очередь, крон, агенты, action planner.
- `Tenant/Billing` — пакетные ограничения и изоляция клиентов.

## Реализация ключевых задач из запроса

### 1) Миграции таблиц + ORM Bitrix D7

Реализовано:
- SQL миграции в `install/db/mysql/install.sql` и `uninstall.sql`.
- `MigrationManager` для выполнения SQL при установке/удалении.
- D7 ORM классы:
  - `TenantTable`
  - `ProjectTable`
  - `TaskTable`
  - `ApiCredentialTable`

### 2) Крон/агенты для планировщика и очередей

Реализовано:
- `AgentManager` регистрирует агент `runQueueAgent()` каждые 300 секунд.
- `QueueService` обрабатывает отложенные задания из `b_ai_seo_task`.
- `Scheduler::tick()` запускает очередь на `OnAfterEpilog`.
- Cron-скрипт: `local/modules/ai.seoaudit/tools/cron_queue.php`.

Пример crontab:

```bash
*/5 * * * * /usr/bin/php -f /var/www/html/local/modules/ai.seoaudit/tools/cron_queue.php
```

### 3) UI в админке (дашборд + мастер)

Реализовано:
- Пункт меню модуля `AI SEO Audit`.
- Страница `Dashboard` (`ai_seoaudit_dashboard.php`).
- Страница `Setup Wizard` (`ai_seoaudit_wizard.php`) для сохранения API-ключей.

### 4) API провайдеры (Яндекс/Google/OpenAI/локальные LLM)

Реализовано:
- Контракты:
  - `SearchProviderInterface`
  - `LlmProviderInterface`
- Провайдеры:
  - `YandexWebmasterProvider`
  - `GoogleSearchConsoleProvider`
  - `OpenAiProvider`
  - `LocalLlmProvider`
- Реестр провайдеров `ApiProviderRegistry`.

## Пакеты для маркетплейса

| Пакет | Для кого | Ключевые возможности |
|---|---|---|
| **Start** | Малый бизнес | Семантика, позиции, базовый аудит, SERP-отчет, ручные рекомендации |
| **Pro** | Инхаус SEO/агентства | Кластеризация, competitor discovery, AI meta generation, action planner |
| **Enterprise** | Крупные проекты | Multi-region, ML прогнозы, RAG, benchmark score, API/webhooks |
| **Autonomous** | AI-first компании | Multi-agent automation, RL оптимизация, auto growth engine |

## Следующий этап

1. Реализовать полноценные интеграции REST-клиентов Яндекс/Google.
2. Добавить UI управления проектами/тенантами/заданиями.
3. Подключить генерацию PDF/HTML SEO-отчетов.
4. Внедрить векторное хранилище и RAG knowledge base.
5. Подготовить коммерческие редакции и лицензионные ограничения для маркетплейса.
