#!/bin/bash
# Script Deployment Otomatis dengan Node.js Build
# Gunakan script ini jika server Anda mendukung Node.js

# Pastikan script berhenti jika ada error
set -e

echo "🚀 Memulai Deployment (dengan Node.js Build)..."

# 1. Pull Code Terbaru
echo "📥 Mengambil kode terbaru dari Git..."
git pull origin main

# 2. Install/Update Composer Dependencies
echo "📦 Menginstall PHP dependencies..."

# Coba deteksi binary PHP 8.3
PHP_BIN="php"
if [ -f "/usr/local/bin/php83" ]; then
    PHP_BIN="/usr/local/bin/php83"
elif [ -f "/usr/local/bin/ea-php83" ]; then
    PHP_BIN="/usr/local/bin/ea-php83"
fi

echo "   Menggunakan PHP: $PHP_BIN"

if command -v composer &> /dev/null; then
    $PHP_BIN $(which composer) install --optimize-autoloader --no-dev
else
    if [ ! -f "composer.phar" ]; then
        echo "   Mendownload composer.phar..."
        $PHP_BIN -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
        $PHP_BIN composer-setup.php
        $PHP_BIN -r "unlink('composer-setup.php');"
    fi
    $PHP_BIN composer.phar install --optimize-autoloader --no-dev
fi

# 3. Build Assets dengan Node.js
echo "🎨 Building Frontend Assets..."
if command -v npm &> /dev/null; then
    echo "   Menginstall Node dependencies..."
    npm install
    echo "   Compiling assets..."
    npm run build
else
    echo "❌ Error: Node.js / NPM tidak ditemukan di server ini."
    echo "   Silakan install Node.js terlebih dahulu atau gunakan metode build lokal."
    exit 1
fi

# 4. Migrate Database
echo "🗄️  Migrasi Database..."
$PHP_BIN artisan migrate --force

# 5. Cache Config & Route
echo "🧹 Optimasi Cache..."
$PHP_BIN artisan optimize
$PHP_BIN artisan view:clear

echo "✅ Deployment Selesai!"
