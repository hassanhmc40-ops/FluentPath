#!/bin/sh
set -e

echo "[entry] Waiting for MySQL to accept connections..."
i=0
until nc -z mysql 3306 2>/dev/null; do
    i=$((i + 1))
    if [ "$i" -ge 60 ]; then
        echo "[entry] MySQL not reachable after 60s - aborting." >&2
        exit 1
    fi
    sleep 2
done
echo "[entry] MySQL is up."

cd /var/www/html

# Storage must be writable by php-fpm / queue / scheduler (all run as www-data)
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

# Always apply pending migrations first so code changes propagate on restart
php artisan migrate --force

# Seed only when the database is still empty (idempotent first boot)
SEEDED=$(php artisan tinker --execute="echo \Illuminate\Support\Facades\DB::table('users')->exists() ? 'yes' : 'no';" 2>/dev/null || echo no)
if [ "$SEEDED" != "yes" ]; then
    echo "[entry] Empty database - seeding demo content..."
    php artisan db:seed --force
fi

echo "[entry] Starting supervisord (php-fpm + schedule:work; queue runs as its own compose service)..."
exec supervisord -c /etc/supervisord.conf