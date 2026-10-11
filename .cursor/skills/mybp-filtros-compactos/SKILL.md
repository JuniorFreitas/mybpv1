---
name: mybp-filtros-compactos
description: Aplica o padrão MyBP de filtros compactos em telas operacionais/relatórios (FiltroListagem + ComboboxAutoComplete densos, grade col-md-4, Mais filtros com animação, DateRangeFilter no blur, ações padronizadas). Use when the user asks for filtros compactos, padronizar filtros operacionais, mybp-filtros-compactos, PADRAO_UX_FILTROS_COMPACTOS, DateRangeFilter em filtro, ou copiar o padrão da carteira de treinamentos.
---

# MyBP — Filtros compactos (operacionais)

Padroniza filtros densos (muitos campos) em telas operacionais. **Não** substitui o padrão de cadastros (`mybp-front-cardlist`).

## Antes de editar

1. Ler `docs/PADRAO_UX_FILTROS_COMPACTOS.md`.
2. Abrir a referência: `resources/js/components/treinamentos-carteira-etiquetas/TreinamentosCarteiraEtiquetas.vue`.
3. Confirmar CSS: `resources/sass/_mybp-filtros-compactos.scss` (importado em `resources/sass/app.scss`).
4. Períodos: usar **sempre** `resources/js/components/DateRangeFilter.vue` (não trocar por `DatePicker` / `type="date"` solto).

## Diferença dos padrões

| | Cadastros (`mybp-front-cardlist`) | Compactos (esta skill) |
|---|---|---|
| Uso | CRUD / listagem leve | Carteira, etiquetas, operacional |
| Densidade | Inputs `form-control-sm` padrão | Tokens menores (`1.625rem`, fonte ~11px) |
| Layout | `col-lg-6` típico | Preferir `col-md-4` (3 por linha) |
| Avançados | Opcional | Shell animado + `mybp-filtros-avancados` |

## Workflow

### 1. Envolver com FiltroListagem

```vue
<FiltroListagem
  class="mt-2 mybp-filtros-compactos"
  :mostrar-limpar-filtros="totalFiltrosAtivos > 0"
  :desabilitado="carregando"
  @submit="atualizar"
  @limpar="limparFiltros"
>
```

### 2. Campos

- Cada campo: `form-group mybp-filtro-campo` + `mybp-label`
- Combobox: wrapper `mybp-combobox-wrap` + `ComboboxAutoComplete` (nunca `<select>` nos filtros)
- Períodos: `DateRangeFilter` (ver seção abaixo)
- Linha cheia só quando fizer sentido (ex.: multi-select de treinamentos)

### 3. DateRangeFilter (obrigatório para períodos)

Componente: `resources/js/components/DateRangeFilter.vue`.

```vue
<date-range-filter
  v-model:enabled="dados.campoPeriodo"
  v-model:start-date="dados.dataInicio"
  v-model:end-date="dados.dataFim"
  :disabled="carregando"
  :id-suffix="'periodo-' + hash"
  label="Período"
  wrapper-class="col-12 col-md-4 mybp-filtro-periodo"
  @change="onPeriodoChange"
/>
```

**Regras de comportamento (não alterar sem alinhamento):**

| Regra | Detalhe |
|---|---|
| Commit só no **blur** | Não escutar `@input` / `@change` do `<input type="date">` para emitir |
| Busca na tela | Ouvir **`@change`** do `DateRangeFilter` (emitido no blur válido ou no toggle do checkbox) |
| Calendário nativo | Manter `type="date"` editável (sem `readonly` que quebra o picker) |
| Valor inválido | No blur, se incompleto/inválido, restaurar a última data válida e **não** emitir |
| Formato do v-model | `startDate` / `endDate` em **ISO** (`YYYY-MM-DD`) |
| Backend BR | Se a API espera `DD/MM/YYYY até DD/MM/YYYY`, converter no handler (ex.: `formatarDataFiltro`) |

**Não fazer:** envolver `DatePicker` (daterangepicker) dentro do `DateRangeFilter`; digitar não pode disparar busca a cada tecla.

### 4. Primários vs avançados (toggle suave)

Não usar `v-show` / `v-if` seco no painel. Usar o shell animado do CSS compartilhado:

```vue
<div
  class="col-12 mybp-filtros-avancados-shell"
  :class="{ 'is-open': filtrosAvancadosAbertos }"
  :aria-hidden="filtrosAvancadosAbertos ? 'false' : 'true'"
>
  <div class="mybp-filtros-avancados-shell__inner">
    <div class="mybp-filtros-avancados">
      <div class="mybp-filtros-avancados__block">
        <p class="mybp-filtros-avancados__title">Outros filtros</p>
        <!-- campos secundários -->
      </div>
    </div>
  </div>
</div>
```

Botão:

```vue
<button
  type="button"
  class="btn btn-sm mybp-btn-mais-filtros"
  :class="filtrosAvancadosAbertos ? 'btn-primary is-open' : 'btn-outline-primary'"
  :aria-expanded="filtrosAvancadosAbertos ? 'true' : 'false'"
  @click="filtrosAvancadosAbertos = !filtrosAvancadosAbertos"
>
  <i class="fa" :class="filtrosAvancadosAbertos ? 'fa-chevron-up' : 'fa-sliders-h'"></i>
  {{ filtrosAvancadosAbertos ? 'Menos filtros' : 'Mais filtros' }}
  <span class="badge badge-light ml-1" v-if="totalFiltrosAtivosAvancados">
    {{ totalFiltrosAtivosAvancados }}
  </span>
</button>
```

- Primários: o que o usuário usa o tempo todo  
- Avançados: Padrão, Foto, Crachá, PCD, UF, Por página, etc.  
- Animação: `grid-template-rows` + opacity/translate em `_mybp-filtros-compactos.scss`

### 5. Ações (#acoes)

1. Buscar (`btn-success`, submit)
2. Mais filtros (`mybp-btn-mais-filtros`)
3. Ação principal da tela (ex.: Gerar carteira)
4. Exportar (`btn-success`) se houver
5. Limpar filtros via prop do `FiltroListagem`

**Não** colocar “Atualizar” se já existe “Buscar”.  
**Limpar seleção** fica na listagem, ao lado de “Selecionar todos”, com `v-if="selecionados.length"`.

### 6. Padrões de campo compostos (quando aplicável)

- **Colaborador/CPF:** um input; máscara CPF se só dígitos → `campoCPF`, senão `campoBusca`
- **Situação:** unifica admitido/demitido (`ativos` | `admitidos` | `nao_admitidos` | `demitidos`)

### 7. Botões fora do filtro

Usar `btn btn-sm btn-outline-primary mybp-btn-acao-compact` (mesmo tamanho das ações do filtro).

## Verificação

- [ ] Classes `mybp-filtros-compactos` + `mybp-filtro-campo` presentes
- [ ] Períodos com `DateRangeFilter` + `@change` (commit no blur)
- [ ] Painel avançado com `mybp-filtros-avancados-shell` (animação)
- [ ] Botão com `mybp-btn-mais-filtros`
- [ ] Grade equilibrada (sem “buracos” de `col-md-3` com 3 itens)
- [ ] Combobox input alinhado ao toggle (radius)
- [ ] Lista do combobox com fonte compacta
- [ ] Limpar filtros / Limpar seleção no lugar certo
- [ ] Assets rebuild se SCSS novo

## Não fazer

- Misturar este padrão com card-list de cadastro na mesma área de filtro sem necessidade
- Usar `form-control` grande (padrão Bootstrap) nestes filtros
- Manter botões grandes/antigos (`mr-1 mb-1` soltos) ao lado dos novos
- Disparar busca a cada tecla no período / usar `readonly` que remove o calendário nativo
- Abrir avançados com `v-show` sem o shell de transição
