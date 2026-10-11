#!/usr/bin/env bash
# Monta manual.html (a partir de secoes/ e modelo/) e gera Manual-Completo-MyBPIN.pdf com o Google Chrome.
# O PDF e gerado duas vezes: a primeira descobre em que pagina cada titulo caiu; a segunda grava esses numeros no sumario.
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

imprimir() {
  "$CHROME" --headless=new --disable-gpu --no-pdf-header-footer \
    --print-to-pdf="Manual-Completo-MyBPIN.pdf" "file://$PWD/manual.html" 2>/dev/null
}

rm -f paginas.json
python3 montar.py
imprimir

if command -v swift >/dev/null 2>&1; then
  swift modelo/paginas-texto.swift Manual-Completo-MyBPIN.pdf paginas-texto.json
  python3 paginas.py
  python3 montar.py
  imprimir
else
  echo "swift nao encontrado: sumario gerado sem numeros de pagina." >&2
fi
rm -f paginas-texto.json titulos.json

# Base de conhecimento para o chatbot (RAG): regenerada a cada build, a partir das mesmas secoes.
python3 gerar-base-conhecimento.py

echo "PDF gerado: $PWD/Manual-Completo-MyBPIN.pdf"
