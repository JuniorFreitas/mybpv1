# Mensagem de Aniversariante Tasks

## Execution Protocol (MANDATORY -- do not skip)

Implement these tasks with the `tlc-spec-driven` skill: **activate it by name and follow its Execute flow and Critical Rules.** Do not search for skill files by filesystem path. The skill is the source of truth for the full flow (per-task cycle, sub-agent delegation, adequacy review, Verifier, discrimination sensor).

**Design**: inline (padrão Carta Oferta)
**Status**: In Progress

---

## Test Coverage Matrix

> Generated from codebase, project guidelines, and spec. Guidelines found: `AGENTS.md` (PHPUnit em sqlite `:memory:`).

| Code Layer | Required Test Type | Coverage Expectation | Location Pattern | Run Command |
| --- | --- | --- | --- | --- |
| Renderer / Resolver | unit | 1:1 ACs de placeholder, sanitização, fallback | `tests/Unit/*Aniversariante*` | `php vendor/bin/phpunit tests/Unit --filter Aniversariante` |
| Entity / migration / Vue | none | build gate | — | PHPUnit unit + arquivos presentes |
| Mail wiring | unit | fallback + custom no HTML resolvido | `tests/Unit/*Aniversariante*` | mesmo comando |

## Parallelism Assessment

| Test Type | Parallel-Safe? | Isolation Model | Evidence |
| --- | --- | --- | --- |
| unit | Yes | sqlite `:memory:` / sem DB no renderer | `phpunit.xml` `DB_DATABASE=:memory:` |

## Gate Check Commands

| Gate Level | When to Use | Command |
| --- | --- | --- |
| Quick | Após testes unitários | `php vendor/bin/phpunit tests/Unit --filter Aniversariante` |
| Build | Fim da feature | `php vendor/bin/phpunit tests/Unit --filter Aniversariante` |

---

## Execution Plan

```
Phase 1 (domain)
  T1 renderer+tests
  T2 model+migration+resolver+tests
Phase 2 (app)
  T3 controller+rotas+menu
  T4 tela Vue+mix+blade
Phase 3 (envio)
  T5 mail+jobs usam resolver
```

---

### T1: Renderer de placeholders e sanitização

**Depends on**: none
**Files**: `app/Services/Aniversariante/AniversarianteMensagemRenderer.php`, `tests/Unit/AniversarianteMensagemRendererTest.php`
**Done when**: `{{nome}}`/`{{empresa}}` substituídos; placeholder inexistente → vazio; `<script>` removido
**Tests**: unit | **Gate**: quick
**Req**: ANIV-03

### T2: Persistência e fallback

**Depends on**: T1
**Files**: migration, `app/Models/AniversarianteMensagemTemplate.php`, `app/Services/Aniversariante/AniversarianteMensagemResolver.php`, `tests/Unit/AniversarianteMensagemResolverTest.php`
**Done when**: sem registro/vazio → mensagem padrão; com HTML → HTML da empresa; query sem ScopeEmpresa
**Tests**: unit | **Gate**: quick
**Req**: ANIV-01, ANIV-02, ANIV-05

### T3: API e menu

**Depends on**: T2
**Files**: controller, `routes/web.php`, `menu.blade.php`
**Done when**: dados/salvar/preview/index sob `can:administracao_aniversariantes`; save invalida cache
**Tests**: none (build) | **Gate**: build
**Req**: ANIV-01, ANIV-02, ANIV-03

### T4: Tela Vue

**Depends on**: T3
**Files**: Vue, app.js, blade, webpack.mix.js
**Done when**: editor TinyMCE, placeholders, salvar, preview
**Tests**: none | **Gate**: build
**Req**: ANIV-01, ANIV-03

### T5: E-mail usa mensagem da empresa

**Depends on**: T2
**Files**: `AniversariantesMail.php`, `email/aniversariantes/parabens.blade.php`
**Done when**: corpo vem do resolver+renderer; assunto inalterado
**Tests**: unit no resolver já cobre fallback; mail usa os serviços
**Gate**: quick
**Req**: ANIV-04, ANIV-05, ANIV-06
