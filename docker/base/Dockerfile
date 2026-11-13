# syntax=docker/dockerfile:1
# MyBPIN base runtime — PHP 8.5.12 FPM + Nginx + Supervisor (Debian Bookworm)
# Pin de patch: rebump ARG PHP_IMAGE quando sair correção de segurança.
# Extensões: somente as obrigatórias do UPG-001 (sem imagick/mongodb/memcached/pgsql).

ARG PHP_IMAGE=php:8.5.12-fpm-bookworm
FROM ${PHP_IMAGE}

LABEL org.opencontainers.image.title="mybp-php-base" \
      org.opencontainers.image.description="Runtime base MyBPIN (PHP-FPM 8.5 + Nginx + Supervisor)" \
      org.opencontainers.image.vendor="MyBPIN"

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    PHP_OPCACHE_VALIDATE_TIMESTAMPS=0

RUN apt-get update && apt-get install -y --no-install-recommends \
        nginx \
        supervisor \
        curl \
        ca-certificates \
        git \
        unzip \
        libpng-dev \
        libjpeg62-turbo-dev \
        libwebp-dev \
        libfreetype6-dev \
        libzip-dev \
        libicu-dev \
        libxml2-dev \
        libonig-dev \
        $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    # mbstring e Zend OPcache já vêm na imagem oficial php:8.5-fpm
    && docker-php-ext-install \
        bcmath \
        exif \
        gd \
        intl \
        mysqli \
        pcntl \
        pdo_mysql \
        sockets \
        zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get purge -y --auto-remove \
        $PHPIZE_DEPS \
        git \
        unzip \
        libpng-dev \
        libjpeg62-turbo-dev \
        libwebp-dev \
        libfreetype6-dev \
        libzip-dev \
        libicu-dev \
        libxml2-dev \
        libonig-dev \
        zlib1g-dev \
        icu-devtools \
        linux-libc-dev \
        libc6-dev \
        libgcc-12-dev \
        libstdc++-12-dev \
    && apt-get install -y --no-install-recommends \
        libpng16-16 \
        libjpeg62-turbo \
        libfreetype6 \
        libwebp7 \
        libwebpmux3 \
        libwebpdemux2 \
        libzip4 \
        libicu72 \
        libonig5 \
    && php -m | grep -qi mbstring \
    && php -m | grep -qi gd \
    && php -m | grep -qi zip \
    && php -m | grep -qi redis \
    && php -m | grep -qi 'Zend OPcache\|opcache' \
    && rm -rf /var/lib/apt/lists/* /tmp/pear /tmp/*

# Composer (build/ops na base; app image pode copiar vendor via stage separado)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# PHP conf + mitigação CVE-2026-115560 / CVE-2026-109619
COPY docker/base/php/conf.d/99-mybp.ini /usr/local/etc/php/conf.d/99-mybp.ini
COPY docker/base/php/conf.d/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/base/php/fpm-pool.d/zz-cve-2026-115560.conf /usr/local/etc/php-fpm.d/zz-cve-2026-115560.conf

# Nginx/Supervisor mínimos da base (app image pode sobrescrever)
COPY docker/base/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/base/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY docker/base/supervisor/supervisord.conf /etc/supervisor/supervisord.conf
COPY docker/base/supervisor/conf.d/ /etc/supervisor/conf.d/

RUN mkdir -p /var/www/html /var/log/supervisor \
    && rm -f /etc/nginx/sites-enabled/default 2>/dev/null || true

WORKDIR /var/www/html

EXPOSE 80 9000

CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/supervisord.conf"]
