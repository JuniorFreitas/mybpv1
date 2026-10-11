#!/usr/bin/env bash
# Gera Manual-de-Uso-MyBPIN-RH.pdf a partir de manual.html (usa o Google Chrome instalado).
set -euo pipefail
cd "$(dirname "$0")"

CHROME="${CHROME:-/Applications/Google Chrome.app/Contents/MacOS/Google Chrome}"
if [ ! -x "$CHROME" ]; then
  CHROME="$(command -v google-chrome || command -v chromium || command -v chromium-browser || true)"
fi
if [ -z "$CHROME" ]; then
  echo "Google Chrome/Chromium nao encontrado. Informe o caminho em CHROME=/caminho ./gerar-pdf.sh" >&2
  exit 1
fi

"$CHROME" --headless=new --disable-gpu --no-pdf-header-footer \
  --print-to-pdf="Manual-de-Uso-MyBPIN-RH.pdf" "file://$PWD/manual.html" 2>/dev/null

echo "PDF gerado: $PWD/Manual-de-Uso-MyBPIN-RH.pdf"
