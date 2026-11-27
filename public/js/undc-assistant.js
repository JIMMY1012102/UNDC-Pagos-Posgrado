(() => {
    const root = document.querySelector('[data-undc-assistant]');
    if (!root) return;

    const launcher = root.querySelector('.undc-assistant-launcher');
    const panel = root.querySelector('#undcAssistantWindow');
    const closeButton = root.querySelector('[data-assistant-close]');
    const messages = root.querySelector('[data-assistant-messages]');
    const form = root.querySelector('[data-assistant-form]');
    const input = root.querySelector('#undcAssistantInput');
    const sendButton = root.querySelector('[data-assistant-send]');

    const setOpen = (open) => {
        panel.hidden = !open;
        launcher.setAttribute('aria-expanded', String(open));
        if (open) window.setTimeout(() => input.focus(), 30);
    };

    const addMessage = (text, role, extraClass = '') => {
        const item = document.createElement('div');
        item.className = 'undc-assistant-message is-' + role + (extraClass ? ' ' + extraClass : '');
        const paragraph = document.createElement('p');
        paragraph.textContent = text;
        item.appendChild(paragraph);
        messages.appendChild(item);
        messages.scrollTop = messages.scrollHeight;
        return item;
    };

    launcher.addEventListener('click', () => setOpen(panel.hidden));
    closeButton.addEventListener('click', () => {
        setOpen(false);
        launcher.focus();
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const message = input.value.trim();
        if (!message || sendButton.disabled) return;

        addMessage(message, 'user');
        input.value = '';
        input.style.height = '';
        sendButton.disabled = true;
        const pending = addMessage('Estoy revisando tu consulta…', 'bot', 'is-pending');

        try {
            const response = await fetch('/api/v1/asistente/mensajes', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ message }),
            });
            const payload = await response.json().catch(() => null);
            if (!response.ok || !payload || payload.success !== true || !payload.data?.answer) {
                throw new Error(payload?.message || 'No pude procesar tu consulta. Inténtalo nuevamente.');
            }
            pending.remove();
            addMessage(payload.data.answer, 'bot');
        } catch (error) {
            pending.remove();
            addMessage(error.message || 'No se pudo conectar con el asistente. Inténtalo nuevamente.', 'bot');
        } finally {
            sendButton.disabled = false;
            input.focus();
        }
    });

    input.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            form.requestSubmit();
        }
    });

    input.addEventListener('input', () => {
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 100) + 'px';
    });
})();
