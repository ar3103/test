(function () {
    const forms = document.querySelectorAll('.acme-lead-form');

    forms.forEach((form) => {
        form.querySelector('input[name="PAGE_URL"]').value = window.location.href;
        form.querySelector('input[name="PAGE_TITLE"]').value = document.title;
        form.querySelector('input[name="REFERER"]').value = document.referrer || '';

        const params = new URLSearchParams(window.location.search);
        ['source', 'medium', 'campaign', 'term', 'content'].forEach((name) => {
            const field = form.querySelector(`input[name="UTM_${name.toUpperCase()}"]`);
            if (field) {
                field.value = params.get(`utm_${name}`) || '';
            }
        });

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const resultNode = form.querySelector('.acme-lead-form__result');
            const submitButton = form.querySelector('button[type="submit"]');
            submitButton.disabled = true;

            try {
                const formData = new FormData(form);
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const json = await response.json();
                resultNode.textContent = json.message || 'OK';
                if (json.success) {
                    form.reset();
                }
            } catch (error) {
                resultNode.textContent = 'Request failed';
            } finally {
                submitButton.disabled = false;
            }
        });
    });
})();
