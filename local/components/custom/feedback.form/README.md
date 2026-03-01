# custom:feedback.form

D7-компонент формы обратной связи с Ajax, защитой от спама, капчами и мультисайтовой отправкой.

## Возможности
- Ajax отправка через `runComponentAction`.
- Мультиязычность через `lang/`.
- Мультисайтность: `LID` берется из `Context::getCurrent()->getSite()`.
- Защита от спама: honeypot + Google reCAPTCHA + Yandex SmartCaptcha.
- Отправка данных в:
  - инфоблок,
  - Telegram,
  - почтовое событие (с вложениями),
  - Google Sheets через webhook.

## Параметры
Обязательные для задачи:
- `IBLOCK_ID`
- `TELEGRAM_BOT_TOKEN`
- `TELEGRAM_CHAT_ID`
- `YANDEX_SMARTCAPTCHA_SECRET_KEY`
- `GOOGLE_RECAPTCHA_SECRET_KEY`

Дополнительные:
- `YANDEX_SMARTCAPTCHA_SITE_KEY`
- `GOOGLE_RECAPTCHA_SITE_KEY`
- `MAIL_EVENT_NAME`
- `GOOGLE_SHEETS_WEBHOOK_URL`

## Свойства инфоблока
Рекомендуется создать свойства с символьными кодами:
`PHONE`, `EMAIL`, `CONTACT_METHOD`, `MESSAGE`, `PAGE_URL`, `PAGE_TITLE`, `REFERER`, `UTM_SOURCE`, `UTM_MEDIUM`, `UTM_CAMPAIGN`, `UTM_TERM`, `UTM_CONTENT`, `FILES`.

## Google Sheets
Компонент отправляет JSON на webhook `GOOGLE_SHEETS_WEBHOOK_URL`.
На стороне Apps Script создайте endpoint, который:
1. Проверяет наличие заголовков.
2. Если нет, добавляет:
   - Номер заявки
   - Дата заявки
   - ФИО
   - Телефон
   - Email
   - Способ связи
   - Сообщение
   - Страница оформления заявки
   - Referer
   - UTM source / medium / campaign / term / content
3. Добавляет строку с данными.
