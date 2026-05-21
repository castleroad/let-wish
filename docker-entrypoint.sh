#!/bin/bash
set -e

if [ ! -f /var/www/html/wishes.db ]; then
    php /var/www/html/setup.php
fi

chown www-data:www-data /var/www/html/wishes.db 2>/dev/null || true

exec "$@"
