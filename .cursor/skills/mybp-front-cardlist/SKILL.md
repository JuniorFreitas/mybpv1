---
name: mybp-front-cardlist
description: Migra telas de cadastro MyBP do padrão tabela/fieldset para FiltroListagem + ComboboxAutoComplete nos filtros + cards mybp-* + campos obrigatórios com asterisco vermelho. Use quando o usuário pedir padrão de listagem, card list, filtros mybp, combobox nos filtros, migrar cadastro, aplicar PADRAO_UX_LISTAGEM, mybp-front-cardlist ou marcar campos obrigatórios em modais de cadastro.
---

# MyBP Front — Card List & Filtros

Skill para migrar telas de **cadastro** Laravel + Vue 3 para o padrão visual unificado: `FiltroListagem`, cards `mybp-*`, dropdown de ações e modal com campos obrigatórios (`*` vermelho).

## Antes de editar

1. Ler `docs/PADRAO_UX_LISTAGEM_CADASTROS.md`.
2. Localizar a tela:
   - **Vue SFC**: `resources/js/components/cadastros/<modulo>/*.vue` + `resources/js/g/cadastros/<modulo>/app.js`
   - **Blade inline**: `resources/views/g/cadastros/<modulo>/index.blade.php` + `resources/js/g/cadastros/<modulo>/app.js`
3. Identificar controller (`*Controller.php`) e método `atualizar()`.
4. Abrir **1 tela já migrada** como referência (ver [examples.md](examples.md)).

## Escopo da migração

| Substituir | Por |
|---|---|
| `<fieldset><legend>Filtro</legend>` | `<filtro-listagem>` com slots `#filtros` / `#acoes` |
| `<select>` nativo nos filtros | `ComboboxAutoComplete` dentro de `mybp-combobox-wrap` |
| `<table class="tabela">` | `<div class="mybp-cards-lista">` + `.mybp-card` |
| Botão editar solto na linha | Dropdown `mybp-btn-acoes-compact` + item Editar |
| Labels genéricos no modal | `mybp-label` + `<span class="text-danger">*</span>` nos obrigatórios |

**Não alterar** regras de negócio, rotas, permissões ou validação backend — apenas espelhar o que o backend já exige.

Telas que mantêm **lista (cards) e tabela** juntas seguem a skill `.cursor/skills/mybp-visualizacao-listagem/SKILL.md`. A tabela é a visualização inicial e a preferência é única no sistema.

## Workflow (seguir nesta ordem)

### 1. Mapear a tela

Preencher mentalmente (ou no chat):

```
Módulo: ___
Arquivos: view/vue, app.js, controller
Filtros atuais: busca, status, outros?
Colunas da tabela → blocos do card: ___
Campos obrigatórios (store/update): ___
Rota ativa-desativa: ___
```

### 2. Filtros — `FiltroListagem` + `ComboboxAutoComplete`

**Regra:** todo filtro que não seja busca livre (`input text`) usa **`ComboboxAutoComplete`** — status, enums, CNPJ, cargo, cidade, etc. **Não usar `<select>` nativo** nos filtros.

**Imports** (Vue SFC):

```javascript
import FiltroListagem from '../../ui/FiltroListagem.vue'
import ComboboxAutoComplete from '../../ComboboxAutoComplete.vue'
```

**Registro** (Blade + `app.js`):

```javascript
import FiltroListagem from '../../../components/ui/FiltroListagem'
import ComboboxAutoComplete from '../../../components/ComboboxAutoComplete'

components: { FiltroListagem, ComboboxAutoComplete }
```

**Template mínimo** (Buscar + Status):

- Buscar: `col-12 col-lg-6`, `<input>` texto, placeholder `"Buscar por nome ou ID"`
- Status: `col-12 col-lg-6`, **`combobox-auto-complete`** (não `<select>`)
- Labels: `class="mybp-label"`
- Wrapper do combobox: `mybp-combobox-wrap`
- Botões na slot `#acoes`: **Atualizar** (`btn-success`) + **Cadastrar** (`btn-secondary`)
- **Limpar filtros:** `:mostrar-limpar-filtros="temFiltrosAtivos"` + `@limpar="limparFiltros"` (botão no `FiltroListagem`, visível só com filtro preenchido)
- `@submit="onSubmitFiltro"` → chama `atualizar()` (também dispara ao pressionar **Enter** no campo Buscar)
- Botão **Atualizar** preferencialmente `type="submit"` (Cadastrar permanece `type="button"`)

**Opções do combobox** — formato `{ value, label, meta?, raw? }`:

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

Primeira opção com `value: ''` = **todos** (sem filtro). Usar `placeholder-blur="Todos os status"` no combobox.

**Handlers obrigatórios** (múltiplos comboboxes na mesma tela):

```javascript
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
  }
}
```

No template de cada combobox:

```vue
@opening="fecharOutrosComboboxes('filtro-status')"
@select="onSelectFiltro"
```

Se houver dropdown de card na mesma tela, fechar comboboxes no `onClickOutside` (ignorar cliques em `.mybp-combobox-wrap`).

**Props padrão do combobox de filtro:**

| Prop | Valor típico |
|---|---|
| `instance-id` | único por filtro (`filtro-status`) |
| `input-id` | igual ao `for` do label |
| `v-model` | `controle.dados.campoXxx` |
| `:options` | computed com opção vazia primeiro |
| `placeholder-blur` | rótulo do estado "todos" |
| `empty-message` | `"Nenhuma opção encontrada."` |
| `:max-results` | `10` (enum) ou `50` (listas longas) |
| `:disabled` | `controle.carregando` |

**Grid sugerida:**

| Cenário | Buscar | Outros filtros |
|---|---|---|
| 2 filtros | `col-lg-6` | `col-lg-6` combobox |
| 3 filtros | `col-lg-4` | demais `col-lg-4` combobox |
| + CNPJ (filial) | `col-lg-4` | Status `col-lg-3` + CNPJ `col-lg-5` |

Templates completos: [reference.md](reference.md#combobox-nos-filtros).

### 2.2 Limpar filtros

Utilitário (`listagemQueryParams.js`):

```javascript
import { temFiltrosPreenchidos, limparFiltrosListagem } from '../../../utils/listagemQueryParams'

const CAMPOS_FILTRO_URL = ['campoBusca', 'campoStatus'] // sem pages/page

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

Template:

```vue
<FiltroListagem
  @submit="onSubmitFiltro"
  :mostrar-limpar-filtros="temFiltrosAtivos"
  :desabilitado="controle.carregando"
  @limpar="limparFiltros"
>
```

O botão **Limpar filtros** (`btn-outline-secondary`) fica no `FiltroListagem` e só aparece quando `temFiltrosAtivos === true`.

**Combobox dispara busca automaticamente:** `@select="onSelectFiltro"` deve chamar `atualizar()` imediatamente — o usuário **não** precisa clicar em Atualizar após mudar um combobox.

### 2.1 Query params — histórico na URL

Mesmo padrão de **planejamento/movimentacao** (`SolicitacaoDemissao.vue` + `movimentacao/app.js`): filtros refletidos na URL com `history.replaceState`.

**Utilitário:** `resources/js/utils/listagemQueryParams.js`

```javascript
import {
  lerFiltrosDaUrl,
  sincronizarFiltrosNaUrl,
  criarWatchQueryParams
} from '../../../utils/listagemQueryParams'

const CAMPOS_FILTRO_URL = ['campoBusca', 'campoStatus'] // todos os controle.dados de filtro
```

**Mounted** (ordem obrigatória):

```javascript
mounted() {
  this.urlParamGetFiltros()
  this.formDefault = _.cloneDeep(this.form)
  this.$nextTick(() => this.atualizar())
},
methods: {
  urlParamGetFiltros() {
    lerFiltrosDaUrl(this.controle.dados, CAMPOS_FILTRO_URL)
  },
  syncUrlFiltros() {
    sincronizarFiltrosNaUrl(this.controle.dados, CAMPOS_FILTRO_URL)
  }
}
```

**Watch** (debounce 400ms — igual movimentação):

```javascript
watch: {
  'controle.dados': criarWatchQueryParams(CAMPOS_FILTRO_URL)
}
```

**Comportamento:**

| Ação | Resulte |
|---|---|
| Selecionar combobox | `onSelectFiltro()` → `atualizar()` imediato + URL atualizada via watch |
| **Enter no Buscar** / submit do form | `@submit="onSubmitFiltro"` → `atualizar()` (via `FiltroListagem`) |
| Digitar no Buscar | URL atualiza após debounce (watch) |
| Abrir URL com `?campoBusca=x&campoStatus=true` | `urlParamGetFiltros()` restaura filtros e busca |
| Valor vazio | omitido da URL (significa "todos") |

**Nomes dos params:** usar exatamente as chaves de `controle.dados` (`campoBusca`, `campoStatus`, etc.).

**Paginação na URL** (igual requisição de vagas / movimentação):

| Param | Significado | Quando incluir |
|---|---|---|
| `page` | Página atual | só se `> 1` |
| `pages` | Itens por página (`controle.dados.pages`) | só se diferente do padrão da tela |

```javascript
import {
  lerPaginacaoDaUrl,
  montarExtrasPaginacao,
  aplicarPaginaInicialListagem,
  buscarListagem
} from '../../../utils/listagemQueryParams'

const PAGES_DEFAULT = 20 // ou 100 conforme a tela

// mounted — após ler filtros
const paginaInicial = lerPaginacaoDaUrl(this.controle.dados, { pagesDefault: PAGES_DEFAULT })
this.$nextTick(() => {
  aplicarPaginaInicialListagem(this, paginaInicial)
  buscarListagem(this, { resetPagina: false })
})

// controle.dados
pages: PAGES_DEFAULT

// controle-paginacao
:por-pagina="controle.dados.pages"

// watch — passar pagesDefault para incluir paginação no sync
'controle.dados': criarWatchQueryParams(CAMPOS_FILTRO_URL, { pagesDefault: PAGES_DEFAULT })

// sync manual / após paginar
syncUrlFiltros() {
  sincronizarFiltrosNaUrl(this.controle.dados, CAMPOS_FILTRO_URL,
    montarExtrasPaginacao(this, { pagesDefault: PAGES_DEFAULT }))
}

// carregou — atualiza page na URL após avançar/voltar página
carregou(dados) {
  ...
  this.$nextTick(() => this.syncUrlFiltros())
}

// atualizar filtros — reseta para página 1
atualizar() {
  buscarListagem(this, { resetPagina: true })
}
```

Exemplo: `/g/cadastro/areas?campoStatus=true&page=3`

### 3. Listagem — cards

Estrutura obrigatória por item:

```
mybp-cards-lista
  └── mybp-card
        ├── mybp-card-header-row (id, título, bt-ativo, dropdown)
        └── mybp-card-conteudo
              └── mybp-card-secoes-row OU mybp-card-details-row
```

**Header**

- `#id` em `mybp-badge-id`
- Título principal em `mybp-card-titulo`
- `bt-ativo` com `@atualizou="atualizar()"`
- Dropdown com prefixo de chave único (`area:`, `vaga:`, `cc:`)

**Conteúdo do card** — escolher layout:

| Tipo | Quando usar | Classes |
|---|---|---|
| Destaques | 2–4 blocos informativos (gestor, CBO, métricas) | `mybp-card-destaque`, `--primary`, `--info` |
| Detalhes | Pares label/valor compactos | `mybp-detail-item`, `mybp-detail-label`, `mybp-detail-value` |
| Resumo numérico | KPIs (capacidade, ocupação) | `mybp-projeto-resumo`, barra de ocupação |

Valores ausentes: **`Não informado`** (padrão CIH).

**Dropdown (Options API)**

```javascript
data() { return { dropdownAbertoKey: null } },
methods: {
  onSubmitFiltro() { this.atualizar() },
  toggleDropdown(id) {
    const key = `modulo:${id}`
    this.dropdownAbertoKey = this.dropdownAbertoKey === key ? null : key
  },
  isDropdownOpen(id) { return this.dropdownAbertoKey === `modulo:${id}` },
  fecharDropdown() { this.dropdownAbertoKey = null },
  abrirEdicao(id) { this.fecharDropdown(); this.alterar(id) }
}
```

### 4. Modal — campos obrigatórios

1. Ler validação em `store()` / `update()` do controller.
2. No topo do formulário:

```html
<p class="mybp-campo-obrigatorio-legenda mb-3">
  Campos com <span class="text-danger">*</span> são obrigatórios.
</p>
```

3. Em cada campo **required** no backend:

```html
<label class="mybp-label" for="modulo-form-campo">
  Nome <span class="text-danger">*</span>
</label>
```

4. Campos opcionais: `mybp-label` **sem** asterisco.
5. Manter validações JS existentes (`valida_campo_vazio`, `onblur`, etc.) nos obrigatórios.

### 5. Backend — `atualizar()`

Padronizar sem quebrar contrato de paginação:

```php
// Busca por nome + ID numérico
if ($request->filled('campoBusca')) {
    $termo = trim((string) $request->campoBusca);
    $query->where(function ($q) use ($termo) {
        $q->where('label', 'like', '%' . $termo . '%');
        if (is_numeric($termo)) {
            $q->orWhere('id', (int) $termo);
        }
    });
}

// Status booleano robusto
if ($request->filled('campoStatus')) {
    $status = filter_var($request->campoStatus, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    if ($status !== null) {
        $query->whereAtivo($status);
    }
}
```

Enriquecer itens para o card (evitar N+1):

- `with()` para relações necessárias
- `map()` expondo campos planos (`gestor_nome`, `centro_custo_label`, contadores)
- Batch query para dados em tabelas pivot quando necessário

Resposta JSON **inalterada** na estrutura:

```php
return response()->json([
    'atual' => $resultado->currentPage(),
    'ultima' => $resultado->lastPage(),
    'total' => $resultado->total(),
    'dados' => [
        'items' => $items,
        'empresa_id' => auth()->user()->empresa_id, // se já existia
    ],
], 200);
```

### 6. Estados da listagem

Manter os três estados:

```html
<preload v-if="controle.carregando" />
<div class="alert alert-warning" v-show="!controle.carregando && lista.length === 0">...</div>
<div class="mybp-cards-lista" v-show="!controle.carregando && lista.length > 0">...</div>
<controle-paginacao class="d-flex justify-content-center mt-3" ... />
```

### 7. Build e verificação

```bash
npm run dev
```

Checklist final:

- [ ] `FiltroListagem` e `ComboboxAutoComplete` importados e registrados
- [ ] Filtros enum/status/CNPJ usam combobox (sem `<select>` nativo)
- [ ] Opções com `{ value: '', label: 'Todos...' }` como primeira entrada
- [ ] `@submit="onSubmitFiltro"` → `atualizar()` (Enter no Buscar funciona via `FiltroListagem`)
- [ ] `:mostrar-limpar-filtros="temFiltrosAtivos"` + `@limpar="limparFiltros"`
- [ ] `@select="onSelectFiltro"` em todo combobox (busca imediata)
- [ ] `CAMPOS_FILTRO_URL` + `urlParamGetFiltros` + watch `criarWatchQueryParams`
- [ ] URL reflete filtros e paginação (`?campoBusca=...&page=2`)
- [ ] Cards usam classes `mybp-*` (sem CSS duplicado no componente)
- [ ] Asterisco vermelho só nos campos validados como `required`
- [ ] Legenda de obrigatórios no modal
- [ ] `atualizar()` com busca por ID + enrich de payload
- [ ] Assets compilam sem erro
- [ ] Dropdown e `bt-ativo` funcionam após editar/desativar

## O que não fazer

- Não usar `<select>` nativo nos filtros — usar `ComboboxAutoComplete`
- Não criar estilos locais que dupliquem `_mybp-listagem-ui.scss`
- Não marcar campo como obrigatório no front se o backend não valida
- Não remover paginação ou alterar URL de `atualizar` sem necessidade
- Não migrar lógica pesada para o controller — só enrich da listagem
- Não usar tabela densa após migração (exceto se o usuário pedir explicitamente)

## Recursos

- Doc oficial: `docs/PADRAO_UX_LISTAGEM_CADASTROS.md`
- Estilos: `resources/sass/_mybp-listagem-ui.scss`
- Query params: `resources/js/utils/listagemQueryParams.js`
- Templates completos: [reference.md](reference.md)
- Telas referência: [examples.md](examples.md)
