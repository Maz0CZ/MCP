# Zibuu MCP – Autonomous Minecraft Hosting Panel

Zibuu MCP is a lightweight hosting panel that provisions Spigot-based Minecraft servers on a single Debian host. The stack uses PHP, Apache/Nginx, and SQLite, and provides a modern dark UI, PHPMailer-powered notifications, and full console control through GNU Screen instead of RCON.

## Features

- Landing page with responsive dark UI (accent `#ab47bc`).
- User registration & login with secure password hashing.
- Built-in PHPMailer integration for welcome and transactional messages.
- Predefined hosting packages with RAM allocations stored in SQLite.
- Automated Spigot server provisioning with per-instance directories, ports, and EULA acceptance.
- Background process management via GNU Screen for true console access.
- Live log streaming and console command injection from the browser.
- Admin dashboard for viewing users, packages, and all provisioned servers.

## Project structure

```
public/              Public web root (place behind Apache/Nginx docroot)
  assets/            Static assets (CSS/JS)
  api/               AJAX endpoints for console streaming and server control
includes/            Shared PHP configuration and helpers
scripts/             Maintenance utilities (database initialization)
storage/             SQLite database file and server directories
```

## Requirements

- Debian 11+ host with Apache or Nginx + PHP 8.1+
- PHP extensions: `pdo_sqlite`, `sqlite3`
- Java Runtime Environment (OpenJDK 17+ recommended)
- GNU Screen
- Spigot `spigot.jar` built with BuildTools and placed at the path configured in `includes/config.php`
- Composer (for installing PHPMailer)

## Installation

1. **Clone the repository** into your web root and install PHP dependencies:

   ```bash
   composer install
   ```

2. **Configure the panel** by adjusting `includes/config.php`:
   - Set the absolute path to your Spigot `spigot.jar`.
   - Update SMTP credentials and enable email if needed.
   - Optionally disable process control during development by setting `enable_process_control` to `false`.

3. **Prepare storage directories**:

   ```bash
   mkdir -p storage/servers
   chown -R www-data:www-data storage
   ```

4. **Initialize the SQLite database** (creates tables, default users, and packages):

   ```bash
   php scripts/init_db.php
   ```

   Default logins:
   - Customer: `test@test.cz` / `test`
   - Admin: `admin@test.cz` / `lofaska`

5. **Configure your web server** so the document root points to the `public/` directory. Example Apache vhost snippet:

   ```apache
   DocumentRoot /var/www/zibuu/public
   <Directory /var/www/zibuu/public>
       AllowOverride All
       Require all granted
   </Directory>
   ```

6. **Provision servers** directly from the dashboard. Each server receives a dedicated folder under `storage/servers/<id>` with `server.properties`, `eula.txt`, and a rolling console log. The panel launches servers inside named Screen sessions (e.g., `mcserver_42`).

7. **Console streaming** is handled via AJAX polling of the console log. Commands entered in the browser are injected into the Screen session with `screen -X stuff`, avoiding reliance on RCON.

## Updating packages & world data

- To add or modify hosting packages, update the `packages` table via SQLite or extend the admin panel with CRUD actions.
- Server worlds and configuration files live within each server directory; standard backup tooling (e.g., `rsync`, `tar`) can be used.

## Development tips

- During local development without Spigot or Screen available, set `enable_process_control` to `false`. The panel will skip shell execution while still creating directories and configuration files.
- Log streaming falls back to friendly placeholder text until the actual log file appears.

## Security considerations

- Always serve the panel over HTTPS.
- Restrict PHP exec permissions and validate any additional configuration values carefully.
- Rotate console logs periodically for long-running servers (e.g., via cron) to avoid large files.

## License

This project is provided as-is for educational purposes. Customize and extend it to suit your hosting workflow.
