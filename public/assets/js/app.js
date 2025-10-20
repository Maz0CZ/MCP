document.addEventListener('DOMContentLoaded', () => {
    const consoleContainer = document.querySelector('[data-console-log]');
    const serverId = consoleContainer?.dataset?.consoleLog;
    if (consoleContainer && serverId) {
        const poll = () => {
            fetch(`/api/console_log.php?id=${serverId}`)
                .then((res) => res.ok ? res.text() : Promise.reject(res.statusText))
                .then((text) => {
                    consoleContainer.textContent = text;
                    consoleContainer.scrollTop = consoleContainer.scrollHeight;
                })
                .catch(() => {});
        };
        poll();
        setInterval(poll, 5000);
    }

    const consoleForm = document.querySelector('[data-console-form]');
    if (consoleForm) {
        consoleForm.addEventListener('submit', (event) => {
            event.preventDefault();
            const input = consoleForm.querySelector('input[name="command"]');
            const formData = new FormData(consoleForm);
            fetch('/api/console_send.php', {
                method: 'POST',
                body: formData,
            }).then(() => {
                if (input) {
                    input.value = '';
                }
            });
        });
    }
});
