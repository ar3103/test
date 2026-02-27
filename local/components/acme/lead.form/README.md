# acme:lead.form

D7-компонент формы заявки с поддержкой Ajax, мультиязычности и мультисайтовости (`SITE_ID` передается во все каналы).

## Возможности
- Ajax submit без перезагрузки страницы.
- Поля: имя, телефон, email, способ связи (checkbox), сообщение, множественная загрузка файлов.
- Скрытые поля: URL страницы, title страницы, referer, UTM-метки.
- Антиспам:
  - honeypot поле `company_name`;
  - Yandex SmartCaptcha (server-side verify);
  - Google reCAPTCHA (server-side verify).
- Интеграции:
  - Telegram Bot API;
  - инфоблок;
  - email + вложения через почтовое событие `ACME_LEAD_FORM`;
  - Google Sheets (через webhook URL, например Google Apps Script web app).

## Параметры компонента
- `IBLOCK_ID`
- `TELEGRAM_BOT_TOKEN`
- `TELEGRAM_CHAT_ID`
- `YANDEX_SMARTCAPTCHA_SECRET_KEY`
- `GOOGLE_RECAPTCHA_SECRET_KEY`
- `GOOGLE_SHEETS_WEBHOOK_URL`
- `EMAIL_TO`

## Важно по email
Компонент отправляет событие `ACME_LEAD_FORM`. Для реальной отправки нужно создать:
1. Тип почтового события `ACME_LEAD_FORM`.
2. Шаблон почтового события для нужных сайтов.

К вложениям в письме используются ID файлов, сохраненных через `CFile::SaveFile`.

## Пример подключения
```php
$APPLICATION->IncludeComponent(
    'acme:lead.form',
    '.default',
    [
        'IBLOCK_ID' => 12,
        'TELEGRAM_BOT_TOKEN' => '***',
        'TELEGRAM_CHAT_ID' => '***',
        'YANDEX_SMARTCAPTCHA_SECRET_KEY' => '***',
        'GOOGLE_RECAPTCHA_SECRET_KEY' => '***',
        'GOOGLE_SHEETS_WEBHOOK_URL' => 'https://script.google.com/macros/s/.../exec',
        'EMAIL_TO' => 'sales@example.com',
    ]
);
```
