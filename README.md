# MCP Panel – Hypermodern Debian Minecraft Hosting

MCP Panel is a Nette-powered control panel that provisions Spigot-based Minecraft servers on a single Debian host. The UI uses a
hypermodern dark theme accented with **MC** purple (`#ab47bc`) and delivers real-time console access without RCON by orchestrating
GNU Screen sessions directly on the host.

## Features

- Fully responsive landing and dashboard views rendered with Latte templates and the Nette UI toolkit.
- Secure registration and login backed by SQLite with hashed passwords and session-based auth.
- PHPMailer integration for transactional email (disabled by default, configurable via `common.neon`).
- Package catalog stored in SQLite that drives per-server JVM memory limits.
- Automated server provisioning that prepares directories, writes configuration, and starts Spigot inside named Screen sessions.
- Live console streaming and command injection using lightweight JSON endpoints.
- Admin workspace for inspecting users, packages, and servers from a single interface.

## Project layout

```
app/                    Nette bootstrap, presenters, templates, and models
scripts/                Utility scripts (database initialization)
storage/                SQLite database file and runtime server directories
www/                    Public web root for Apache/Nginx or PHP built-in server
```

## Requirements

- Debian 11+ with Apache or Nginx and PHP 8.1+
- PHP extensions: `pdo_sqlite`, `sqlite3`
- GNU Screen and OpenJDK 17+ on the host
- Spigot `spigot.jar` built with BuildTools (path configured in `app/config/common.neon`)
- Composer for dependency installation

## Quick start

1. **Clone and install dependencies**

   ```bash
   git clone https://github.com/Maz0CZ/mcp.git
   cd mcp
   composer install
   ```

2. **Prepare storage directories**

   ```bash
   mkdir -p storage/servers
   chown -R www-data:www-data storage
   ```

3. **Configure the panel**

   Adjust `app/config/common.neon` to point to your Spigot JAR, tweak Screen/process settings, and set SMTP credentials if
you plan to send email.

4. **Initialize the SQLite database**

   ```bash
   php scripts/init_db.php
   ```

   Default logins:
   - Customer: `test@test.cz` / `test`
   - Admin: `admin@test.cz` / `lofaska`

5. **Serve the panel**

   For local evaluation run the PHP built-in server:

   ```bash
   php -S 0.0.0.0:8000 -t www
   ```

   For production, point your web server document root to the `www/` directory.

## Deployment notes

- Each server instance runs inside a dedicated Screen session (`mcp_<id>`) ensuring commands can be injected directly for
full console control without enabling RCON.
- Console logs stream from each server directory and can be rotated with standard tooling (logrotate, cron jobs, etc.).
- Set `enableProcessControl` to `false` in `common.neon` when developing without Java/Screen—provisioning will still create
filesystem assets without executing shell commands.

## License

This project is provided as-is for educational use. Tailor the workflows, styling, and automation to match your hosting
requirements.
