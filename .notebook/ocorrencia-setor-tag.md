# Ocorrência — Setor e Tag

## Flow
1. `Ocorrencia.vue` mounted → `listaSetoresTags()` → `GET g/ocorrencia/listaSetoresTags`
2. `OcorrenciaController::listaSetoresTags()` → `OcorrenciaSetor` + `Tag` (TenantTrait / ScopeEmpresa)
3. Arrays alimentam selects do modal Nova Ocorrência e filtros da listagem

## Gotchas
- Scope filtra por `auth()->user()->empresa_id`. Empresa sem cadastro → selects só com "Selecione"
- Pillar (39765) no banco local: 5 setores, **0 tags**
- `preload` antigo era compartilhado com o load das listas e mascarava falhas; usar `preloadListas`
- Catch silencioso escondia erro HTTP; agora chama `mostraErro`
- Vue 3: `@click="formNovoTag; ..."` **não chama** o método — precisa `formNovoTag()`
- `use mysql_xdevapi\Exception` quebrava o `catch` de `mudarSetor` (classe inexistente)
- `attach('')` com `tag_id` vazio quebrava store; agora attach só se preenchido
- Filtro `whereTagId` na Tag era coluna inexistente; correto: `where('tags.id', ...)`
- Modais Cadastrar Tag/Setor listam itens da empresa; duplicidade case-insensitive bloqueada no front e no back

## Refs
- `app/Http/Controllers/OcorrenciaController.php:listaSetoresTags()`
- `resources/js/components/ocorrencia/Ocorrencia.vue:listaSetoresTags()`
- `app/Tenant/Scopes/ScopeEmpresa.php`
- `tests/Feature/OcorrenciaListaSetoresTagsTest.php`
