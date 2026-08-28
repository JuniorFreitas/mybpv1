# Padrão UX/UI — Listagens de cadastro

Guia reutilizável para telas de listagem com filtros, cards e combobox (mesmo padrão de **Centro de Custo** e **Minhas Avaliações**).

## Quando usar

- Cadastros com filtros + listagem em cards (não tabela densa)
- Filtro por CNPJ/lotação com busca digitável
- Ações primárias: **Atualizar** e **Cadastrar**

## Arquivos do padrão

| Recurso | Caminho |
|---|---|
| Estilos globais | `resources/sass/_mybp-listagem-ui.scss` |
| Wrapper de filtros | `resources/js/components/ui/FiltroListagem.vue` |
| Combobox | `resources/js/components/ComboboxAutoComplete.vue` |
| Referência viva | `CentroCusto.vue`, `Beneficio.vue`, `vagas/index.blade.php`, `vagas_abertas/index.blade.php` |

## Estrutura de filtros

**Linha 1:** apenas campos de filtro (`align-items-end`).

**Linha 2:** apenas botões de ação.

```vue
<FiltroListagem @submit="onSubmitFiltro">
    <template #filtros>
        <div class="col-12 col-lg-4">
            <div class="form-group mb-2 mb-lg-0">
                <label class="mybp-label" for="meu-filtro">Buscar</label>
                <input id="meu-filtro" class="form-control form-control-sm" />
            </div>
        </div>
                <div class="col-12 col-lg-6">
                    <div class="form-group mb-2 mb-lg-0">
                        <label class="mybp-label" for="meu-filtro-status">Status</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroStatus"
                                instance-id="filtro-status"
                                v-model="controle.dados.campoStatus"
                                :options="filtroStatusOpcoes"
                                input-id="meu-filtro-status"
                                placeholder-blur="Todos os status"
                                empty-message="Nenhuma opção encontrada."
                                :max-results="10"
                                @opening="fecharOutrosComboboxes('filtro-status')"
                                @select="onSelectFiltro"
                            />
                        </div>
                    </div>
                </div>
    </template>

    <template #acoes>
        <button type="button" class="btn btn-sm btn-success" @click="atualizar">
            <i class="fa fa-sync"></i> Atualizar
        </button>
        <button type="button" class="btn btn-sm btn-secondary" @click="abrirModalNovo">
            <i class="fa fa-plus"></i> Cadastrar
        </button>
    </template>
</FiltroListagem>
```

### Grid sugerida (3 filtros + filial)

| Campo | Colunas `lg` |
|---|---|
| Buscar | 4 |
| Status | 3 |
| Por Cnpj | 5 |

Sem filial: Buscar `col-lg-6` + Status `col-lg-6`.

## Combobox nos filtros

**Regra:** todo filtro que não seja busca livre usa `ComboboxAutoComplete` — status, enums, CNPJ, cargo, cidade, etc. **Não usar `<select>` nativo** nos filtros.

Opções no formato `{ value, label, meta?, raw? }`:

```javascript
const cnpjComboboxOpcoes = computed(() =>
    Object.entries(listaCcs.value?.cnpjs ?? {}).map(([key, item]) => ({
        value: key,
        label: `${item.nome_fantasia || item.razao_social} - ${item.cnpj}`,
        meta: item.matriz ? 'Matriz' : 'Filial',
        raw: item
    }))
)

const cnpjComboboxOpcoesFiltro = computed(() => [
    { value: '', label: 'Todos os CNPJs' },
    ...cnpjComboboxOpcoes.value
])
```

Filtro vazio (`value: ''`) = todos. No cadastro/edição, não incluir opção vazia — matriz como padrão.

**Combobox:** `@select="onSelectFiltro"` → `atualizar()` imediato (sem clicar em Atualizar).

### Query params (histórico compartilhável)

Usar `resources/js/utils/listagemQueryParams.js`:

1. Definir `CAMPOS_FILTRO_URL` com todas as chaves de `controle.dados`
2. No `mounted`: `lerFiltrosDaUrl()` → `$nextTick` → `atualizar()`
3. Watch: `criarWatchQueryParams(CAMPOS_FILTRO_URL)` (debounce 400ms)
4. URL via `history.replaceState` (igual planejamento/movimentacao)

Exemplo: `/g/cadastro/areas?campoBusca=comercial&campoStatus=false&page=2`

**Paginação:** `page` (página atual, se > 1) e `pages` (itens por página, se ≠ padrão). Sincronizar também em `carregou()` após navegar entre páginas.

### Status (enum fixo)

```javascript
filtroStatusOpcoes() {
    return [
        { value: '', label: 'Todos os status' },
        { value: 'true', label: 'Apenas ativos' },
        { value: 'false', label: 'Apenas inativos' }
    ]
}
```

### CNPJ / lotação

Ver bloco `cnpjComboboxOpcoesFiltro` acima (primeira opção `Todos os CNPJs`).

## Listagem em cards

```vue
<div class="mybp-cards-lista">
    <div class="mybp-card" v-for="item in lista" :key="item.id">
        <div class="mybp-card-header-row">
            <div class="mybp-card-left">
                <span class="mybp-badge-id">#{{ item.id }}</span>
                <div class="mybp-card-titulo"><strong>{{ item.label }}</strong></div>
            </div>
            <div class="mybp-card-right">
                <!-- status, dropdown ações -->
            </div>
        </div>
        <div class="mybp-card-details-row">
            <div class="mybp-detail-item">
                <i class="fas fa-map-marker-alt text-muted"></i>
                <span class="mybp-detail-label">Lotação</span>
                <span class="mybp-detail-value">{{ lotacao(item) }}</span>
            </div>
        </div>
    </div>
</div>
```

Blocos destacados (ex.: gestores): `mybp-card-destaque` + modificadores `--primary` / `--info`.

## Classes CSS (`mybp-*`)

Prefixo **`mybp-`** evita conflito com estilos locais. Principais:

- Filtros: `mybp-label`, `mybp-filtros-form`, `mybp-filtros-botoes`, `mybp-combobox-wrap`
- Cards: `mybp-cards-lista`, `mybp-card`, `mybp-card-header-row`, `mybp-badge-id`
- Detalhes: `mybp-detail-item`, `mybp-detail-label`, `mybp-detail-value`
- Ações: `mybp-btn-acoes-compact`, `mybp-dropdown-menu`

Estilos carregados via `resources/sass/app.scss` → `@import "mybp-listagem-ui"`.

## Checklist ao migrar uma tela

- [ ] Importar `FiltroListagem` e `ComboboxAutoComplete`
- [ ] Filtros enum/status/CNPJ via combobox (sem `<select>` nativo)
- [ ] Labels com `mybp-label` (ou `ma-label` — alias visual igual em Minhas Avaliações)
- [ ] `@select="onSelectFiltro"` em combobox (atualiza listagem na hora)
- [ ] Query params com `listagemQueryParams.js` (`CAMPOS_FILTRO_URL` + watch)
- [ ] Cards com classes `mybp-*`, não CSS duplicado no componente
- [ ] Botões de ação na segunda linha do filtro
- [ ] Estados: loading (`preload`), vazio (`alert-warning`), info contextual (`alert-info`)

## Referências cruzadas

- Combobox e filtros avançados: `resources/js/components/cadastros/avaliacoes/avaliar/index.vue`
- Skill de migração: `.cursor/skills/mybp-front-cardlist/SKILL.md`
- Cards similares: `resources/js/components/administracao/aprovacao-extra-config/AprovacaoExtraConfig.vue`
