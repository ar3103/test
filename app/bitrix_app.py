from __future__ import annotations

from dataclasses import dataclass
from time import time


@dataclass
class BitrixPortalContext:
    domain: str
    access_token: str
    refresh_token: str
    member_id: str
    expires_at: int


class BitrixAppRegistry:
    """In-memory реестр установок приложения по portal/member_id."""

    def __init__(self) -> None:
        self._contexts: dict[str, BitrixPortalContext] = {}

    @staticmethod
    def make_key(domain: str, member_id: str) -> str:
        return f"{domain}:{member_id}"

    def save_install(
        self,
        *,
        domain: str,
        member_id: str,
        access_token: str,
        refresh_token: str,
        expires_in: int,
    ) -> BitrixPortalContext:
        context = BitrixPortalContext(
            domain=domain,
            access_token=access_token,
            refresh_token=refresh_token,
            member_id=member_id,
            expires_at=int(time()) + expires_in,
        )
        self._contexts[self.make_key(domain, member_id)] = context
        return context

    def remove_install(self, *, domain: str, member_id: str) -> bool:
        key = self.make_key(domain, member_id)
        return self._contexts.pop(key, None) is not None

    def get(self, *, domain: str, member_id: str) -> BitrixPortalContext | None:
        return self._contexts.get(self.make_key(domain, member_id))


def build_embedded_app_html() -> str:
    """Минимальная iframe-страница Bitrix24 app c кнопкой запуска синхронизации."""

    return """<!doctype html>
<html lang=\"ru\">
<head>
  <meta charset=\"utf-8\" />
  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\" />
  <title>Bitrix24 ↔ МойСклад</title>
  <script src=\"https://api.bitrix24.com/api/v1/\"></script>
  <style>
    body { font-family: Arial, sans-serif; margin: 24px; }
    .box { max-width: 760px; border: 1px solid #e6e6e6; border-radius: 12px; padding: 16px; }
    button { background: #2fc6f6; color: #fff; border: none; padding: 10px 16px; border-radius: 8px; cursor: pointer; }
    code { background: #f8f8f8; padding: 2px 6px; border-radius: 4px; }
  </style>
</head>
<body>
  <div class=\"box\">
    <h2>Интеграция Bitrix24 ↔ МойСклад</h2>
    <p>Это встроенное приложение Bitrix24. Нажмите кнопку, чтобы отправить команду на синхронизацию текущего заказа.</p>
    <button id=\"sync\">Синхронизировать заказ</button>
    <pre id=\"result\"></pre>
  </div>

  <script>
    BX24.init(function() {
      BX24.fitWindow();
    });

    async function syncOrder() {
      const placement = BX24.placement.info();
      const result = document.getElementById('result');
      try {
        const payload = {
          domain: BX24.getDomain(),
          placement: placement,
          auth: BX24.getAuth(),
        };
        const response = await fetch('/bitrix/app/sync', {
          method: 'POST',
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify(payload)
        });
        const data = await response.json();
        result.textContent = JSON.stringify(data, null, 2);
      } catch (e) {
        result.textContent = 'Ошибка: ' + e;
      }
    }

    document.getElementById('sync').addEventListener('click', syncOrder);
  </script>
</body>
</html>
"""
