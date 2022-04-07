#!/bin/zsh

echo "\033[1;32mExecutando Script de Deploy by MasterTag / Janeiro de 2021 \033[0m"

git push -u origin develop
ssh bpsehomologacao <<-EOF
    cd /home/ubuntu/www/mybp
    git pull origin develop
    docker exec -t app php artisan migrate --force
    docker exec -t app php artisan db:seed --class=HabilidadesTableSeeder --force
EOF
