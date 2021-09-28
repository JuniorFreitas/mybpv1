#!/usr/bin/env bash

docker exec -t app php artisan migrate --force
docker exec -t app php artisan db:seed --class=HabilidadesTableSeeder --force
#docker exec -t app php artisan db:seed --class=OcorrenciasJornadaTableSeeder --force
docker exec -t app composer dump
