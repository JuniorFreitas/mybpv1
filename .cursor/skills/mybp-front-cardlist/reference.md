# Referência — mybp-front-cardlist

## Componentes

| Componente | Caminho |
|---|---|
| FiltroListagem | `resources/js/components/ui/FiltroListagem.vue` |
| ComboboxAutoComplete | `resources/js/components/ComboboxAutoComplete.vue` |
| ControlePaginacao | `resources/js/components/ControlePaginacao.vue` |
| Estilos | `resources/sass/_mybp-listagem-ui.scss` |

## Template — FiltroListagem (Vue)

**Buscar** permanece `<input>` texto. **Demais filtros** usam `ComboboxAutoComplete`.

```vue
<filtro-listagem
    @submit="onSubmitFiltro"
    :mostrar-limpar-filtros="temFiltrosAtivos"
    :desabilitado="controle.carregando"
    @limpar="limparFiltros"
>
    <template #filtros>
        <div class="col-12 col-lg-6">
            <div class="form-group mb-2 mb-lg-0">
                <label class="mybp-label" for="modulo-filtro-busca">Buscar</label>
                <input
                    id="modulo-filtro-busca"
                    type="text"
                    placeholder="Buscar por nome ou ID"
                    autocomplete="off"
                    class="form-control form-control-sm"
                    :disabled="controle.carregando"
                    v-model="controle.dados.campoBusca"
                />
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="form-group mb-2 mb-lg-0">
                <label class="mybp-label" for="modulo-filtro-status">Status</label>
                <div class="mybp-combobox-wrap">
                    <combobox-auto-complete
                        ref="comboFiltroStatus"
                        instance-id="filtro-status"
                        v-model="controle.dados.campoStatus"
                        :options="filtroStatusOpcoes"
                        :disabled="controle.carregando"
                        input-id="modulo-filtro-status"
                        placeholder-blur="Todos os status"
                        empty-message="Nenhuma opção encontrada."
                        :max-results="10"
                        @opening="fecharOutrosComboboxes('filtro-status')"
                        @select="onSelectFiltro"
                    ></combobox-auto-complete>
                </div>
            </div>
        </div>
    </template>

    <template #acoes>
        <button type="submit" class="btn btn-sm btn-success" :disabled="controle.carregando">
            <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i> Atualizar
        </button>
        <button type="button" class="btn btn-sm btn-secondary" :disabled="controle.carregando" @click="formNovo">
            <i class="fa fa-plus"></i> Cadastrar
        </button>
    </template>
</filtro-listagem>
```

**Enter no Buscar:** o `FiltroListagem` emite `submit` ao pressionar Enter em `<input type="text">` (ignora combobox). Garanta `@submit="onSubmitFiltro"` → `atualizar()`.

## Limpar filtros

```javascript
import { temFiltrosPreenchidos, limparFiltrosListagem } from '../../../utils/listagemQueryParams'

const CAMPOS_FILTRO_URL = ['campoBusca', 'campoStatus']

computed: {
    temFiltrosAtivos() {
        return temFiltrosPreenchidos(this.controle.dados, CAMPOS_FILTRO_URL)
    }
},
methods: {
    limparFiltros() {
        limparFiltrosListagem(this.controle.dados, CAMPOS_FILTRO_URL)
        this.fecharOutrosComboboxes(null)
        this.atualizar()
    }
}
```

```vue
<FiltroListagem
    :mostrar-limpar-filtros="temFiltrosAtivos"
    :desabilitado="controle.carregando"
    @limpar="limparFiltros"
>
```

Botão renderizado pelo `FiltroListagem` (`btn-outline-secondary`), oculto quando nenhum filtro está preenchido.

## Combobox nos filtros

### Quando usar combobox

| Tipo de filtro | Componente |
|---|---|
| Busca livre (nome, ID, título) | `<input type="text">` |
| Status ativo/inativo | `ComboboxAutoComplete` |
| Enum fixo (sim/não, esgotado, com vínculo) | `ComboboxAutoComplete` |
| CNPJ / lotação | `ComboboxAutoComplete` |
| Lista dinâmica (cargo, cidade, projeto) | `ComboboxAutoComplete` |

### Opções — enum fixo (status)

```javascript
computed: {
    filtroStatusOpcoes() {
        return [
            { value: '', label: 'Todos os status' },
            { value: 'true', label: 'Apenas ativos' },
            { value: 'false', label: 'Apenas inativos' }
        ]
    }
}
```

Backend compatível com string ou booleano:

```php
if ($request->filled('campoStatus')) {
    $status = filter_var($request->campoStatus, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    if ($status !== null) {
        $query->whereAtivo($status);
    }
}
```

### Opções — lista dinâmica

```javascript
computed: {
    filtroCargoOpcoes() {
        const cargos = (this.filtroOpcoes.cargos || []).map((cargo) => ({
            value: cargo.id,
            label: cargo.nome
        }))
        return [{ value: '', label: 'Todos os cargos' }, ...cargos]
    }
}
```

### Opções — CNPJ (com meta Matriz/Filial)

```javascript
const cnpjComboboxOpcoesFiltro = computed(() => [
    { value: '', label: 'Todos os CNPJs' },
    ...Object.entries(listaCcs.value?.cnpjs ?? {}).map(([key, item]) => ({
        value: key,
        label: `${item.nome_fantasia || item.razao_social} - ${item.cnpj}`,
        meta: item.matriz ? 'Matriz' : 'Filial',
        raw: item
    }))
])
```

### Handlers (Options API)

```javascript
mounted() {
    document.addEventListener('click', this.onClickOutside)
},
beforeUnmount() {
    document.removeEventListener('click', this.onClickOutside)
},
methods: {
    onSubmitFiltro() { this.atualizar() },
    onSelectFiltro() { this.atualizar() },
    fecharOutrosComboboxes(manter) {
        const combos = [
            ['filtro-status', 'comboFiltroStatus'],
            ['filtro-cnpj', 'comboFiltroCnpj']
        ]
        combos.forEach(([id, refName]) => {
            if (id !== manter && this.$refs[refName]?.close) {
                this.$refs[refName].close()
            }
        })
    },
    onClickOutside(event) {
        if (event?.target?.closest?.('.dropdown')) return
        if (event?.target?.closest?.('.mybp-combobox-wrap')) return
        this.dropdownAbertoKey = null
        this.fecharOutrosComboboxes(null)
    }
}
```

### Handlers (Composition API)

Mesma lógica com `ref()` para `dropdownAbertoKey` e refs dos comboboxes (`comboFiltroCnpj.value?.close()`).

### Template — 3 filtros com combobox (Projetos)

```vue
<div class="col-12 col-lg-4">
    <label class="mybp-label" for="projeto-filtro-disponibilidade">Disponibilidade</label>
    <div class="mybp-combobox-wrap">
        <combobox-auto-complete
            ref="comboFiltroDisponibilidade"
            instance-id="filtro-disponibilidade"
            v-model="controle.dados.campoDisponibilidade"
            :options="filtroDisponibilidadeOpcoes"
            input-id="projeto-filtro-disponibilidade"
            placeholder-blur="Todos"
            empty-message="Nenhuma opção encontrada."
            :max-results="10"
            @opening="fecharOutrosComboboxes('filtro-disponibilidade')"
            @select="onSelectFiltro"
        ></combobox-auto-complete>
    </div>
</div>
```

Referências: `projeto/index.blade.php`, `vagas_abertas/index.blade.php`, `CentroCusto.vue`.

## Query params na URL

Utilitário: `resources/js/utils/listagemQueryParams.js` (padrão de `planejamento/movimentacao`).

```javascript
import {
    lerFiltrosDaUrl,
    sincronizarFiltrosNaUrl,
    criarWatchQueryParams
} from '../../../utils/listagemQueryParams'

const CAMPOS_FILTRO_URL = ['campoBusca', 'campoStatus']

// mounted
urlParamGetFiltros() {
    lerFiltrosDaUrl(this.controle.dados, CAMPOS_FILTRO_URL)
}

// watch
watch: {
    'controle.dados': criarWatchQueryParams(CAMPOS_FILTRO_URL)
}

// combobox @select
onSelectFiltro() {
    this.atualizar()
}
```

Exemplo de URL: `/g/cadastro/areas?campoBusca=rh&campoStatus=true&page=2`

### Paginação

| Param | Descrição |
|---|---|
| `page` | Página atual (omitido quando `1`) |
| `pages` | Itens por página em `controle.dados.pages` (omitido quando = padrão da tela) |

```javascript
const paginaInicial = lerPaginacaoDaUrl(controle.dados, { pagesDefault: 20 })
aplicarPaginaInicialListagem(vm, paginaInicial)
buscarListagem(vm, { resetPagina: false }) // carga inicial

// após paginar (carregou)
montarExtrasPaginacao(vm, { pagesDefault: 20 }) // → { page: '3' }
```

Valores vazios são omitidos da query string.

## Template — Card com destaques

```vue
<div class="mybp-cards-lista" v-show="!controle.carregando && lista.length > 0">
    <div class="mybp-card" v-for="item in lista" :key="item.id">
        <div class="mybp-card-header-row">
            <div class="mybp-card-left">
                <span class="mybp-badge-id">#{{ item.id }}</span>
                <div class="mybp-card-titulo">
                    <strong>{{ item.label }}</strong>
                </div>
            </div>
            <div class="mybp-card-right">
                <bt-ativo
                    :rota="`cadastro/modulo/${item.id}/ativa-desativa`"
                    :model="item"
                    @atualizou="atualizar()"
                ></bt-ativo>
                <div class="dropdown" :class="{ show: isDropdownOpen(item.id) }">
                    <a
                        class="mybp-btn-acoes-compact"
                        href="#"
                        role="button"
                        aria-haspopup="true"
                        :aria-expanded="isDropdownOpen(item.id) ? 'true' : 'false'"
                        @click.prevent.stop="toggleDropdown(item.id)"
                    >
                        <i class="fas fa-ellipsis-v"></i>
                    </a>
                    <div
                        class="dropdown-menu mybp-dropdown-menu dropdown-menu-right"
                        :class="{ show: isDropdownOpen(item.id) }"
                        @click="fecharDropdown"
                    >
                        <a class="dropdown-item" href="javascript://" @click.prevent="abrirEdicao(item.id)">
                            <i class="fa fa-edit mr-1"></i> Editar
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="mybp-card-conteudo">
            <div class="mybp-card-secoes-row">
                <div class="mybp-card-destaque mybp-card-destaque--primary">
                    <i class="fas fa-user-tie text-primary"></i>
                    <div class="mybp-card-destaque-info">
                        <small class="mybp-card-destaque-etapa">Gestor</small>
                        <strong class="mybp-card-destaque-valor mybp-card-destaque-valor--wrap">
                            {{ item.gestor_nome || 'Não informado' }}
                        </strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
```

## Template — Card com detalhes (linha)

```vue
<div class="mybp-card-details-row">
    <div class="mybp-detail-item">
        <i class="fas fa-map-marker-alt text-muted"></i>
        <span class="mybp-detail-label">Lotação</span>
        <span class="mybp-detail-value">{{ lotacao(item) }}</span>
    </div>
</div>
```

## Template — Modal obrigatórios

```vue
<fieldset>
    <legend>Dados do cadastro</legend>
    <p class="mybp-campo-obrigatorio-legenda mb-3">
        Campos com <span class="text-danger">*</span> são obrigatórios.
    </p>
    <div class="col-12">
        <label class="mybp-label" for="modulo-form-nome">
            Nome <span class="text-danger">*</span>
        </label>
        <input
            id="modulo-form-nome"
            class="form-control form-control-sm"
            type="text"
            onblur="valida_campo_vazio(this, 1)"
            v-model="form.nome"
        />
    </div>
</fieldset>
```

## Backend — enrich com batch (pivot)

```php
$resultado = $query->paginate($porPagina);
$items = collect($resultado->items());

$extras = DB::table('tabela_pivot')
    ->whereIn('entidade_id', $items->pluck('id'))
    ->pluck('campo_extra', 'entidade_id');

$items = $items->map(function (Model $item) use ($extras) {
    $item->campo_extra = $extras[$item->id] ?? null;
    $item->gestor_nome = $item->Gestor?->nome;

    return $item;
});

return response()->json([
    'atual' => $resultado->currentPage(),
    'ultima' => $resultado->lastPage(),
    'total' => $resultado->total(),
    'dados' => [
        'items' => $items->values()->all(),
        'empresa_id' => auth()->user()->empresa_id,
    ],
], 200);
```

## Classes CSS principais

| Grupo | Classes |
|---|---|
| Filtros | `mybp-label`, `mybp-filtros-form`, `mybp-filtros-botoes`, `mybp-combobox-wrap` |
| Obrigatórios | `mybp-campo-obrigatorio-legenda`, `text-danger` no `*` |
| Cards | `mybp-cards-lista`, `mybp-card`, `mybp-card-header-row`, `mybp-card-left`, `mybp-card-right`, `mybp-badge-id`, `mybp-card-titulo` |
| Conteúdo | `mybp-card-conteudo`, `mybp-card-secoes-row`, `mybp-card-destaque`, `mybp-card-destaque--primary`, `mybp-card-destaque--info` |
| Detalhes | `mybp-card-details-row`, `mybp-detail-item`, `mybp-detail-label`, `mybp-detail-value` |
| Ações | `mybp-btn-acoes-compact`, `mybp-dropdown-menu` |
| Métricas | `mybp-projeto-resumo`, barra de ocupação (ver Projetos) |

## Blade vs Vue SFC

| Aspecto | Blade (`index.blade.php`) | Vue SFC (`*.vue`) |
|---|---|---|
| FiltroListagem | Registrar em `app.js` | Import no `<script>` do componente |
| Template cards | `@{{ }}` no Blade | `{{ }}` no Vue |
| Referência | `vagas/index.blade.php` | `Areas.vue`, `CentroCusto.vue` |
