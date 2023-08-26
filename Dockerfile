#troque para latest se a estrutura for arm64 (mac m1 posterior ou dessa estrutura) se for amd64 (linux comum que usa essa estrutura) troque para main
#FROM juniorfreitas/laravel:latest
FROM juniorfreitas/laravel:main

RUN apt-get update && apt-get install -y \
    python3 \
    python3-pip --no-install-recommends && \
    pip3 install xlwt redis boto3

# Override nginx's default config
COPY .deploy/nginx/default.conf /etc/nginx/conf.d/default.conf

# Copy existing application directory
COPY . /usr/share/nginx/html/

COPY .deploy/scripts/start $APP_SCRIPTS/start

RUN chmod +x $APP_SCRIPTS/start && \
    composer install --ignore-platform-reqs --no-scripts && \
    chmod -R 775 bootstrap/cache

EXPOSE 80 6001 9001
