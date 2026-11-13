# UPG-005 — Smoke RF-006 (2026-11-13)

Ambiente: container `mybpdp` — Laravel **13.42.0**, PHP **8.5.12**, imagem `mybp/sistema:php85-local` / base `mybp/php-base:8.5.12-bookworm`.

| Item RF-006 | Resultado | Evidência |
|---|---|---|
| Login (página) | OK | `GET /` → 302 `g/login`; `GET /g/login` → 200 + `csrf-token` meta |
| Sessão / CSRF cookie | OK | `Set-Cookie: XSRF-TOKEN` + `mybpin_session` no redirect |
| Listagens / HTTP app | OK | Home 302 autenticado; nginx 1.22.1 |
| Upload progress (SEC-001) | OK | `session.upload_progress.enabled => Off` |
| Job / Horizon | OK | `php artisan horizon:status` → running |
| Fila Redis | OK | Redis PING = 1; `queue.default=redis` |
| Exportação Excel | OK | `Maatwebsite\Excel\Excel` carregável; lock Excel 4.x |
| PDF DomPDF | OK | `Barryvdh\DomPDF\Facade\Pdf` carregável |
| E-mail (config/fila) | OK | `mail.default=smtp`; layout `layouts/mail/layout.blade.php` presente; envio permanece via fila |
| Suite automatizada | OK | `php artisan test` → **680 passed** |
| Scan Trivy (RNF-001) | OK | App + base: **0** HIGH/CRITICAL fixáveis (`--ignore-unfixed`) — ver `UPG-005-trivy-*.txt` |

## Observações
- Após `composer update` L13, FPM precisou de **restart do container** (autoload stale apontava `sodium_compat` fantasma). `ext-sodium` nativo presente.
- Login autenticado com credenciais reais **não** executado neste smoke (sem segredos); formulário e CSRF validados.
- Deploy produção / push ECR: **aprovação humana explícita** (autonomy.deployment).
- `laravel/ui` permanece `4.x-dev` até tag estável.
