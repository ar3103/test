# Калькулятор промо-акций для 1С-Битрикс

Репозиторий содержит готовый компонент `custom:promo.calculator`, который реализует:

- форму-калькулятор на базе обратной связи;
- расчет ориентировочной сметы в реальном времени;
- генерацию коммерческого предложения в Excel (`.xlsx`) из данных формы;
- отправку результатов на e-mail;
- отправку результатов в Telegram;
- сохранение заявки в инфоблок.

## Поля формы

- Тип промо акции (select)
- Требуемый персонал (выпадающий список, подтягивается из инфоблока "Стоимость")
- Количество человек
- Количество часов
- Количество дней
- Имя
- Телефон
- Почта
- Текст сообщения

## Структура

- `local/components/custom/promo.calculator/.description.php` — описание компонента.
- `local/components/custom/promo.calculator/.parameters.php` — параметры компонента.
- `local/components/custom/promo.calculator/class.php` — backend-логика (расчет, Excel, почта, Telegram, инфоблок).
- `local/components/custom/promo.calculator/templates/.default/template.php` — HTML формы и блока итоговой сметы.
- `local/components/custom/promo.calculator/templates/.default/script.js` — фронтенд-расчет и отправка формы.
- `local/components/custom/promo.calculator/templates/.default/style.css` — базовые стили.

## Подключение компонента

```php
$APPLICATION->IncludeComponent(
    'custom:promo.calculator',
    '.default',
    [
        'IBLOCK_ID' => 10,
        'EMAIL_TO' => 'sales@example.com',
        'TELEGRAM_BOT_TOKEN' => '123456:ABCDEF',
        'TELEGRAM_CHAT_ID' => '-1001234567890',
        'MANAGEMENT_PERCENT' => 15,
        'COSTS_IBLOCK_ID' => 12, // инфоблок "Стоимость" для списка требуемого персонала
        'PROMO_TYPES' => [
            'Промоутер без особых требований' => 900,
            'Промоутер с опытом' => 1200,
            'Супервайзер' => 1800,
        ],
    ]
);
```

## Требования

1. Установлен модуль `iblock`.
2. Для генерации Excel доступна библиотека `phpoffice/phpspreadsheet`.
3. Директория `/upload/promo_calculator` доступна на запись.

## Модель расчета

1. База за персонал: `ставка * количество человек * количество дней`.
2. Налоги за персонал: `база * 0.60`.
3. Итого за персонал: `база + налоги за персонал`.
4. Менеджмент: `итого за персонал * MANAGEMENT_PERCENT / 100`.
5. АК 15%: `итого за персонал * 0.15`.
6. Итого с менеджментом и АК: `итого за персонал + менеджмент + АК`.
7. Налоги: `итого с менеджментом и АК * 0.08`.
8. ИТОГО: `итого с менеджментом и АК + налоги`.

> Стоимость примерная и может меняться в зависимости от дополнительных факторов.


## Локализация

Компонент поддерживает мультиязычность через стандартный механизм Bitrix `lang/`:

- `local/components/custom/promo.calculator/lang/<lang>/` — переводы backend, параметров и описания;
- `local/components/custom/promo.calculator/templates/.default/lang/<lang>/template.php` — переводы шаблона и сообщений для JS.

При переключении языка сайта интерфейс формы, серверные сообщения валидации и подписи в Excel/Telegram берутся из соответствующих языковых файлов.
