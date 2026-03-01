(function () {
  const root = document.getElementById('ai-consultant');
  if (!root) return;

  const toggle = root.querySelector('.ai-consultant__toggle');
  const win = root.querySelector('.ai-consultant__window');
  const form = document.getElementById('ai-consultant-form');
  const leadForm = document.getElementById('ai-consultant-lead');
  const messagesEl = document.getElementById('ai-consultant-messages');
  const ratingEl = document.getElementById('ai-consultant-rating');
  const siteId = root.dataset.siteId;

  const history = [{ role: 'assistant', content: messagesEl.innerText.trim() }];
  let finalRating = 0;

  toggle.addEventListener('click', () => {
    win.hidden = !win.hidden;
  });

  function append(role, text) {
    const div = document.createElement('div');
    div.className = `ai-consultant__message ai-consultant__message--${role}`;
    div.textContent = text;
    messagesEl.appendChild(div);
    messagesEl.scrollTop = messagesEl.scrollHeight;
    history.push({ role, content: text });
  }

  async function send(action, body) {
    const formData = new URLSearchParams();
    formData.set('action', action);
    formData.set('siteId', siteId);
    Object.keys(body).forEach((key) => formData.set(key, body[key]));

    const response = await fetch('/local/ajax/ai_consultant.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: formData.toString()
    });
    return response.json();
  }

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    const input = form.elements.message;
    const text = input.value.trim();
    if (!text) return;

    append('user', text);
    input.value = '';

    const data = await send('chat', {
      message: text,
      history: JSON.stringify(history)
    });

    if (!data.ok) {
      append('assistant', 'Извините, временно недоступно. Попробуйте позже.');
      return;
    }

    append('assistant', data.answer);
    if (data.lead_required) {
      leadForm.hidden = false;
    }
  });

  leadForm.addEventListener('submit', async (event) => {
    event.preventDefault();

    const payload = {
      name: leadForm.elements.name.value.trim(),
      phone: leadForm.elements.phone.value.trim(),
      interest: leadForm.elements.interest.value.trim(),
      history,
      rating: finalRating
    };

    const data = await send('save', { payload: JSON.stringify(payload) });
    if (!data.ok) {
      append('assistant', 'Не удалось сохранить заявку. Проверьте данные и повторите.');
      return;
    }

    append('assistant', data.auto_reply || 'Спасибо! Мы с вами свяжемся.');
    ratingEl.hidden = false;
    leadForm.hidden = true;
  });

  ratingEl.querySelectorAll('button[data-rating]').forEach((button) => {
    button.addEventListener('click', () => {
      finalRating = Number(button.dataset.rating) || 0;
      append('assistant', 'Спасибо за оценку консультации!');
      ratingEl.hidden = true;
    });
  });
})();
