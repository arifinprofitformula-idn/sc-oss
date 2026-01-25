#!/usr/bin/env bash
set -eu
set -o pipefail || true

APP_DIR="/home/bisnisem/apps/sc-oss"
WEB_DIR="/home/bisnisem/silvergram.store"

cd "$APP_DIR"

# ambil code terbaru
git pull origin main

# dependency production
composer install --no-dev --optimize-autoloader

# database & cache
php artisan migrate --force
php artisan optimize:clear

# publish public (ikut dotfiles)
rm -rf "$WEB_DIR"/*
cp -a "$APP_DIR/public/." "$WEB_DIR/"

# patch index.php agar tidak balik ke ../vendor dan ../bootstrap
sed -i "s|__DIR__.'/../vendor/autoload.php'|__DIR__.'/../apps/sc-oss/vendor/autoload.php'|g; s|__DIR__.'/../bootstrap/app.php'|__DIR__.'/../apps/sc-oss/bootstrap/app.php'|g" "$WEB_DIR/index.php"

# permissions (shared hosting friendly)
chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"

echo "DEPLOY OK"
