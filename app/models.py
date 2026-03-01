from __future__ import annotations

from dataclasses import dataclass, field


@dataclass
class OrderPosition:
    sku: str
    name: str
    quantity: float
    price: float


@dataclass
class OrderPayload:
    external_id: str
    status: str
    customer_name: str
    comment: str | None = None
    need_invoice: bool = False
    positions: list[OrderPosition] = field(default_factory=list)


@dataclass
class SyncResult:
    updated_order_id: str
    invoice_id: str | None = None
    updated_positions: int = 0
    removed_positions: int = 0
