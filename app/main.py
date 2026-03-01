from __future__ import annotations

from typing import Any

from fastapi import FastAPI
from fastapi.responses import HTMLResponse

from app.bitrix_app import BitrixAppRegistry, build_embedded_app_html
from app.clients import BitrixClient, MoySkladClient
from app.models import OrderPayload, OrderPosition, SyncResult
from app.service import SyncService

app = FastAPI(title="Bitrix24 ↔ МойСклад integration")

bitrix_client = BitrixClient()
moysklad_client = MoySkladClient()
registry = BitrixAppRegistry()

bitrix_to_moysklad = SyncService(source=bitrix_client, target=moysklad_client)
moysklad_to_bitrix = SyncService(source=moysklad_client, target=bitrix_client)


@app.post("/webhooks/bitrix/orders", response_model=SyncResult)
def handle_bitrix_order(payload: OrderPayload) -> SyncResult:
    return bitrix_to_moysklad.sync_order(payload)


@app.post("/webhooks/moysklad/orders", response_model=SyncResult)
def handle_moysklad_order(payload: OrderPayload) -> SyncResult:
    return moysklad_to_bitrix.sync_order(payload)


@app.post("/bitrix/install")
def bitrix_install(payload: dict[str, Any]) -> dict[str, str]:
    auth = payload.get("auth", {})
    member_id = auth.get("member_id", "")
    domain = auth.get("domain", payload.get("domain", ""))

    if not domain or not member_id:
        return {"result": "error", "message": "domain/member_id required"}

    registry.save_install(
        domain=domain,
        member_id=member_id,
        access_token=auth.get("access_token", ""),
        refresh_token=auth.get("refresh_token", ""),
        expires_in=int(auth.get("expires_in", 3600)),
    )
    return {"result": "ok"}


@app.post("/bitrix/uninstall")
def bitrix_uninstall(payload: dict[str, Any]) -> dict[str, str]:
    auth = payload.get("auth", {})
    member_id = auth.get("member_id", "")
    domain = auth.get("domain", payload.get("domain", ""))

    removed = registry.remove_install(domain=domain, member_id=member_id) if domain and member_id else False
    return {"result": "ok", "removed": str(removed).lower()}


@app.get("/bitrix/app", response_class=HTMLResponse)
def bitrix_app() -> str:
    return build_embedded_app_html()


@app.post("/bitrix/app/sync")
def sync_from_embedded_app(payload: dict[str, Any]) -> dict[str, Any]:
    auth = payload.get("auth", {})
    member_id = auth.get("member_id", "")
    domain = payload.get("domain", "")

    if not registry.get(domain=domain, member_id=member_id):
        return {"result": "error", "message": "application is not installed for this portal"}

    # Демонстрационный сценарий: реальный order_id берется из placement/options.
    demo_order = OrderPayload(
        external_id="BITRIX-DEMO-ORDER",
        status="IN_PROGRESS",
        customer_name="ООО Демоклиент",
        need_invoice=True,
        positions=[OrderPosition(sku="SKU-1", name="Тестовый товар", quantity=1, price=1000)],
    )
    sync_result = bitrix_to_moysklad.sync_order(demo_order)
    return {"result": "ok", "sync": sync_result.__dict__}


@app.get("/health")
def health() -> dict[str, str]:
    return {"status": "ok"}
