# IMG-001 — Imagem base MyBP PHP 8.5

## Tags locais
- `mybp/php-base:8.5.12-bookworm` (pin de patch)
- `mybp/php-base:8.5-bookworm` (alias menor)

## Base upstream
- `php:8.5.12-fpm-bookworm` (Docker Official Images)

## Política de rebump
1. Quando sair patch PHP 8.5.x (ou advisory), atualizar `ARG PHP_IMAGE` em `docker/base/Dockerfile`.
2. Rebuild local + scan Trivy CRITICAL/HIGH (`--ignore-unfixed` e full).
3. Publicar no ECR `mybp/...` somente com aprovação de deploy.
4. Manter `session.upload_progress.enabled=Off` até patch oficial do CVE-2026-115560.

## Extensões (UPG-001)
bcmath, exif, gd, intl, mysqli, pcntl, pdo_mysql, sockets, zip, redis; mbstring + Zend OPcache da imagem oficial.

## Não instaladas
imagick, mongodb, memcached, pgsql; headers/dev removidos pós-build.

## Mitigação CVE session upload progress
- `docker/base/php/conf.d/99-mybp.ini` → Off
- `docker/base/php/fpm-pool.d/zz-cve-2026-115560.conf` → `php_admin_flag` Off

## Evidências (2026-11-13)
- `php -v` → **8.5.12**
- Módulos gd/zip/redis/intl/bcmath/pcntl/mysqli OK após purge de *-dev*
- `ini_get(session.upload_progress.enabled)` → Off (`''`)
- Trivy **fixable** CRITICAL/HIGH: **0**
- Trivy **all** CRITICAL/HIGH: **134** (3 CRITICAL + 131 HIGH) — sem fix Debian Bookworm disponível; topo: krb5, libxml2, sqlite, curl
- Artefatos: `IMG-001-trivy.txt` (scan anterior), `IMG-001-trivy-summary.txt`

## Aceite de segurança
**Aprovado pelo usuário em 2026-11-13:** RNF-001 = **0 CRITICAL/HIGH com correção disponível (`--ignore-unfixed`)**. Residuais sem patch de distro documentados e aceitos até rebase OS/PHP.

## Push ECR
Pendente aprovação humana (não executado).
