# Cadastro de Documentos da Pré-admissão — Tasks

## Execution Protocol (MANDATORY -- do not skip)

Implement these tasks with the `tlc-spec-driven` skill: **activate it by name and follow its Execute flow and Critical Rules.** Do not search for skill files by filesystem path. The skill is the source of truth for the full flow (per-task cycle, sub-agent delegation, adequacy review, Verifier, discrimination sensor).

**If the skill cannot be activated, STOP and tell the user — do not proceed without it.**

---

**Design**: `.specs/features/cadastro-documentos-preadmissao/design.md`
**Status**: Done (aguardando Verifier)

---

## Test Coverage Matrix

> Generated from codebase, project guidelines, and spec — confirm before Execute. Guidelines found: `AGENTS.md` §16 (PHPUnit, SQLite `:memory:`, never real DB), `phpunit.xml`, padrão `tests/Unit/Services/Dossie/DossieTipoCadastroServiceTest.php` + `tests/Feature/DossieTipoCadastroTest.php`. Sem testes Vue/e2e para cadastros.

| Code Layer | Required Test Type | Coverage Expectation | Location Pattern | Run Command |
| --- | --- | --- | --- | --- |
| Service (domínio) | unit | 1:1 ACs PREADM-02..08 + edge cases da spec | `tests/Unit/Services/Preadmissao/*Test.php` | `./vendor/bin/phpunit tests/Unit/Services/Preadmissao` |
| Controller / rotas | feature | Rotas: happy + 403 + 404 outra empresa + duplicado + validação | `tests/Feature/DocumentoPreadmissaoCadastroTest.php` | `./vendor/bin/phpunit tests/Feature/DocumentoPreadmissaoCadastroTest.php` |
| Models / cache getter | unit (via service) | Getter não zera cache; mutação invalida; categoria null-safe | mesmo suite unit | mesmo unit |
| Seeder / menu / Vue / mix | none | Gate build (assets compilam) | — | `npx mix` no entry do módulo (dev) |

## Parallelism Assessment

> Generated from codebase — confirm before Execute.

| Test Type | Parallel-Safe? | Isolation Model | Evidence |
| --- | --- | --- | --- |
| unit | No | SQLite `:memory:` compartilhado no processo; Schema::create no setUp | `tests/Feature/DossieTipoCadastroTest.php` |
| feature | No | Idem; `withoutMiddleware` + Gate::define | mesmo arquivo |
| e2e Vue | n/a | — | cadastros sem Playwright |

## Gate Check Commands

> Generated from codebase — confirm before Execute.

| Gate Level | When to Use | Command |
| --- | --- | --- |
| Quick | Após tasks com unit | `./vendor/bin/phpunit tests/Unit/Services/Preadmissao` |
| Full | Após feature tests | `./vendor/bin/phpunit tests/Unit/Services/Preadmissao tests/Feature/DocumentoPreadmissaoCadastroTest.php` |
| Build | Após UI / mix | `npx mix` (entry documentospreadmissao) + full phpunit acima |

---

## Execution Plan

### Phase 1: Foundation

T1 → T2  (T2 pode ser [P] após T1 só por clareza de seeder; T2 não depende de T1)

```
T1 ──→ T2
```

Na prática T1 e T2 não compartilham arquivos: T2 [P] junto com T1.

### Phase 2: Domain

```
T1 → T3
```

### Phase 3: API + UI

```
T3 → T4 → T5
```

---

## Task Breakdown

### T1: Corrigir models e cache do catálogo

**What**: `$table` da categoria, timestamps, relações, getter de cache sem forget-on-read, `limparCache`, categoria null-safe.
**Where**: `app/Models/DocumentosCurriculosCatAdmissaoEmpresa.php`, `app/Models/DocumentosCurriculosAdmissaoEmpresa.php`
**Depends on**: None
**Reuses**: `DossieTipo::limparCache()` (ideia de invalidação explícita)
**Requirement**: PREADM-08

**Tools**: Skill `tlc-spec-driven`; sem MCP

**Done when**:

- [x] Categoria usa tabela `documentos_curriculos_cat_adm_empresa`
- [x] Ambos `$timestamps = false`
- [x] Getter usa `Cache::get`/`put` TTL 168h e eager-load/null-safe da categoria
- [x] `limparCache(int $empresaId)` esquece `docAdmEmpresa_{id}`

**Tests**: none (coberto em T3)
**Gate**: none neste task (T3 é o gate)
**Commit**: `fix(preadmissao): corrigir model de categoria e cache do catálogo`

---

### T2: Habilidades de permissão [P]

**What**: Inserir `cadastro_documentos_preadmissao`, `_insert`, `_update` no seeder.
**Where**: `database/seeders/HabilidadesTableSeeder.php`
**Depends on**: None
**Reuses**: bloco `cadastro_dossie_tipos`
**Requirement**: PREADM-01

**Tools**: NONE

**Done when**:

- [x] Três habilidades com descrições no padrão dos cadastros
- [x] Papel 1 continua anexando todas no final do seeder (já existente)

**Tests**: none
**Gate**: none
**Commit**: `feat(preadmissao): adicionar permissões do cadastro de documentos`

---

### T3: Service de cadastro + testes unitários

**What**: `DocumentoPreadmissaoCadastroService` com listagem, CRUD, identificadores, categoria find-or-create, flags, sanitização e invalidação de cache.
**Where**: `app/Services/Preadmissao/DocumentoPreadmissaoCadastroService.php`, `tests/Unit/Services/Preadmissao/DocumentoPreadmissaoCadastroServiceTest.php`
**Depends on**: T1
**Reuses**: `DossieTipoCadastroService` (identificadores, DomainException, transação)
**Requirement**: PREADM-02, PREADM-03, PREADM-04, PREADM-05, PREADM-06, PREADM-07, PREADM-08

**Tools**: Skill `tlc-spec-driven`

**Done when**:

- [x] Listar só da empresa; filtros busca/status/categoria
- [x] Criar gera `tipo` snake + `metodo` Studly; ignora tipo/metodo do payload
- [x] Duplicidade de tipo na empresa lança DomainException
- [x] Update não altera tipo/metodo
- [x] Categoria nova find-or-create na transação
- [x] Flags de arquivo mutuamente exclusivas; max >= min
- [x] Descrição sanitizada (sem script; permite `<a>`)
- [x] Mutações chamam `limparCache`
- [x] Gate quick passa
- [x] Test count: ≥ 10 testes pass

**Tests**: unit
**Gate**: quick
**Commit**: `feat(preadmissao): service de cadastro dos documentos da pré-admissão`

---

### T4: Controller, rotas e testes de feature

**What**: Controller fino, rotas em `/g/cadastro/documentos-preadmissao`, feature tests (403, isolamento, store, update, toggle).
**Where**: `app/Http/Controllers/DocumentoPreadmissaoController.php`, `routes/web.php`, `tests/Feature/DocumentoPreadmissaoCadastroTest.php`
**Depends on**: T2, T3
**Reuses**: `DossieTipoController` + `DossieTipoCadastroTest`
**Requirement**: PREADM-01, PREADM-02, PREADM-03, PREADM-05, PREADM-06

**Tools**: Skill `tlc-spec-driven`

**Done when**:

- [x] `authorize` / middleware `can:cadastro_documentos_preadmissao*`
- [x] JSON no contrato `atual`/`ultima`/`total`/`dados.items` + `categorias`
- [x] Feature: store 201, listagem não vaza outra empresa, update preserva tipo, 404 cross-tenant
- [x] Gate full passa
- [x] Test count: ≥ 4 feature tests pass

**Tests**: feature
**Gate**: full
**Commit**: `feat(preadmissao): API de cadastro dos documentos da pré-admissão`

---

### T5: Tela Vue + menu + mix

**What**: Listagem/cards/modal no padrão DossieTipos; menu Cadastro; webpack entry.
**Where**: `resources/js/components/cadastros/documentospreadmissao/DocumentosPreadmissao.vue`, `resources/js/g/cadastros/documentospreadmissao/app.js`, `resources/views/g/cadastros/documentospreadmissao/index.blade.php`, `resources/views/layouts/menu.blade.php`, `webpack.mix.js`
**Depends on**: T4
**Reuses**: `DossieTipos.vue`, `PADRAO_UX_LISTAGEM_CADASTROS.md`, skill `mybp-front-cardlist`, `frontend-ux-senior`
**Requirement**: PREADM-02, PREADM-07

**Tools**: Skills `mybp-front-cardlist`, `frontend-ux-senior`

**Done when**:

- [x] Filtros: busca, status, categoria (ComboboxAutoComplete); limpar filtros; query params
- [x] Cards mybp-* com id, nome, categoria, ordem, tipo, flags resumidas, bt-ativo, editar
- [x] Modal: nome*, categoria* (combobox + nova), descrição TinyMCE simples+link, ordem, ativo, flags (obrigatório, tipo arquivo, múltiplos, min/max, só gestão)
- [x] Menu Cadastro > Documentos da Pré-admissão com `@can`
- [x] Mix gera `public/js/g/documentospreadmissao/app.js`
- [x] Gate build: full phpunit + mix do entry

**Tests**: none
**Gate**: build
**Commit**: `feat(preadmissao): tela de cadastro dos documentos da pré-admissão`

---

## Parallel Execution Map

```
Phase 1:
  T1 [models/cache]
  T2 [P] [seeder]

Phase 2:
  T1 complete → T3 [service + unit]

Phase 3:
  T2 + T3 complete → T4 [controller + feature] → T5 [Vue + menu]
```

---

## Task Granularity Check

| Task | Scope | Status |
| --- | --- | --- |
| T1: Models + cache | 2 models do mesmo bounded context | ⚠️ OK if cohesive |
| T2: Seeder permissões | 1 arquivo | ✅ Granular |
| T3: Service + unit tests | 1 service + testes co-localizados | ✅ Granular |
| T4: Controller + rotas + feature | 1 endpoint group + testes | ⚠️ OK if cohesive |
| T5: Vue + blade + mix + menu | tela completa (padrão DossieTipos é um SFC) | ⚠️ OK if cohesive |

---

## Diagram-Definition Cross-Check

| Task | Depends On (task body) | Diagram Shows | Status |
| --- | --- | --- | --- |
| T1 | None | início | ✅ Match |
| T2 | None | [P] com T1 | ✅ Match |
| T3 | T1 | T1 → T3 | ✅ Match |
| T4 | T2, T3 | T2+T3 → T4 | ✅ Match |
| T5 | T4 | T4 → T5 | ✅ Match |

---

## Test Co-location Validation

| Task | Code Layer Created/Modified | Matrix Requires | Task Says | Status |
| --- | --- | --- | --- | --- |
| T1 | Models / cache getter | unit (via service) | none (coberto em T3) | ⚠️ merge-forward explícito no Done when do T3 |
| T2 | Seeder | none | none | ✅ OK |
| T3 | Service + cache getter | unit | unit | ✅ OK |
| T4 | Controller / rotas | feature | feature | ✅ OK |
| T5 | Vue / menu / mix | none | none | ✅ OK |

T1 sem testes próprios: o getter/cache só é verificável com o service; T3 inclui os testes do getter (não é deferral órfão).
