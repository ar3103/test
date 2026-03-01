# Bitrix пакет рассылок (Email/SMS/Telegram) с многосайтностью

Реализован базовый рабочий пакет для:

- еженедельной рассылки новых статей блога по чекбоксу;
- автоответов на заявки из форм (включая повторные обращения с историей);
- триггерных кампаний (брошенная корзина, вебинар, re-engagement);
- праздничных рассылок с короткой ссылкой на форму отзыва;
- отправки в каналы Email + SMS + Telegram;
- логирования маркетинговых событий (open/click/bounce + бизнес-события).

## Структура

- `local/php_interface/init.php`
- `local/sites/s1/include/mailing.php`
- `local/lib/Mailing/Agent.php`
- `local/lib/Mailing/BlogSender.php`
- `local/lib/Mailing/Triggers.php`
- `local/lib/Mailing/FormHandler.php`
- `local/lib/Mailing/UserMailer.php`
- `local/lib/Mailing/ShortLinkManager.php`
- `local/lib/Mailing/ChannelDispatcher.php`
- `local/lib/Mailing/Analytics.php`

## Когда запускаются рассылки

Bitrix «понимает», что пора отправлять рассылки, через `CAgent`:

- `weeklyBlogEmail()` — раз в 604800 секунд (неделя);
- `triggeredCampaigns()` — раз в 86400 секунд (сутки);
- `holidayAndReviewLinks()` — раз в 86400 секунд.

Агенты регистрируются автоматически в `local/php_interface/init.php`.

## Как определяется база контактов

- Блог-рассылка: активные подписчики `CSubscription` по текущему сайту.
- Формы/триггеры: элементы соответствующих инфоблоков (`FORMS_IBLOCK_ID`, `CART_IBLOCK_ID`, `WEBINAR_IBLOCK_ID`).
- Re-engagement/праздничные: пользователи `CUser` с давней неактивностью (`REENGAGE_DAYS`).

## Многосайтность

Конфиг читается из файла:

- `/local/sites/<SITE_ID>/include/mailing.php`

Для каждого сайта можно задавать собственные:

- ID инфоблоков;
- event type'ы;
- каналы;
- параметры A/B;
- параметры коротких ссылок и аналитики.

## Короткие ссылки и аналитика

- `ShortLinkManager::create()` создаёт короткий URL (`/r/<code>`) и сохраняет связь в инфоблоке.
- `ShortLinkManager::registerClick()` увеличивает счётчик кликов и пишет событие в аналитику.
- `Analytics` пишет события в инфоблок аналитики для построения отчётов CRM/BI.

## Важно для production

1. Создать почтовые события и шаблоны:
   - `FORM_AUTO_RESPONSE`
   - `FORM_REPEAT_RESPONSE`
   - `UNIFIED_MARKETING_EMAIL`
2. Настроить обработчик маршрута `/r/<code>` (через `urlrewrite.php`/роутер) и вызвать `registerClick()`.
3. Проверить наличие свойств инфоблоков:
   - Для блога: `SEND_EMAIL`, `EMAIL_SENT`
   - Для лидов/форм: `EMAIL`, `PHONE`, `TELEGRAM_CHAT_ID`, `USER_ID`
   - Для коротких ссылок: `TARGET_URL`, `USER_ID`, `SITE_ID`, `CLICKS`
   - Для аналитики: `SITE_ID`, `EVENT_TYPE`, `PAYLOAD`
4. Для Telegram задать токен бота в опции `main:telegram_bot_token`.

