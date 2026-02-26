#!/bin/bash

# Laravel Production Deployment Script
# Run this on your production server after pulling code

echo "Starting deployment..."

# Install/Update Composer dependencies
echo "Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader

# Install/Update NPM dependencies
echo "Installing NPM dependencies..."
npm ci

# Build Vite assets for production
echo "Building Vite assets..."
npm run build

# Clear and cache Laravel configurations
echo "Optimizing Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations
echo "Running migrations..."
php artisan migrate --force

# Set proper permissions
echo "Setting permissions..."
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

echo "Deployment complete!"
