# Discovery — Upgrade Laravel 13 + PHP 8.5 + imagem própria

## Status
Escopo validado pelo usuário (2026-11-13): seguir recomendação Harness. UPG-001 em execução.

## Nível Harness
**L3** — mudança crítica de runtime, dependências, autenticação CSRF, imagem de produção e superfície de vulnerabilidades.

## Problema
O MyBPIN roda hoje em **Laravel 12.69.3** + **PHP 8.2.8** sobre a imagem base `juniorfreitas/laravel` (Debian Buster em arquivo). Essa base está desatualizada, com CVEs de OS/PHP conhecidas, e não atende o objetivo de ter **imagem própria atualizada** com Laravel 13 e PHP 8.5, mantendo todos os recursos (Nginx, FPM, Horizon, workers, schedule, Reverb/websockets, Supervisor).

## Evidências (confirmado)
- `composer.json`: `laravel/framework ^12.0`, `php ^8.2`, `platform.php = 8.2.8`
- `composer.lock`: `laravel/framework v12.69.3`
- Container local `mybpdp`: PHP **8.2.8** (ago/2023) — abaixo do mínimo seguro 8.2.35 (PHP2Shell)
- `Dockerfile` atual: `FROM juniorfreitas/laravel:latest` + APT em `archive.debian.org` (Buster)
- Paths FPM hardcoded em `/etc/php/8.2/fpm/` (`.deploy/scripts/start`)
- SEC-001 (mitigação `session.upload_progress.enabled=Off`) em **NEEDS APPROVAL**; CVE-2026-115560 **não** é corrigida pelo PHP 8.5.12 — a paliativa deve seguir na nova imagem
- Referência útil no workspace: `mybp-2026/Dockerfile` já usa `php:8.5-fpm-bookworm` multi-stage

## Objetivo de produto
Disponibilizar o MyBPIN em runtime **Laravel 13 + PHP 8.5** com **imagem Docker própria** (base + app), com superfície de vulnerabilidades controlada e **paridade funcional** dos serviços atuais em Docker/ECS.

## Usuários / resultado esperado
- Time DevOps/Engenharia: build e deploy previsível, imagem auditável
- Time RH/negócio: nenhum fluxo crítico quebrado (admissão, filas, e-mail, exportação, websockets)
- Segurança: CVEs críticas do PHP (ex.: PHP2Shell) eliminadas via versão patchada; mitigação de upload progress mantida

## Requisitos funcionais
| ID | Requisito |
|---|---|
| RF-001 | A aplicação sobe em container com PHP 8.5.x (mínimo 8.5.12 enquanto for o patch de segurança vigente) + Laravel 13 |
| RF-002 | Imagem base própria versionada (não depender de `juniorfreitas/laravel` legado) |
| RF-003 | Imagem de aplicação herda a base e executa o mesmo conjunto de serviços: Nginx, PHP-FPM, Horizon, worker, schedule, websockets (quando habilitado), Supervisor GUI conforme flags atuais |
| RF-004 | Extensões PHP necessárias ao MyBP presentes (mínimo observado no container atual: bcmath, curl, gd, imagick, intl, mbstring, mysqli/pdo_mysql, pcntl, redis, zip, exif, soap/xml, opcache; reavaliar mongodb/memcached/pgsql se ainda forem usados) |
| RF-005 | `composer install` sem `--ignore-platform-reqs` no build de produção |
| RF-006 | Smoke dos fluxos críticos pós-upgrade (login, listagens, upload, job/Horizon, exportação, e-mail em fila, PDF) |

## Requisitos não funcionais
| ID | Requisito | Critério mensurável |
|---|---|---|
| RNF-001 | Segurança de imagem | Scan Trivy: **0 CRITICAL/HIGH com correção disponível** (`--ignore-unfixed`); residuais sem patch de distro documentados em `.harness/security/` com aceite humano. Meta aspiracional de 0 absoluto no scan full permanece como melhoria contínua (rebase OS). |
| RNF-002 | Patch PHP | Base pinada em tag patchada (ex. `php:8.5.12-fpm-bookworm`), não só `8.5` flutuante sem política de rebuild |
| RNF-003 | Mitigação CVE-2026-115560 | `session.upload_progress.enabled=Off` + `php_admin_flag` no pool FPM na imagem nova |
| RNF-004 | Rebuild contínuo | Processo documentado para rebump da base quando sair patch PHP/OS |
| RNF-005 | Paridade local/prod | Mesma família de Dockerfile para local e ECS (multi-stage), sem Buster |
| RNF-006 | Testes | `php artisan test` (SQLite `:memory:`) verde após upgrade; smoke manual/E2E dos fluxos RF-006 |

## Regras / restrições
- Multi-tenant `empresa_id` preservado
- Sem deploy em produção sem aprovação explícita
- MySQL de produção/dev: validar versão suportada (compose comenta MySQL 5.7 — **hipótese de risco**)
- Frontend permanece Vue 3 + Mix neste escopo (sem migração Vite obrigatória)

## Decisões do usuário (2026-11-13)
1. **"Sem vulnerabilidade"** = **0 CRITICAL/HIGH** no scan (aceito)
2. Registry: **ECR** `mybp/...` como primário (padrão atual de deploy); Docker Hub opcional depois
3. Base OS: **Debian Bookworm** (`php:8.5.12-fpm-bookworm`)
4. Estratégia: **faseada** A (imagem+PHP 8.5/L12) → B (Laravel 13)
5. Extensões legadas: classificar no UPG-001 (obrigatória / opcional / removível)
6. Cutover sessão/cache: fixar prefixos no `.env` na Fase B para evitar invalidação involuntária, salvo decisão contrária no UPG-003

## Riscos principais
| Risco | Impacto | Mitigação |
|---|---|---|
| Pacotes incompatíveis com PHP 8.5 / L13 (`intervention/image` v2, `predis` v1, `phpquery-laravel`, Excel, DomPDF) | Alto | Spike de compatibilidade (UPG-001) antes do merge |
| CSRF renomeado para `PreventRequestForgery` + Sec-Fetch-Site | Alto | Atualizar middleware/testes; validar APIs/forms |
| Cache `serializable_classes=false` quebra objetos em cache | Médio | Inventariar o que é cacheado como objeto |
| Imagem “zero vuln” irrealista se incluir pacotes extras (python/xlwt, imagick) | Médio | Minimizar superfície; isolar tools; aceitar médios justificados |
| Big-bang quebra produção | Alto | Sequência faseada + ambiente QA |
| SEC-001 ainda necessária em 8.5.12 | Médio | Portar mitigação para paths da imagem nova |

## Recomendação de engenharia
1. **Não** tratar “zero vulnerabilidade absoluta” como critério — usar RNF-001.
2. Executar em **duas fases**:
   - **Fase A**: imagem própria PHP 8.5.12 + Nginx/Supervisor + mitigação CVE, ainda em Laravel 12 (Laravel 12 já suporta PHP 8.5).
   - **Fase B**: upgrade Composer para Laravel 13 + ajustes de breaking changes + QA.
3. Reutilizar o padrão multi-stage de `mybp-2026` como ponto de partida, adaptando serviços do MyBP atual (Horizon, flags `ENABLE_*`).
4. Manter SEC-001 até existir patch oficial do CVE-2026-115560; só então remover a paliativa com evidência.

## Fora de escopo (oportunidades — não implementar agora)
- Migração Mix → Vite
- Troca MySQL 5.7 → 8.x (avaliar em tarefa separada se necessário)
- Octane/Swoole
- Reescrita da arquitetura frontend

## Sequência sugerida de backlog
1. UPG-001 — Spike inventário compatibilidade
2. IMG-001 — ADR + imagem base PHP 8.5 segura
3. IMG-002 — Dockerfile app MyBP na nova base + start/paths
4. UPG-002 — Platform PHP 8.5 no Composer (ainda L12)
5. UPG-003 — Upgrade Laravel 13 + deps oficiais
6. UPG-004 — Ajustes breaking changes app/testes
7. UPG-005 — QA/smoke + hardening scan
8. SEC-001 — reavaliar após nova imagem / redeploy
