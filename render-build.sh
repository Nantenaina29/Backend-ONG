#!/usr/bin/env bash
# Raha misy error dia mijanona avy hatrany ny script
set -o errexit


composer install --no-dev --optimize-autoloader

php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan migrate --force