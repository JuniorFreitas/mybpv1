# MyBP — runtime lean (php-nginx) + Composer só no stage builder
# Mantém entrypoint .deploy/scripts/start-phpnginx (Horizon/schedule/migrate).
#
# Build local (imagens da base já tagueadas):
#   docker build -t mybp:local .
# Com args:
#   docker build \
#     --build-arg BASE_IMAGE=php-nginx:8.5-mybp \
#     --build-arg COMPOSER_IMAGE=php-nginx:8.5-composer \
#     -t mybp:local .

ARG COMPOSER_IMAGE=juniorfreitas/php-nginx:8.5-composer
ARG BASE_IMAGE=juniorfreitas/php-nginx:8.5

# -----------------------------------------------------------------------------
# vendor — composer install (não entra no runtime)
# -----------------------------------------------------------------------------
FROM ${COMPOSER_IMAGE} AS vendor

USER root
WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
        --ignore-platform-reqs \
        --no-dev \
        --prefer-dist \
        --no-scripts \
        --no-interaction \
        --no-progress \
    && chown -R 101:101 /app/vendor

# -----------------------------------------------------------------------------
# runtime — imagem da app (sem Composer)
# -----------------------------------------------------------------------------
FROM ${BASE_IMAGE}

USER root
WORKDIR /usr/share/nginx/html

# Vhost e hardening FPM/PHP no build (sem mutar /etc no start como root)
COPY .deploy/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY .deploy/php/fpm-pool.d/zz-cve-2026-115560.conf /etc/php-fpm.d/zz-cve-2026-115560.conf
COPY .deploy/php/fpm-pool.d/zz-mybp-fpm-harden.conf /etc/php-fpm.d/zz-mybp-fpm-harden.conf
COPY .deploy/php/php.ini-production /etc/php.ini

COPY . /usr/share/nginx/html/
COPY --from=vendor /app/vendor /usr/share/nginx/html/vendor
COPY .deploy/scripts/start-phpnginx /start-app.sh

RUN sed -i 's/\r$//' /start-app.sh /usr/share/nginx/html/.deploy/scripts/start-phpnginx \
    && chmod 755 /start-app.sh /usr/share/nginx/html/.deploy/scripts/start-phpnginx \
    && rm -f bootstrap/cache/packages.php bootstrap/cache/services.php \
    && mkdir -p \
        bootstrap/cache \
        storage/app/public \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        /var/lib/php/session \
        /var/lib/php/wsdlcache \
        /var/lib/php/opcache \
        /run/php-fpm \
    && APP_ENV=production php artisan package:discover --ansi \
    && APP_ENV=production php artisan horizon:publish \
    && chown -R 101:101 \
        /usr/share/nginx/html \
        /var/lib/php \
        /run/php-fpm \
        /var/cache/nginx \
    && chmod -R ug+rwX bootstrap/cache storage \
    && setcap 'cap_net_bind_service=+ep' /usr/sbin/nginx \
    && nginx -t \
    && rm -f /var/cache/nginx/nginx.pid \
    && chown -R 101:101 /var/cache/nginx

USER 101:101

# Alinhado à TD de produção (mybp-prod) + Traefik: containerPort 80
EXPOSE 80

CMD ["/start-app.sh"]
