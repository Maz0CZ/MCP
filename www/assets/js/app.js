document.addEventListener('DOMContentLoaded', () => {
    const consoleWrap = document.querySelector('.console');
    if (consoleWrap) {
        const serverId = consoleWrap.dataset.server;
        const log = document.getElementById('console-log');
        const form = document.getElementById('console-command');

        const poll = () => {
            fetch(`/api/servers/${serverId}/console`)
                .then((response) => response.json())
                .then((data) => {
                    log.textContent = (data.lines || []).join('\n');
                    log.scrollTop = log.scrollHeight;
                })
                .catch(() => {
                    log.textContent = 'Unable to load console output.';
                })
                .finally(() => setTimeout(poll, 2000));
        };

        poll();

        form?.addEventListener('submit', (event) => {
            event.preventDefault();
            const input = form.querySelector('input[name="command"]');
            const command = input?.value.trim();
            if (!command) {
                return;
            }

            fetch(`/api/servers/${serverId}/command`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ command }),
            }).then(() => {
                if (input) {
                    input.value = '';
                }
            });
        });
    }

    document.querySelectorAll('button[data-action]').forEach((button) => {
        button.addEventListener('click', () => {
            const serverId = button.dataset.server;
            const action = button.dataset.action;
            fetch(`/api/servers/${serverId}/action`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ action }),
            });
        });
    });
});
