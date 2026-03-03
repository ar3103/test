(function () {
    const root = document.querySelector('.promo-calculator');
    if (!root) {
        return;
    }

    const promoTypes = JSON.parse(root.dataset.promoTypes || '{}');
    const managementPercent = Number(root.dataset.managementPercent || 15);
    const i18n = JSON.parse(root.dataset.i18n || '{}');

    const form = root.querySelector('.promo-calculator__form');
    const statusNode = root.querySelector('[data-status]');
    const downloadNode = root.querySelector('[data-download]');

    const nodes = {
        total: root.querySelector('[data-total]'),
        base: root.querySelector('[data-base]'),
        personnelTax: root.querySelector('[data-personnel-tax]'),
        personnelTotal: root.querySelector('[data-personnel-total]'),
        management: root.querySelector('[data-management]'),
        agency: root.querySelector('[data-agency]'),
        subtotal: root.querySelector('[data-subtotal]'),
        taxes: root.querySelector('[data-taxes]')
    };

    const recalc = () => {
        const promoType = form.promo_type.value;
        const people = Number(form.people_count.value || 0);
        const days = Number(form.days_count.value || 0);
        const hours = Number(form.hours_count.value || 0);

        const rate = Number(promoTypes[promoType] || 0);
        const base = rate * people * days * hours;
        const personnelTax = base * 0.6;
        const personnelTotal = base + personnelTax;
        const management = personnelTotal * (managementPercent / 100);
        const agency = personnelTotal * 0.15;
        const subtotal = personnelTotal + management + agency;
        const taxes = subtotal * 0.08;
        const total = subtotal + taxes;

        nodes.base.textContent = Math.round(base);
        nodes.personnelTax.textContent = Math.round(personnelTax);
        nodes.personnelTotal.textContent = Math.round(personnelTotal);
        nodes.management.textContent = Math.round(management);
        nodes.agency.textContent = Math.round(agency);
        nodes.subtotal.textContent = Math.round(subtotal);
        nodes.taxes.textContent = Math.round(taxes);
        nodes.total.textContent = Math.round(total);
    };

    form.addEventListener('input', recalc);
    form.addEventListener('change', recalc);
    recalc();

    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        statusNode.textContent = i18n.status_sending || 'Sending...';

        const body = new FormData(form);

        try {
            const response = await fetch(window.location.href, {
                method: 'POST',
                body,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const payload = await response.json();
            if (!payload.success) {
                statusNode.textContent = payload.message || i18n.status_error_process || 'Request processing error';
                return;
            }

            statusNode.textContent = payload.message;
            if (payload.download_url) {
                downloadNode.href = payload.download_url;
                downloadNode.hidden = false;
                downloadNode.click();
            }
        } catch (error) {
            statusNode.textContent = i18n.status_error_network || 'Unable to send the form. Please try again later.';
        }
    });
})();
