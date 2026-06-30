#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/.."

if ! command -v npx >/dev/null 2>&1; then
    echo "Node/npx não encontrado. Use Node >= 24."
    exit 1
fi

echo "→ Instalando Chrome para Playwright (se necessário)..."
npx playwright install chrome

echo ""
echo "→ Abrindo Chrome em http://localhost:8000/g/login"
echo "  Faça login e clique RESUME no Playwright Inspector quando estiver pronto."
echo ""

npm run test:e2e:whatsapp:login
