#!/bin/sh
# Runs every time the server starts on Render
set -e

php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create / update the tables in Supabase (safe to run many times)
php artisan migrate --force

# Delete statuses older than 24 hours
php artisan model:prune --model="App\Models\Status" || true

# Demo people (only if SEED_DEMO=true in Render; each one runs only once)
# and fresh demo statuses when the old ones have expired
if [ "$SEED_DEMO" = "true" ]; then
    php artisan db:seed --class=DemoContentSeeder --force || true
    php artisan db:seed --class=MoreDemoContentSeeder --force || true
    php artisan db:seed --class=DemoStatusesSeeder --force || true
fi

exec apache2-foreground
