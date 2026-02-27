# Импорт отзывов из Яндекс/Google/2ГИС в инфоблок «Отзывы» (Bitrix D7)

## Что реализовано
- Импорт отзывов из 3 источников (`yandex`, `google`, `twogis`) через D7 `HttpClient`.
- Очередь импорта в БД (`company_review_import_queue`) для стабильной работы под нагрузкой.
- Дедупликация по паре `provider + external_id`.
- Сохранение отзывов в инфоблок в статусе **неактивен** (`ACTIVE = N`) — для модерации.
- Уведомления о новых отзывах на модерации:
  - по email через почтовое событие `NEW_EXTERNAL_REVIEW_FOR_MODERATION`;
  - в Telegram через Bot API.
- После публикации отзыва (`ACTIVE = Y`) автоматически добавляется ответ компании в свойство `COMPANY_REPLY`.
- Ответ выбирается случайно из набора до 10 вариантов.

## Структура
- `local/php_interface/init.php` — автозагрузка, регистрация обработчиков и агента.
- `local/lib/ReviewSync/Agent/ReviewImportAgent.php` — агент импорта (каждые 5 минут).
- `local/lib/ReviewSync/Service/ReviewImportService.php` — orchestrator импорта.
- `local/lib/ReviewSync/Infrastructure/QueueRepository.php` — очередь/резервирование батча.
- `local/lib/ReviewSync/Infrastructure/IblockReviewRepository.php` — запись элемента инфоблока.
- `local/lib/ReviewSync/Infrastructure/NotificationService.php` — уведомления email/telegram.
- `local/lib/ReviewSync/EventHandlers/ReviewEvents.php` — автоответ после публикации.
- `local/lib/ReviewSync/Service/AutoReplyService.php` — логика автоответа.
- `local/lib/ReviewSync/Providers/*` — коннекторы площадок.

## Обязательные свойства инфоблока «Отзывы»
В инфоблоке (ID указывается в настройке `reviews_iblock_id`) создайте свойства:
- `PHOTO` (файл/картинка)
- `AUTHOR_NAME` (строка)
- `POSITION` (строка)
- `RATING` (число)
- `ATTACHMENT` (файл)
- `SOURCE` (строка)
- `SOURCE_EXTERNAL_ID` (строка)
- `COMPANY_REPLY` (строка/текст)

Текст отзыва сохраняется в `PREVIEW_TEXT`.

## Настройки (`b_option`, module_id = `company.reviewsync`)
- `reviews_iblock_id` — ID инфоблока отзывов.
- `import_batch_size` — размер батча (по умолчанию 50).
- `<provider>_endpoint` — endpoint API (`yandex_endpoint`, `google_endpoint`, `twogis_endpoint`).
- `<provider>_api_key` — API ключ.
- `<provider>_place_id` — идентификатор карточки/филиала.
- `notification_email` — email для уведомлений.
- `telegram_bot_token`, `telegram_chat_id` — для Telegram уведомлений.
- `auto_reply_variants` — до 10 строк, каждая строка = вариант ответа.

## Производительность
- Импорт работает агентом по расписанию, а не в хите пользователя.
- Работа в батчах и с промежуточной очередью снижает риск timeout.
- Встроенный lock (`import_lock`) не допускает параллельного запуска одного цикла.

## Важно
- Для email уведомлений нужно создать почтовое событие `NEW_EXTERNAL_REVIEW_FOR_MODERATION` и шаблон.
- Для каждого провайдера ожидается JSON вида `{ "reviews": [...] }`, маппинг полей описан в соответствующих классах провайдеров.
