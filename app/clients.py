from __future__ import annotations

from dataclasses import dataclass, field

from app.models import OrderPayload, OrderPosition


@dataclass
class InMemoryOrder:
    order_id: str
    header: OrderPayload
    positions: dict[str, OrderPosition] = field(default_factory=dict)


class BitrixClient:
    """Заглушка клиента Bitrix24 (заменяется на реальные HTTP вызовы)."""

    def __init__(self) -> None:
        self.orders: dict[str, InMemoryOrder] = {}

    def upsert_order(self, payload: OrderPayload) -> str:
        order = self.orders.get(payload.external_id)
        if order is None:
            order = InMemoryOrder(order_id=payload.external_id, header=payload)
            self.orders[payload.external_id] = order
        else:
            order.header = payload
        return order.order_id

    def get_positions(self, order_id: str) -> dict[str, OrderPosition]:
        order = self.orders.get(order_id)
        return {} if order is None else dict(order.positions)

    def upsert_positions(self, order_id: str, positions: list[OrderPosition]) -> int:
        order = self.orders[order_id]
        for position in positions:
            order.positions[position.sku] = position
        return len(positions)

    def delete_positions(self, order_id: str, skus: list[str]) -> int:
        order = self.orders[order_id]
        removed = 0
        for sku in skus:
            if sku in order.positions:
                removed += 1
                del order.positions[sku]
        return removed


class MoySkladClient(BitrixClient):
    """Поведение совпадает для демо-целей."""

    def create_invoice(self, order_id: str) -> str:
        return f"invoice-{order_id}"
