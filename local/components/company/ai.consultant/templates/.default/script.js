(() => {
    const root = document.getElementById('ai-consultant-root');
    if (!root || !window.BX) {
        return;
    }

    const panel = root.querySelector('.ai-consultant__panel');
    const toggle = root.querySelector('.ai-consultant__toggle');
    const form = document.getElementById('ai-consultant-form');
    const input = document.getElementById('ai-consultant-input');
    const messages = document.getElementById('ai-consultant-messages');
    const rating = document.getElementById('ai-consultant-rating');

    const sessionKey = 'ai_consultant_session';
    const sessionId = localStorage.getItem(sessionKey) || Math.random().toString(36).slice(2);
    localStorage.setItem(sessionKey, sessionId);

    function appendMessage(text, role = 'bot') {
        const node = document.createElement('div');
        node.className = `ai-consultant__message ai-consultant__message--${role}`;
        node.textContent = text;
        messages.appendChild(node);
        messages.scrollTop = messages.scrollHeight;
    }

    toggle.addEventListener('click', () => {
        panel.hidden = !panel.hidden;
    });

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        const text = input.value.trim();
        if (!text) {
            return;
        }

        appendMessage(text, 'user');
        input.value = '';

        const contacts = {
            name: document.getElementById('ai-name').value.trim(),
            phone: document.getElementById('ai-phone').value.trim(),
            topic: document.getElementById('ai-topic').value.trim(),
        };

        BX.ajax.runComponentAction(root.dataset.componentName, 'sendMessage', {
            mode: 'class',
            signedParameters: root.dataset.signedParams,
            data: {
                sessionId,
                message: text,
                contacts,
            },
        }).then((response) => {
            appendMessage(response.data.answer, 'bot');
            if (!response.data.needContacts) {
                rating.hidden = false;
            }
        }).catch(() => {
            appendMessage('Сервис временно недоступен, оставьте номер телефона — менеджер свяжется с вами.', 'bot');
        });
    });

    rating.querySelectorAll('button').forEach((button) => {
        button.addEventListener('click', () => {
            BX.ajax.runComponentAction(root.dataset.componentName, 'rateDialog', {
                mode: 'class',
                signedParameters: root.dataset.signedParams,
                data: {
                    sessionId,
                    rating: Number(button.dataset.rating),
                },
            }).then((response) => {
                appendMessage(response.data.autoReply, 'bot');
                rating.hidden = true;
            });
        });
    });
})();
