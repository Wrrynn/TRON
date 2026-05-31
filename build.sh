#!/bin/bash

# Build script untuk Vercel - Laravel deployment
set -e

echo "📦 Installing PHP dependencies..."
# Download dan install Composer if not exists
if ! command -v composer &> /dev/null; then
    echo "Downloading Composer..."
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi

# Install PHP dependencies
composer install --no-dev --optimize-autoloader

echo "📦 Installing Node dependencies..."
npm install

echo "🔨 Building assets..."
npm run build

echo "✅ Build completed successfully!"
