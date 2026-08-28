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
| Referência viva | `CentroCusto.vue`, `Beneficio.vue`, `resources/views/g/cadastros/vagas/index.blade.php` + `resources/js/g/cadastros/vagas/app.js` |

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
        <div class="col-12 col-lg-5 mybp-combobox-wrap">
            <div class="form-group mb-2 mb-lg-0">
                <label class="mybp-label" for="meu-cnpj">Por Cnpj</label>
                <combobox-auto-complete
                    input-id="meu-cnpj"
                    instance-id="meu-cnpj"
                    v-model="controle.dados.campoCnpj"
                    :options="cnpjComboboxOpcoesFiltro"
                />
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

## Combobox (CNPJ / lotação)

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

- [ ] Importar `FiltroListagem` e usar slots `#filtros` / `#acoes`
- [ ] Labels com `mybp-label` (ou `ma-label` — alias visual igual em Minhas Avaliações)
- [ ] CNPJ com `ComboboxAutoComplete`, não `<select>` nativo
- [ ] Cards com classes `mybp-*`, não CSS duplicado no componente
- [ ] Botões de ação na segunda linha do filtro
- [ ] Estados: loading (`preload`), vazio (`alert-warning`), info contextual (`alert-info`)

## Referências cruzadas

- Combobox e filtros avançados: `resources/js/components/cadastros/avaliacoes/avaliar/index.vue`
- Cards similares: `resources/js/components/administracao/aprovacao-extra-config/AprovacaoExtraConfig.vue`
