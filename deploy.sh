#!/usr/bin/env bash

echo "\033[1;32mExecutando Script de Deploy by MasterTag / Janeiro de 2021 \033[0m"

ssh-add ~/.ssh/id_rsa

git push -u origin master
ssh  ubuntu@100.24.12.79 <<-EOF
    cd /home/ubuntu/www/mybp
    git add .
    git commit -m "fixed"
    git pull origin master
    sh deployDocker.sh
    npm install
    npm run prod
EOF
