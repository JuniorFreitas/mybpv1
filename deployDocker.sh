#!/usr/bin/env bash

set -euo pipefail

CONTAINER="${MYBP_CONTAINER:-mybpdp}"

docker exec -t "${CONTAINER}" php artisan migrate --force
docker exec -t "${CONTAINER}" php artisan db:seed --class=HabilidadesTableSeeder --force
docker exec -t "${CONTAINER}" php artisan db:seed --class=WhatsappTemplatePadraoSeeder --force
docker exec -t "${CONTAINER}" composer dump-autoload
