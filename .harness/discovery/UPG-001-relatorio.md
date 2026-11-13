# UPG-001 — Relatório de compatibilidade PHP 8.5 + Laravel 13

**Data:** 2026-11-13  
**Ambiente de análise:** Composer 2 (container `composer:2`), platform PHP `8.5.12`; código MyBP em `/Users/juniorfreitas/Desktop/WEB/2026/mybp`  
**Veredito:** **GO com condições** — Fase A viável; Fase B viável após bumps de pacotes.

## 1) Bloqueadores Composer

### Fase A (Laravel 12 + PHP 8.5) — GO com bump obrigatório
| Pacote atual | Problema | Ação |
|---|---|---|
| `maatwebsite/excel` ^3.1 → `phpoffice/phpspreadsheet` 1.30.x | `php >=7.4 <8.5` | Subir para **`maatwebsite/excel ^4.0`** (resolve `phpspreadsheet` 5.x, PHP 8.3+) |

Dry-run Phase A (`UPG-001-composer-phaseA-php85.log`): resolveu com `excel 4.0.6` + `phpspreadsheet 5.11.0`, Laravel 12, **sem advisories** no dry-run.

### Fase B (Laravel 13 + PHP 8.5) — GO com condições
Dry-run Phase B (`UPG-001-composer-phaseB-l13.log`) resolveu `laravel/framework **v13.42.0**` quando:

| Pacote | Situação | Ação |
|---|---|---|
| `maatwebsite/excel` | idem Fase A | `^4.0` |
| `laravel/tinker` | major | `^3.0` |
| `phpunit/phpunit` | major | `^12.0` |
| `laravel/ui` | 4.6.2 sem Illuminate 13 | usar **`4.x-dev`** até haver tag estável L13 (risco: sem semver estável) |
| `barryvdh/laravel-dompdf` | 3.1.2 já aceita `^13.0` | bump lock para ≥ 3.1.2 |
| `spatie/laravel-ignition` ^3 | só até Illuminate 12 | **remover** na Fase B (dev); usar stack de erro L13 / Flare quando disponível |
| `barryvdh/laravel-debugbar` | só até Illuminate 12 | **remover ou adiar** até haver suporte L13 |
| `barryvdh/laravel-ide-helper` | só até Illuminate 12 | **remover ou adiar** até haver suporte L13 |
| `predis/predis` v1.1.10 | resolve; Horizon sugere ^1\|^2\|^3 | opcional subir depois |
| `intervention/image` 2.7.2 | resolve em PHP 8.5 | manter na Fase A; avaliar v3 depois (API breaking) |
| `juniorfreitas/phpquery-laravel` | `php >=5.3`; **sem uso no `app/`** | candidato a **remoção** |

## 2) Breaking changes Laravel 13 no código MyBP

| Item do upgrade guide | Afeta MyBP? | Evidência |
|---|---|---|
| CSRF → `PreventRequestForgery` | **Sim (High)** | `app/Http/Middleware/VerifyCsrfToken.php`, `Kernel.php`, `bootstrap/app.php`, `config/sanctum.php` |
| `withoutMiddleware(VerifyCsrfToken)` | Parcial | Maioria dos testes usa `withoutMiddleware()` sem classe; atualizar se houver referência explícita |
| Cache `serializable_classes` | **Baixo/Provável OK** | Cache observado usa arrays/escalares/strings (assinatura, MFA, exports, imagens data-URL). Revisar na UPG-003 |
| Prefixos cache/session | **Mitigado** | `CACHE_PREFIX`/`REDIS_PREFIX`/`SESSION_COOKIE` já configuráveis; `.env.example` tem `REDIS_PREFIX` |
| `array_first` / `array_last` | **Não** | Sem uso em `app/` (só meta IDE) |
| `upsert()` vazio | **Não** | Sem `->upsert(` em `app/` |
| `JobAttempted` / `QueueBusy` | **Não** | Sem listeners no app |
| Pagination view names | **Não observado** | Sem `pagination::default` no app |
| Session `serialization` json | **Decisão Fase B** | `config/session.php` sem chave ainda; manter `php` no cutover ou aceitar re-login |

## 3) Extensões PHP (container atual 8.2.8)

| Extensão | Classificação | Motivo |
|---|---|---|
| bcmath, ctype, curl, dom, fileinfo, filter, gd, hash, iconv, intl, json, mbstring, mysqli, mysqlnd, openssl, pcntl, pcre, PDO, pdo_mysql, posix, redis, session, SimpleXML, tokenizer, xml, xmlreader, xmlwriter, zip, zlib, opcache, exif, sodium | **Obrigatória** | Laravel / Horizon / Excel / GD Intervention / Redis |
| imagick | **Opcional** | Intervention Image sugere; código usa `Image::make` (GD suficiente). Preferir **não** instalar na base inicial (reduz CVEs) |
| mongodb | **Removível** | Sem uso em `app/` |
| memcached | **Removível** | Só driver em config; produção usa Redis |
| pgsql / pdo_pgsql | **Removível** | Default MySQL; sem uso app |
| imap, mongodb, FFI, shmop, sysv*, calendar, gettext, gmp, xsl | **Removível / fora do escopo** | Não exigidos pelos pacotes Composer do MyBP |

Python/`xlwt` no Dockerfile atual: **avaliar remoção** na imagem nova (superfície OS); só manter se script legado ainda depender.

## 4) Excel 4 — impacto funcional
- Dezenas de `app/Exports/*` e jobs de exportação.
- Sem binders custom (`DefaultValueBinder`/`bindValue`) no app → migração mecânica (return types em imports, se houver).
- Smoke obrigatório pós-bump: export CIH/padrão + 1 import (`Admissaoimport`).

## 5) Imagem / segurança (encadeia IMG-001)
- Base: `php:8.5.12-fpm-bookworm` (ADR-001)
- Manter mitigação CVE-2026-115560 (`session.upload_progress.enabled=Off` + admin flag)
- Meta scan: 0 CRITICAL/HIGH
- Registry: ECR `mybp/...`

## 6) Ordem de remediação (atualizada)
1. **IMG-001/002** — imagem base + app (extensões obrigatórias acima)
2. **UPG-002** — platform PHP 8.5 + **`maatwebsite/excel ^4`** + smoke exports (ainda L12)
3. **UPG-003** — Laravel 13 + tinker/phpunit + ui 4.x-dev + limpeza dev deps bloqueantes
4. **UPG-004** — CSRF/`PreventRequestForgery`, regressões Excel/PDF/filas
5. **UPG-005** — scan + smoke + perfil

## 7) Riscos residuais
- `laravel/ui` em **dev** na Fase B até release estável
- Debugbar / ide-helper / ignition ausentes temporariamente em L13
- Excel 4 pode exigir ajustes pontuais em Imports
- Remoção de Imagick: validar qualidade de resize de fotos

## Logs
- `.harness/discovery/UPG-001-composer-phaseA-php85.log`
- `.harness/discovery/UPG-001-composer-phaseB-l13.log`
- `.harness/discovery/UPG-001-composer-l13-dryrun.log` (tentativas intermediárias)
