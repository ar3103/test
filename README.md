# Импорт отзывов в инфоблок Bitrix (D7)

Реализация для выгрузки отзывов из Яндекс Карт, Google Maps, 2ГИС в инфоблок `отзывы`.

## Что делает решение

- Импортирует отзывы по агенту `\Company\ReviewSync\Agent\ReviewImportAgent::run();`
- Защищает от дублей по `XML_ID = platform:external_id`
- Новые отзывы по умолчанию сохраняются как `ACTIVE = N` (модерация)
- Отправляет уведомление о новом отзыве на модерации:
  - через почтовое событие `REVIEW_ON_MODERATION`
  - в Telegram-бот (опционально)
- После ручной публикации отзыва (`ACTIVE = Y`) автоматически заполняет поле `COMPANY_REPLY`
  случайным ответом из списка (до 10 вариантов)

## Требования к инфоблоку `отзывы`

Создайте/проверьте свойства с кодами:

- `AUTHOR_PHOTO` (Файл) — фото
- `AUTHOR_NAME` (Строка) — имя
- `AUTHOR_ROLE` (Строка) — должность
- `RATING` (Число) — рейтинг
- `ATTACHMENT` (Файл) — прикрепить файл
- `COMPANY_REPLY` (Текст/Строка) — автоответ компании
- `PLATFORM` (Строка) — источник
- `SOURCE_DATE` (Строка/Дата) — дата из источника

Текст отзыва пишется в `PREVIEW_TEXT`.

## Настройка опций модуля

В `b_option` для модуля `company.reviewsync`:

- `iblock_id` — ID инфоблока отзывов
- `moderation_active` = `Y`/`N` (если `Y`, новые отзывы не публикуются)
- `notify_email` — email для уведомлений
- `telegram_bot_token` / `telegram_chat_id` — Telegram уведомления
- `reply_templates` — до 10 строк, каждая строка отдельный вариант ответа

API-параметры:

- `yandex_api_key`, `yandex_org_id`
- `google_api_key`, `google_place_id`
- `twogis_api_key`, `twogis_branch_id`

## Подключение

1. Скопируйте каталог `local/modules/company.reviewsync` в проект.
2. Подключите `local/php_interface/init.php` или добавьте обработчик события в ваш существующий `init.php`.
3. Добавьте агент `\Company\ReviewSync\Agent\ReviewImportAgent::run();` (например, каждые 15 минут).
4. Создайте почтовый тип/шаблон с событием `REVIEW_ON_MODERATION`.

## Важные замечания по производительности

- Импорт инкрементальный: для каждой площадки хранится `last_sync_<provider>`.
- Короткие таймауты HTTP для защиты от зависаний (`5-10 сек`).
- Минимум запросов к БД: проверка дубля по `XML_ID` и точечная запись.
- Рекомендуется индекс на `b_iblock_element.XML_ID` (если его нет).
