#!/usr/bin/env bash
# Publica temporariamente a mascara de dados em public/_manual_mascara.js para ser carregada no navegador
# durante a captura de telas. Rode com --limpar ao terminar: o arquivo NAO deve ficar em public/ nem ir para o git.
set -euo pipefail
cd "$(dirname "$0")"
DESTINO="../../../public/_manual_mascara.js"

if [ "${1:-}" = "--limpar" ]; then
  rm -f "$DESTINO"
  echo "Removido: $DESTINO"
  exit 0
fi

python3 - "$DESTINO" <<'PY'
import json, sys
m = open("mascara.js", encoding="utf-8").read()
pre = open("mascara-pre.js", encoding="utf-8").read()
js = "sessionStorage.__m=%s;sessionStorage.__pre=%s;(0,eval)(sessionStorage.__pre);(0,eval)(sessionStorage.__m);'mascara carregada'" % (json.dumps(m), json.dumps(pre))
open(sys.argv[1], "w", encoding="utf-8").write(js)
PY
echo "Publicado: $DESTINO"
echo "No navegador (pagina do sistema): (0,eval)(await (await fetch('/_manual_mascara.js?'+Date.now())).text())"
