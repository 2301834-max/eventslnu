#!/usr/bin/env sh
set -eu

php artisan optimize:clear --ansi
php artisan migrate --force --ansi
php artisan storage:link --ansi || true
php artisan config:cache --ansi
php artisan route:cache --ansi
php artisan view:cache --ansi
php artisan queue:restart --ansi || true
