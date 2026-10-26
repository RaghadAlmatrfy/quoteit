#!/bin/sh
# Runs every time the server starts on Render
set -e

php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create / update the tables in Supabase (safe to run many times)
php artisan migrate --force

# Add the demo people (only if SEED_DEMO=true in Render, and only once)
if [ "$SEED_DEMO" = "true" ]; then
    php artisan db:seed --class=DemoContentSeeder --force || true
fi

# Delete statuses older than 24 hours
php artisan model:prune --model="App\Models\Status" || true

exec apache2-foreground
