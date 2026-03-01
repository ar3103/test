from __future__ import annotations

from app.clients import BitrixClient, MoySkladClient
from app.models import OrderPayload, SyncResult


class SyncService:
    def __init__(self, source: BitrixClient, target: MoySkladClient):
        self.source = source
        self.target = target

    def sync_order(self, payload: OrderPayload) -> SyncResult:
        order_id = self.target.upsert_order(payload)

        current_positions = self.target.get_positions(order_id)
        incoming_positions = {p.sku: p for p in payload.positions}

        to_upsert = list(incoming_positions.values())
        to_delete = [sku for sku in current_positions if sku not in incoming_positions]

        updated_positions = self.target.upsert_positions(order_id, to_upsert) if to_upsert else 0
        removed_positions = self.target.delete_positions(order_id, to_delete) if to_delete else 0
        invoice_id = self.target.create_invoice(order_id) if payload.need_invoice else None

        return SyncResult(
            updated_order_id=order_id,
            invoice_id=invoice_id,
            updated_positions=updated_positions,
            removed_positions=removed_positions,
        )
