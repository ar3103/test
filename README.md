# AI онлайн-консультант для Bitrix (с поддержкой многосайтности)

Реализация включает:
- чат-виджет на фронтенде;
- запросы в OpenAI Chat Completions;
- обязательный сбор контактов: имя, телефон, интерес;
- сохранение истории диалога в инфоблок;
- уведомления в Telegram и на email;
- автоответ после завершения диалога;
- оценку качества консультации.

## Структура

- `local/php_interface/lib/AiConsultant.php` — основной сервис AI-консультанта.
- `local/ajax/ai_consultant.php` — AJAX endpoint для `chat/save`.
- `local/components/custom/ai.consultant` — компонент чата.

## Настройки для каждого сайта (SITE_ID)

В `Bitrix\Main\Config\Option` модуля `local.ai_consultant` задаются отдельные ключи:

- `openai_api_key_<SITE_ID>`
- `openai_model_<SITE_ID>`
- `knowledge_<SITE_ID>`
- `iblock_id_<SITE_ID>`
- `telegram_bot_token_<SITE_ID>`
- `telegram_chat_id_<SITE_ID>`

> Такой формат позволяет независимо конфигурировать консультанта для каждого сайта в мультисайте.

## Инфоблок

Создайте инфоблок и свойства:
- `PHONE` (строка)
- `INTEREST` (текст/строка)
- `SITE_ID` (строка)
- `RATING` (число)

ID инфоблока сохраните в `iblock_id_<SITE_ID>`.

## Почтовое событие

Создайте почтовый тип и шаблон `AI_CONSULTANT_DIALOG` с полями:
- `#NAME#`
- `#PHONE#`
- `#INTEREST#`
- `#DIALOG#`
- `#RATING#`

## Подключение компонента

```php
<?$APPLICATION->IncludeComponent(
    'custom:ai.consultant',
    '.default',
    [
        'TITLE' => 'Онлайн-консультант',
        'GREETING' => 'Здравствуйте! Подскажу по услугам и помогу с выбором.',
    ]
);?>
```

## Рекомендации

1. Заполните `knowledge_<SITE_ID>` фактами по конкретному сайту.
2. Старайтесь ограничивать ответ AI только проверенными данными.
3. Добавьте CSS для стилизации блока `.ai-consultant*` в шаблоне сайта.
