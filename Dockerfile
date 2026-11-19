#troque para latest se a estrutura for arm64 (mac m1 posterior ou dessa estrutura) se for amd64 (linux comum que usa essa estrutura) troque para main
FROM juniorfreitas/php-nginx:8.5
# FROM juniorfreitas/laravel:main

WORKDIR /usr/share/nginx/html

COPY .deploy/nginx/default.conf /etc/nginx/conf.d/default.conf

COPY . /usr/share/nginx/html/

COPY .deploy/scripts/start-phpnginx /start-app.sh

# Normalizar line endings (evita "bad interpreter: Permission denied" por CRLF)
# Limpa caches de packages gerados no host (dev) antes do composer --no-dev
RUN sed -i 's/\r$//' /start-app.sh /usr/share/nginx/html/.deploy/scripts/start-phpnginx && \
    chmod +x /start-app.sh /usr/share/nginx/html/.deploy/scripts/start-phpnginx && \
    rm -f bootstrap/cache/packages.php bootstrap/cache/services.php && \
    composer install --ignore-platform-reqs --no-dev --prefer-dist --no-scripts && \
    APP_ENV=production php artisan package:discover --ansi && \
    APP_ENV=production php artisan horizon:publish && \
    chmod -R 775 bootstrap/cache storage

EXPOSE 80 9001

# Entrypoint da stack RHEL (php-fpm + nginx + horizon/schedule/worker)
CMD ["/start-app.sh"]
