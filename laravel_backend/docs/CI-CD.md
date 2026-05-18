# Laravel Backend CI/CD

## Audit Summary

The backend is a Laravel 12 application located in `laravel_backend`. The CI/CD configuration must run from that directory and must not assume Android/mobile project files exist.

### Root Causes Found

- The previous GitHub Actions workflow only ran PHPUnit. It did not run formatting, security audit, migrations as an explicit gate, or conditional asset builds.
- The backend did not have its own `.env.example`, so a workflow using `cp .env.example .env` inside `laravel_backend` would fail.
- Node/Vite setup must be conditional because the backend currently has no `package.json`.
- Bitbucket CI referenced `php scripts/run-phpunit-ci.php`, but that script did not exist. It now uses `php tools/phpunit-compact.php`.
- Laravel Pint was installed but not enforceable because the codebase had style drift. The backend has been formatted so CI can run `php vendor/bin/pint --test`.
- Composer audit could not be validated locally because the sandbox has no DNS access to Packagist. The GitHub workflow runs it in CI where network access is available.

## CI Architecture

Workflow: `.github/workflows/backend-tests.yml`

- Triggers on `push` and `pull_request` for backend/workflow paths.
- Uses least-privilege `contents: read`.
- Cancels duplicate in-progress CI runs for the same branch/ref.
- Uses PHP 8.3 because the app requires PHP `^8.2` and CI should test on a modern stable runtime compatible with Laravel 12.
- Uses SQLite for fast isolated tests.
- Caches Composer packages using `composer.lock`.
- Installs Composer dependencies with `--no-scripts`, then runs Laravel package discovery after `.env` exists.
- Detects Node assets and only runs npm steps if `laravel_backend/package.json` exists.
- Runs migrations, Pint, Composer audit, PHPUnit, and optional asset build.
- Uploads Laravel logs if a job fails.

## Deployment Architecture

Workflow: `.github/workflows/deploy-vps.yml`

This is a manual VPS deployment workflow using SSH and release directories:

- Builds the backend artifact in GitHub Actions.
- Uploads a tarball to the server.
- Extracts to `releases/<commit-sha>`.
- Links shared `.env`, `storage`, and `bootstrap/cache`.
- Runs production Laravel commands.
- Atomically switches `current` symlink.
- Restarts queues.
- Keeps the five newest releases for quick rollback.

Server layout:

```text
/var/www/lnu-events
├── current -> releases/<sha>
├── releases
└── shared
    ├── .env
    ├── bootstrap-cache
    └── storage
```

Rollback example:

```bash
cd /var/www/lnu-events
ln -sfn releases/<previous-sha> current
cd current
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
```

## Compatibility Table

| Component | Version | Reason |
| --- | --- | --- |
| Laravel | 12.x | Locked by `composer.lock`; current application framework version. |
| PHP | 8.3 in CI/deploy build | Satisfies `^8.2`, supported by Laravel 12, and provides a modern stable runtime. |
| Composer | v2 | Required for modern Laravel dependency resolution and cache behavior. |
| Node | 22 if `package.json` exists | Current LTS-class runtime for optional Vite builds. Skipped when no backend asset pipeline exists. |
| SQLite | Runner-provided SQLite 3 | Fast isolated test database for CI. |
| MySQL | 8.0 in Docker | Matches `docker-compose.yml` and common production VPS deployments. |

## GitHub Secrets

Required only for deployment:

- `VPS_HOST`: server hostname or IP address.
- `VPS_PORT`: SSH port. Use `22` unless changed.
- `VPS_USER`: SSH user that owns or can write to the app path.
- `VPS_SSH_PRIVATE_KEY`: private key with access to the server.
- `VPS_APP_PATH`: deployment root, for example `/var/www/lnu-events`.
- `VPS_PHP_BIN`: optional PHP binary, for example `php8.3`; defaults to `php`.
- `VPS_RELOAD_COMMAND`: optional command after symlink switch, for example `sudo systemctl reload php8.3-fpm`.

Production `.env` is not stored in GitHub. Place it on the server at:

```text
$VPS_APP_PATH/shared/.env
```

Production `.env` must include:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_KEY=base64:...`
- `APP_URL=https://your-domain.example`
- database credentials
- mail settings
- queue/cache/session settings

Generate `APP_KEY` once:

```bash
php artisan key:generate --show
```

## Validation

Local validation commands:

```bash
cd laravel_backend
php vendor/bin/pint --test
composer test:compact
docker compose config --quiet
```

CI validation:

- Push a branch or open a pull request that touches `laravel_backend/**`.
- Confirm the `Laravel Backend CI` workflow passes.

Deployment validation:

- Confirm server has `shared/.env`.
- Run the manual `Laravel Backend VPS Deploy` workflow.
- Confirm `current` points to the new release.
- Confirm `php artisan migrate:status` works on the server.
- Confirm the app responds over HTTPS.
