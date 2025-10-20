# UltimatePanel – Ultra-light Warez-friendly Game Hosting

UltimatePanel is a Nette-powered control surface that provisions detached game servers on a Debian box without heavyweight daemons. It keeps things clone-and-go: SQLite for state, GNU Screen for process control, optional PHPMailer, and a hypermodern dark UI themed around **Ultimate**Panel’s signature `#ab47bc` accent.

Unlike the earlier panel build, UltimatePanel is designed to launch more than just Minecraft. Game profiles describe how to prepare directories, copy bundled binaries (if present), and build launch commands. Out of the box you get:

- **Minecraft Java (offline mode)** – ships with prewritten `server.properties` tuned for cracked clients.
- **Generic Linux dedicated** – drop any SteamCMD/Unity/Source binary or your own warez build and wire it up with `start.sh`.
- **Wine wrapper** – run Windows servers inside Wine/Proton via a generated launch script.

## Feature set

- Latte-based landing, dashboard, console, and admin interfaces with responsive layouts and accent-aware styling.
- Registration/login backed by SQLite with hashed credentials, session auth, and an admin role.
- Package catalogue that maps memory allocations to named game profiles.
- One-click provisioning that writes templated assets, copies bundled launchers when present, and starts each instance inside a named Screen session.
- Real-time console streaming plus command injection without RCON by writing directly into the Screen session.
- API endpoints for AJAX polling of logs and lifecycle actions (start/stop/restart).
- Admin overview for users, packages, and servers—including which game payload each instance runs.

## Repository layout

```
app/                    Bootstrap, DI config, models, presenters, Latte templates
scripts/                Utility scripts (SQLite initialiser)
storage/                SQLite database, server directories, optional bundled binaries
www/                    Public web root with assets and front controller
```

## Requirements

- Debian 11+ (or equivalent) with PHP 8.1+, Apache/Nginx or PHP’s built-in server
- PHP extensions: `pdo_sqlite`, `sqlite3`
- GNU Screen available on the host (set `enableProcessControl` to `false` for dry runs)
- Binaries for any games you intend to run (drop them under `storage/bins/...` as described below)
- Composer for dependency installation

## Quick start

1. **Clone & install**

   ```bash
   git clone https://github.com/Maz0CZ/UltimatePanel.git
   cd UltimatePanel
   composer install
   ```

2. **Bootstrap storage**

   ```bash
   mkdir -p storage/servers storage/bins/minecraft storage/bins/generic storage/bins/wine
   chown -R www-data:www-data storage
   ```

   - Place your preferred `minecraft_server.jar` inside `storage/bins/minecraft/server.jar` for instant offline servers.
   - Drop any generic Linux launcher into `storage/bins/generic/start.sh` (ensure it is executable).
   - Optionally put a Wine helper script under `storage/bins/wine/launch.sh`.

3. **Initialise the database**

   ```bash
   php scripts/init_db.php
   ```

   Default logins:

   - User: `test@test.cz` / `test`
   - Admin: `admin@test.cz` / `lofaska`

4. **Serve the panel**

   ```bash
   php -S 0.0.0.0:8000 -t www
   ```

   Or point Apache/Nginx to `www/` for production.

5. **Provision a server**

   Sign in, choose a package, and UltimatePanel will prepare a per-game directory under `storage/servers/<game>/server_xxx`, generate configs, and (if Screen control is enabled) spawn the process immediately.

## Configuration

`app/config/common.neon` contains all runtime toggles:

- `brandName` / `accentColor` – tweak UI branding.
- `enableProcessControl` – set to `false` to skip launching commands (useful on dev machines without Screen/Wine/Java).
- `screenBinary` – change if GNU Screen lives elsewhere.
- `games` – extend or edit per-game definitions with new commands, bundled executables, or templated files.
- `mail` – PHPMailer configuration, disabled by default.

## Warez-friendly workflow

UltimatePanel never enforces license checks. Game profiles can generate offline `server.properties`, generic launch scripts, or Wine shims, and any bundled executable is optional. Drop cracked builds or legitimate binaries in `storage/bins`, tweak the generated scripts, and restart from the dashboard.

## Testing

```bash
find app scripts www -name '*.php' -print0 | xargs -0 -n1 php -l
```

## License

Released as-is by Maz0CZ. Tinker freely, ship responsibly.
