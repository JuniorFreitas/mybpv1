# Padrão UX — Filtros compactos (operacionais / relatórios)

Padrão visual e estrutural para filtros densos em telas operacionais (carteira, etiquetas, listagens pesadas), distinto do padrão de cadastros (`PADRAO_UX_LISTAGEM_CADASTROS.md`).

**Referência viva:** `resources/js/components/treinamentos-carteira-etiquetas/TreinamentosCarteiraEtiquetas.vue`  
**CSS compartilhado:** `resources/sass/_mybp-filtros-compactos.scss`  
**Skill:** `.cursor/skills/mybp-filtros-compactos/SKILL.md`

---

## Quando usar

| Cenário | Padrão |
|---|---|
| Cadastro CRUD (poucos filtros + cards) | `PADRAO_UX_LISTAGEM_CADASTROS` + skill `mybp-front-cardlist` |
| Operacional / carteira / relatório com muitos filtros | **Este padrão** + skill `mybp-filtros-compactos` |

---

## Estrutura base

```vue
<FiltroListagem
  class="mt-2 mybp-filtros-compactos"
  :mostrar-limpar-filtros="totalFiltrosAtivos > 0"
  :desabilitado="carregando"
  @submit="atualizar"
  @limpar="limparFiltros"
>
  <template #filtros>
    <!-- linhas com col-12 col-md-4 (3 por linha) -->
    <div class="form-group mybp-filtro-campo">...</div>
  </template>
  <template #acoes>
    <!-- botões padronizados -->
  </template>
</FiltroListagem>
```

### Classes obrigatórias

| Classe | Onde |
|---|---|
| `mybp-filtros-compactos` | No `FiltroListagem` |
| `mybp-filtro-campo` | Wrapper de cada campo (form-group) |
| `mybp-filtro-periodo` | `wrapper-class` do `DateRangeFilter` |
| `mybp-filtros-avancados-shell` / `__inner` | Wrapper animado do painel “Mais filtros” (`is-open`) |
| `mybp-filtros-avancados` | Painel interno de “Mais filtros” |
| `mybp-filtros-avancados__block` / `__title` | Blocos internos do painel |
| `mybp-btn-mais-filtros` | Botão Mais/Menos filtros (`is-open` quando aberto) |
| `mybp-btn-acao-compact` | Botões de ação fora do slot (ex.: Filtrar treinamentos) |
| `mybp-label` | Labels |
| `mybp-combobox-wrap` | Em volta do `ComboboxAutoComplete` |

---

## DateRangeFilter (períodos)

Sempre usar `resources/js/components/DateRangeFilter.vue`.

- **Commit / busca:** só no **blur** (ou toggle do checkbox). A tela escuta `@change`.
- **Não** emitir a cada tecla (`@input` do date nativo).
- **Não** usar `readonly` (quebra o calendário nativo).
- **v-model** `startDate`/`endDate` em ISO (`YYYY-MM-DD`).
- Se o backend espera `DD/MM/YYYY até DD/MM/YYYY`, converter no handler da tela.

```vue
<date-range-filter
  v-model:enabled="dados.campoPeriodo"
  v-model:start-date="dados.dataInicio"
  v-model:end-date="dados.dataFim"
  wrapper-class="col-12 col-md-4 mybp-filtro-periodo"
  @change="onPeriodoChange"
/>
```

---

## Mais filtros (animação)

Não usar `v-show` seco. Estrutura:

```vue
<div
  class="col-12 mybp-filtros-avancados-shell"
  :class="{ 'is-open': filtrosAvancadosAbertos }"
>
  <div class="mybp-filtros-avancados-shell__inner">
    <div class="mybp-filtros-avancados">...</div>
  </div>
</div>
```

Botão: `mybp-btn-mais-filtros` + classes `btn-outline-primary` / `btn-primary is-open`.

---
## Layout (grade)

Preferir **3 colunas** por linha: `col-12 col-md-4`.

Exemplo de ordem (carteira):

1. Período vencimento · Período treinado · Treinados  
2. Colaborador/CPF · CNPJ · Centro de custo  
3. Vaga · Cargo · Situação  
4. Treinamentos específicos (`col-12`)  
5. Painel avançado (só com “Mais filtros”)

Campos secundários (Padrão, Foto, Crachá, PCD, UF, Por página) ficam em **Mais filtros**.

---

## Campo unificado Colaborador / CPF

Um único input:

- Digitação com letras → `campoBusca` (nome)
- Digitação só números / máscara CPF → `campoCPF` + máscara `000.000.000-00`
- Hint visual “CPF” no label quando em modo CPF

---

## Situação (Admitidos + Demitidos)

Um combobox só, mapeando a regra de backend:

| UI | `campoDemitido` | `campoAdmitido` |
|---|---|---|
| Ativos (padrão) | `false` | `''` |
| Admitidos | `false` | `'S'` |
| Não admitidos | `false` | `'N'` |
| Demitidos | `true` | `''` |

---

## Ações (#acoes)

Ordem sugerida:

1. **Buscar** — `btn-sm btn-success` (`type="submit"`)
2. **Mais / Menos filtros** — `mybp-btn-mais-filtros` + `btn-outline-primary` / `btn-primary is-open` + badge de filtros avançados ativos
3. **Gerar carteira** (ou ação principal da tela) — `btn-primary`
4. **Exportar Excel** — `btn-success`
5. **Limpar filtros** — via `FiltroListagem` (`mostrar-limpar-filtros`)

Regras:

- Não duplicar **Atualizar** se já existe **Buscar**
- **Limpar seleção** fica junto de **Selecionar todos** (lista), não nas ações do filtro; só aparece com seleção
- Tamanho: padding `0.2rem 0.55rem`, fonte `0.8125rem`, `border-radius: 8px`, `font-weight: 600`

---

## Densidade visual

Tokens (CSS vars em `.mybp-filtros-compactos`):

- Altura controle: `1.625rem`
- Fonte input: `0.6875rem`
- Fonte label: `0.7rem`
- Gap entre campos: `0.45rem`
- Combobox aberto (lista): fonte `0.75rem` em `ComboboxAutoComplete.vue`

Combobox input + botão toggle: radius esquerdo no input, direito no botão (`border-left: 0` no toggle).

---

## Checklist ao aplicar em outra tela

- [ ] `FiltroListagem` + `class="mybp-filtros-compactos"`
- [ ] Campos com `mybp-filtro-campo` / períodos com `DateRangeFilter` + `mybp-filtro-periodo` + `@change`
- [ ] Painel avançado com `mybp-filtros-avancados-shell` (não `v-show` seco)
- [ ] Sem `<select>` nativo nos filtros — usar `ComboboxAutoComplete`
- [ ] Grade `col-md-4` (ou equivalente equilibrado)
- [ ] Primários vs avançados definidos
- [ ] Ações alinhadas ao padrão de cores/tamanho
- [ ] `mostrar-limpar-filtros` + `limparFiltros`
- [ ] Rebuild assets se alterou SCSS (`npm run dev` / `prod`)
