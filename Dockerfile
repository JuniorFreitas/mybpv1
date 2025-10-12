#troque para latest se a estrutura for arm64 (mac m1 posterior ou dessa estrutura) se for amd64 (linux comum que usa essa estrutura) troque para main
FROM juniorfreitas/laravel:latest
# FROM juniorfreitas/laravel:main
# Limpar e reconfigurar repositórios APT
RUN rm -rf /etc/apt/sources.list.d/* && \
    echo "deb http://archive.debian.org/debian buster main" > /etc/apt/sources.list && \
    echo "deb http://archive.debian.org/debian-security buster/updates main" >> /etc/apt/sources.list && \
    echo "deb http://archive.debian.org/debian buster-updates main" >> /etc/apt/sources.list && \
    apt-get -o Acquire::Check-Valid-Until=false update && \
    apt-get install -y --no-install-recommends \
        python3 python3-pip python3-setuptools python3-wheel && \
    pip3 install --no-cache-dir xlwt redis boto3 && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

COPY .deploy/nginx/default.conf /etc/nginx/conf.d/default.conf

COPY . /usr/share/nginx/html/

COPY .deploy/scripts/start $APP_SCRIPTS/start

# Definir permissões corretas para os scripts
RUN chmod +x $APP_SCRIPTS/start && \
    chmod +x /usr/share/nginx/html/.deploy/scripts/start && \
    # Garantir que o diretório de scripts tenha permissões corretas
    chmod -R 755 /usr/share/nginx/html/.deploy/scripts/ && \
    rm -f composer.lock && \
    composer install --ignore-platform-reqs && \
    php artisan horizon:publish && \
    chmod -R 775 bootstrap/cache

EXPOSE 80 9001

CMD ["/usr/share/nginx/html/.deploy/scripts/start"]