from app.clients import BitrixClient, MoySkladClient
from app.models import OrderPayload, OrderPosition
from app.service import SyncService


def test_sync_creates_invoice_and_positions() -> None:
    source = BitrixClient()
    target = MoySkladClient()
    service = SyncService(source=source, target=target)

    payload = OrderPayload(
        external_id="ORDER-1",
        status="NEW",
        customer_name="ООО Ромашка",
        need_invoice=True,
        positions=[OrderPosition(sku="A1", name="Товар A", quantity=1, price=100)],
    )

    result = service.sync_order(payload)

    assert result.updated_order_id == "ORDER-1"
    assert result.invoice_id == "invoice-ORDER-1"
    assert result.updated_positions == 1
    assert result.removed_positions == 0


def test_sync_removes_deleted_positions() -> None:
    source = BitrixClient()
    target = MoySkladClient()
    service = SyncService(source=source, target=target)

    initial = OrderPayload(
        external_id="ORDER-2",
        status="NEW",
        customer_name="ИП Петров",
        positions=[
            OrderPosition(sku="A1", name="Товар A", quantity=1, price=100),
            OrderPosition(sku="B1", name="Товар B", quantity=1, price=200),
        ],
    )
    service.sync_order(initial)

    changed = OrderPayload(
        external_id="ORDER-2",
        status="IN_PROGRESS",
        customer_name="ИП Петров",
        positions=[OrderPosition(sku="A1", name="Товар A", quantity=2, price=90)],
    )
    result = service.sync_order(changed)

    assert result.updated_positions == 1
    assert result.removed_positions == 1
    saved = target.get_positions("ORDER-2")
    assert "A1" in saved
    assert "B1" not in saved
    assert saved["A1"].price == 90
