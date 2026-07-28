# AI онлайн-консультант для 1С-Битрикс (с многосайтностью)

Реализован каркас онлайн-консультанта на базе ChatGPT для Bitrix:

- поддержка `SITE_ID` и разных настроек для каждого сайта;
- ответы из GPT с контекстом из инфоблока базы знаний;
- обязательный сбор контактов (имя, телефон, что интересует);
- сохранение истории диалога в инфоблок;
- отправка лида и истории в Telegram и на email;
- автоответ после завершения диалога и запрос оценки качества.

## Структура

- `local/config/ai_consultant.php` — настройки по сайтам;
- `local/lib/AiConsultant/*` — доменная логика;
- `local/tools/ai_consultant.php` — AJAX endpoint;
- `local/js/ai-consultant/widget.js` — клиентский SDK виджета;
- `local/php_interface/init.php` — автозагрузка классов.

## Что настроить в Bitrix

1. Создать инфоблок для базы знаний (`knowledge_iblock_id`).
2. Создать инфоблок для диалогов (`dialog_iblock_id`) и свойства:
   - `SITE_ID` (строка)
   - `SESSION_ID` (строка)
   - `CLIENT_NAME` (строка)
   - `PHONE` (строка)
   - `INTEREST` (строка)
   - `HISTORY` (текст)
   - `RATING` (число/список)
3. Добавить почтовое событие `AI_CONSULTANT_LEAD`.
4. Прописать env-переменные:
   - `OPENAI_API_KEY`
   - `TELEGRAM_BOT_TOKEN`
   - `TELEGRAM_CHAT_ID`

## Пример фронта

```html
<script src="/local/js/ai-consultant/widget.js"></script>
<script>
  AiConsultant.setContact('Иван', '+79990001122', 'Интересует внедрение CRM');

  AiConsultant.ask('Здравствуйте! Какие у вас тарифы?').then((data) => {
    console.log('Ответ:', data.answer);
  });

  // При закрытии диалога
  AiConsultant.finish('5').then((data) => {
    console.log(data.auto_reply); // автоответ
  });
</script>
```
