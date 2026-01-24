#!/bin/bash
# Script Deployment Otomatis untuk Shared Hosting (cPanel)

# Pastikan script berhenti jika ada error
set -e

echo "🚀 Memulai Deployment..."

# 1. Pull Code Terbaru
echo "📥 Mengambil kode terbaru dari Git..."
git pull origin main

# 2. Install/Update Composer Dependencies
echo "📦 Menginstall dependencies..."

# Coba deteksi binary PHP 8.3 (sesuaikan path ini dengan hosting Anda jika berbeda)
PHP_BIN="php"
if [ -f "/usr/local/bin/php83" ]; then
    PHP_BIN="/usr/local/bin/php83"
elif [ -f "/usr/local/bin/ea-php83" ]; then
    PHP_BIN="/usr/local/bin/ea-php83"
fi

echo "   Menggunakan PHP: $PHP_BIN"

# Jalankan composer (asumsi composer ada di path global atau download jika perlu)
if command -v composer &> /dev/null; then
    $PHP_BIN $(which composer) install --optimize-autoloader --no-dev
else
    echo "⚠️  Composer tidak ditemukan di global path, mencoba local composer.phar..."
    if [ ! -f "composer.phar" ]; then
        echo "   Mendownload composer.phar..."
        $PHP_BIN -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
        $PHP_BIN composer-setup.php
        $PHP_BIN -r "unlink('composer-setup.php');"
    fi
    $PHP_BIN composer.phar install --optimize-autoloader --no-dev
fi

# 3. Migrate Database
echo "🗄️  Migrasi Database..."
$PHP_BIN artisan migrate --force

# 4. Cache Config & Route
echo "🧹 Optimasi Cache..."
$PHP_BIN artisan optimize
$PHP_BIN artisan view:clear

echo "✅ Deployment Selesai!"
