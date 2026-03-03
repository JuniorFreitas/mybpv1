# MyBP - Agent Guide

Purpose: quick, accurate changes in a Laravel 8 + Vue 2 monolith with strict multi-tenancy and approval flows.

## Stack Snapshot

-   Backend: Laravel 8.12 (PHP 8.2), Sanctum, Horizon, Websockets
-   Frontend: Vue 2.7 + BootstrapVue, Laravel Mix (webpack)
-   Data: MySQL/MariaDB, Redis
-   Storage/Infra: S3, Docker, ECS
-   Notable libs: Maatwebsite Excel, DomPDF, ZapMe WhatsApp, SweetAlert2

## Non-Negotiables

-   Multi-tenant: ALWAYS filter by `empresa_id` from `auth()->user()`.
-   Approval flows: gestor -> approval extra (optional) -> RH for Demissao/MudancaCargo/Ferias/ValorExtra.
-   Avoid data leakage: never query without tenant filter.
-   Prefer eager loading for relationships to avoid N+1.

## Key Paths

-   Backend: `app/Http/Controllers`, `app/Models`, `app/Services`, `app/Jobs`
-   Frontend: `resources/js/` (Vue components, mixins, app entrypoints)
-   Routes: `routes/web.php`
-   Migrations: `database/migrations`
-   Docs: `docs/`, `COMANDOS_UTEIS.md`, `docs/PADRAO_APROVACAO_EXTRA.md`

## Build / Lint / Test Commands

### Docker

-   Start: `docker compose up -d`
-   Shell: `docker compose exec mybpdp bash`

### Backend (Laravel)

-   Migrate/cache/horizon: `php artisan migrate && php artisan cache:clear && php artisan horizon`

### Frontend (Laravel Mix)

-   Dev build: `npm run dev` or `npm run development`
-   Watch: `npm run watch`
-   HMR: `npm run hot`
-   Homolog: `npm run homol`
-   Production: `npm run prod`

### Tests

-   All tests: `php artisan test`
-   PHPUnit (direct): `./vendor/bin/phpunit`

### Single Test (prefer these)

-   File: `php artisan test tests/Feature/CsvExporterTest.php`
-   Filter by class/method: `php artisan test --filter CsvExporterTest`
-   PHPUnit filter: `./vendor/bin/phpunit --filter CsvExporterTest`

### Lint / Format (manual, no npm scripts)

-   ESLint (Vue/JS): `npx eslint resources/js --ext .js,.vue`
-   Prettier check: `npx prettier --check "resources/js/**/*.{js,vue}"`
-   Prettier write: `npx prettier --write "resources/js/**/*.{js,vue}"`

## Code Style Guidelines

### PHP / Laravel

-   Follow PSR-12 conventions; keep classes and methods small and single-purpose.
-   Controllers return JSON with stable keys (`atual`, `dados`, etc.). Keep response shape consistent.
-   Prefer Form Requests for validation (`rules()`), not inline `Validator::make`.
-   Use Eloquent `fillable` and `casts` in models; define relationships explicitly.
-   Always add `->where('empresa_id', auth()->user()->empresa_id)` in queries.
-   Prefer `select()` with needed columns, not `*`.
-   Use `with()` for relations; avoid N+1 loops.
-   Use `DB::transaction()` or explicit begin/commit/rollback for critical writes.
-   Use queues (Horizon) for heavy jobs: `Job::dispatch()`.

### JS / Vue 2

-   ESLint: `eslint:recommended`, Vue rules, and Prettier enforced.
-   Prettier config: single quotes, no semicolons, 160 column width, no trailing commas.
-   Keep Vue components simple: `data()` for state, `mounted()` for initial fetch, `methods` for actions.
-   Use axios for API calls; keep endpoints in `routes/web.php` patterns.
-   Prefer `v-model` + explicit `@input`/`@keyup.enter` for filters.

### Imports and Naming

-   PHP: namespaces use PSR-4 (`App\...`). One class per file.
-   Vue: PascalCase component names; filenames match component (`MyComponent.vue`).
-   JS: camelCase for variables/functions; PascalCase for classes.

### Error Handling

-   Backend: return `response()->json(['erro' => true], 500)` on exceptions.
-   Always catch and rollback DB changes on failures.
-   Frontend: surface errors via SweetAlert2 or toast where appropriate.

## Approval Extra Pattern (Critical)

-   Applies to: `DemissaoPrevista`, `MudancaCargo`, `FeriasPrevista`, `ValorExtraPrevista`.
-   Columns: `aprovacao_extra_id`, `status_aprovacao_extra`, `obs_aprovacao_extra`, `data_aprovacao_extra`.
-   Access: `privilegio_gestao_rh` or `privilegio_aprovar_por_rh` or in `usuarios_autorizados`.
-   Full spec: `docs/PADRAO_APROVACAO_EXTRA.md`.

## Notes for Agents

-   Avoid touching unrelated files. The repo is large; keep changes focused.
-   If you add new endpoints, follow the existing `/api/modelo/...` route patterns.
-   If you add new Vue entries, register them in `webpack.mix.js` (build output path).
-   Check existing patterns before inventing new ones.
