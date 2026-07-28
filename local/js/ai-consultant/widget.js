(function () {
  const state = {
    sessionId: crypto.randomUUID(),
    history: [],
    name: '',
    phone: '',
    interest: ''
  };

  async function send(action, message = '', rating = '') {
    const body = new URLSearchParams({
      action,
      session_id: state.sessionId,
      message,
      name: state.name,
      phone: state.phone,
      interest: state.interest,
      history: JSON.stringify(state.history),
      rating
    });

    const response = await fetch('/local/tools/ai_consultant.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body
    });

    return response.json();
  }

  window.AiConsultant = {
    setContact(name, phone, interest) {
      state.name = name;
      state.phone = phone;
      state.interest = interest;
    },
    async ask(message) {
      const data = await send('message', message);
      state.history = data.history || state.history;
      return data;
    },
    async finish(rating) {
      return send('finish', '', rating);
    }
  };
})();
