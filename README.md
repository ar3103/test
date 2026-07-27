# AI онлайн-консультант для 1C-Битрикс (многосайтовый)

Реализован компонент `company:ai.consultant`, который:

- общается с посетителем через ChatGPT API;
- использует готовые ответы из инфоблока FAQ;
- запрашивает обязательные контактные данные: **имя, телефон, что интересует**;
- сохраняет историю диалога и интерес клиента в инфоблоке диалогов;
- отправляет уведомления в Telegram и на email;
- после завершения диалога собирает оценку качества и отправляет автоответ.

## Структура

- `local/components/company/ai.consultant/class.php` — контроллер компонента и AJAX-экшены.
- `local/components/company/ai.consultant/lib/ConsultantService.php` — бизнес-логика, OpenAI, инфоблоки, уведомления.
- `local/components/company/ai.consultant/templates/.default/*` — UI-виджет и клиентский JS.

## Подготовка инфоблоков

### 1) Инфоблок диалогов (код `ai_dialogs`)

Создайте инфоблок для каждого сайта (или общий, но привязанный к нужным сайтам) с кодом `ai_dialogs`.

Рекомендуемые свойства:

- `SESSION_ID` (строка)
- `CONTACT_NAME` (строка)
- `PHONE` (строка)
- `TOPIC` (строка)
- `HISTORY` (текст)
- `STATUS` (список: OPEN/CLOSED)
- `RATING` (число)
- `RATING_COMMENT` (текст)
- `UPDATED_AT` (строка/дата)
- `CLOSED_AT` (строка/дата)

### 2) Инфоблок готовых ответов (код `ai_faq`)

Поля элементов:

- `NAME` — заголовок вопроса
- `DETAIL_TEXT` — текст готового ответа

Поиск выполняется по `%SEARCHABLE_CONTENT`.

## Настройки модуля (`COption`)

В `Settings -> Настройки модулей` (или через код) задайте:

- `company.ai_consultant/openai_api_key`
- `company.ai_consultant/openai_model` (например `gpt-4o-mini`)
- `company.ai_consultant/telegram_bot_token`

## Почтовое событие

Создайте почтовый тип/шаблон `AI_DIALOG_EVENT` и используйте поля:

- `#DIALOG_ID#`
- `#SESSION_ID#`
- `#NAME#`
- `#PHONE#`
- `#TOPIC#`
- `#QUESTION#`
- `#ANSWER#`

## Подключение компонента

```php
<?$APPLICATION->IncludeComponent(
    'company:ai.consultant',
    '',
    [
        'DIALOG_IBLOCK_CODE' => 'ai_dialogs',
        'FAQ_IBLOCK_CODE' => 'ai_faq',
        'TELEGRAM_CHAT_ID' => '-1001234567890',
    ]
);?>
```

## Многосайтовость

Компонент использует `SITE_ID` для:

- поиска инфоблоков по коду + сайту;
- отправки email через `CEvent::SendImmediate(..., SITE_ID, ...)`.

Это позволяет держать разные инфоблоки и каналы уведомлений для каждого сайта в одной установке Битрикс.
