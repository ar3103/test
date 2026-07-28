document.addEventListener('DOMContentLoaded', function () {
    const siteSelect = document.getElementById('siteFilter');
    if (!siteSelect) {
        return;
    }

    siteSelect.addEventListener('change', function () {
        const siteId = this.value;
        const url = new URL(window.location.href);
        url.searchParams.set('SITE_ID', siteId);
        window.location.href = url.toString();
    });
});
