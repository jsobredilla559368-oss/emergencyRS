#!/bin/sh

# Exit on error
set -e

echo "🚀 Starting deployment script..."

# Run migrations if database is ready
echo "📂 Running database migrations..."
php artisan migrate --force || echo "⚠️ Migration failed, database might not be ready yet."

# Cache optimization
echo "⚡ Optimizing Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start Apache
echo "🛰️ Starting Apache..."
exec apache2-foreground
