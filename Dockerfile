# syntax=docker/dockerfile:1
# MyBPIN app image — FROM base PHP 8.5.12 (IMG-001)
ARG BASE_IMAGE=mybp/php-base:8.5.12-bookworm
FROM ${BASE_IMAGE}

ENV APP_PATH=/var/www/html \
    PHP_INI_DIR=/usr/local/etc/php \
    PHP_FPM_POOL_DIR=/usr/local/etc/php-fpm.d \
    SUPERVISOR_DIR=/etc/supervisor/conf.d \
    ENABLE_MIGRATE=true \
    ENABLE_SCHEDULE=false \
    ENABLE_WORKER=false \
    ENABLE_HORIZON=false \
    ENABLE_WEBSOCKETS=false \
    ENABLE_CACHE_APP=false

WORKDIR /var/www/html

# Nginx do app (root /var/www/html/public + FPM TCP 9000)
COPY .deploy/nginx/default.conf /etc/nginx/conf.d/default.conf

# Supervisor com supervisorctl + templates opcionais
COPY docker/base/supervisor/supervisord.conf /etc/supervisor/supervisord.conf
COPY docker/app/supervisor/conf.d/ /etc/supervisor/conf.d/

# Código da aplicação
COPY . /var/www/html/

# Entrypoint bakeado (não depende do volume/host)
COPY .deploy/scripts/start /usr/local/bin/docker-entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/docker-entrypoint.sh \
        /var/www/html/.deploy/scripts/start \
    && chmod +x /usr/local/bin/docker-entrypoint.sh \
        /var/www/html/.deploy/scripts/start \
    && mkdir -p storage/framework/{sessions,views,cache} storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && APP_ENV=production composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader \
    && APP_ENV=production php artisan horizon:publish --ansi \

    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwx storage bootstrap/cache

EXPOSE 80 6001 9001

CMD ["/usr/local/bin/docker-entrypoint.sh"]
