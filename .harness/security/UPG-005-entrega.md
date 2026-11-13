# Relatório de entrega — Upgrade Laravel 13 + PHP 8.5 + imagem própria

**Data:** 2026-11-13  
**Nível:** L3  
**Status local:** Validado (UPG-001…005). **Deploy prod:** pendente aprovação humana.

## Entregue
1. Imagem base própria `mybp/php-base:8.5.12-bookworm` (Bookworm, extensões obrigatórias, SEC-001 Off)
2. App sobre a base (`mybp/sistema:php85-local`) com Nginx/FPM/Horizon/Supervisor
3. Composer: PHP platform 8.5.12, Excel 4, **Laravel 13.42.0**
4. CSRF → `PreventRequestForgery`; cache `serializable_classes=false` + payloads seguros
5. Suite **680 passed**; Trivy **0** CRITICAL/HIGH fixáveis (app + base)

## Pendências explícitas
- Push ECR / cutover produção (**aprovação humana**)
- SEC-001 permanece **NEEDS APPROVAL** para redeploy prod (mitigação já na imagem nova)
- `laravel/ui` em `4.x-dev` até tag estável
- Migrar `session.serialization` para `json` em release anunciado
- Login E2E com usuário real (smoke validou formulário/CSRF apenas)

## Como operar local
```bash
docker compose up -d
docker compose exec mybpdp php artisan about
docker compose exec mybpdp php artisan test
# app: http://localhost:8000 → g/login
```
