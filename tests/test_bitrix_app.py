from app.bitrix_app import BitrixAppRegistry, build_embedded_app_html


def test_registry_install_get_remove() -> None:
    registry = BitrixAppRegistry()

    saved = registry.save_install(
        domain="example.bitrix24.ru",
        member_id="m1",
        access_token="at",
        refresh_token="rt",
        expires_in=3600,
    )

    assert saved.domain == "example.bitrix24.ru"
    context = registry.get(domain="example.bitrix24.ru", member_id="m1")
    assert context is not None
    assert context.access_token == "at"

    removed = registry.remove_install(domain="example.bitrix24.ru", member_id="m1")
    assert removed is True
    assert registry.get(domain="example.bitrix24.ru", member_id="m1") is None


def test_embedded_html_contains_bitrix_sdk_and_sync_button() -> None:
    html = build_embedded_app_html()

    assert "api.bitrix24.com/api/v1" in html
    assert "Синхронизировать заказ" in html
    assert "BX24.init" in html
