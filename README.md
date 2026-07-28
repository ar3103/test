# Bitrix24 App: двусторонняя интеграция Bitrix24 ↔ МойСклад

Это версия интеграции в формате **встроенного приложения Bitrix24** (iframe app), а не только webhook-сервиса.

## Бизнес-возможности

- Изменение цен в заказах
- Добавление/изменение/удаление позиций в заказе
- Формирование счёта
- Изменение заказа (статус/клиент/комментарий)
- Двусторонняя синхронизация между Bitrix24 и МойСклад

## Что реализовано как приложение Bitrix24

- `POST /bitrix/install` — обработчик установки приложения на портал Bitrix24
- `POST /bitrix/uninstall` — обработчик удаления приложения
- `GET /bitrix/app` — iframe-страница приложения (кнопка ручной синхронизации)
- `POST /bitrix/app/sync` — backend-команда синхронизации из интерфейса приложения

## API для двустороннего обмена

- `POST /webhooks/bitrix/orders` — события из Bitrix24 в МойСклад
- `POST /webhooks/moysklad/orders` — события из МойСклад в Bitrix24
- `GET /health` — health-check

## Архитектура

- `SyncService` — бизнес-логика синхронизации
- `BitrixClient`/`MoySkladClient` — адаптеры внешних систем (в этой версии in-memory)
- `BitrixAppRegistry` — in-memory реестр установок приложения (portal domain + member_id + токены)

## Как подключить как Bitrix24 app

1. Поднимите сервис на публичном HTTPS-домене.
2. В Bitrix24 создайте локальное приложение (или приложение разработчика).
3. Укажите:
   - install handler: `https://<your-domain>/bitrix/install`
   - uninstall handler: `https://<your-domain>/bitrix/uninstall`
   - application URL (iframe): `https://<your-domain>/bitrix/app`
4. Добавьте нужные scope (CRM, sale и т.д.) под ваши REST-методы.
5. В `clients.py` замените in-memory логику на реальные вызовы Bitrix24 REST и API МойСклад.

## Быстрый старт

```bash
python -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
uvicorn app.main:app --reload
```

## Тесты

```bash
pytest -q
```

## Ограничения текущей демо-реализации

- Хранение установок и заказов in-memory (без БД).
- Нет проверки подписи и полной OAuth-ротации.
- `/bitrix/app/sync` использует демонстрационный заказ; в production нужно брать `placement/options` и грузить реальный заказ из Bitrix24.
