# Entrega 28–29/09/2026 — CIH, Movimentação, Cloud e Layout

Documento consolidado do que foi implementado e corrigido entre **28/09/2026** e **29/09/2026**.

---

## Resumo executivo

| Área | O que mudou |
|------|-------------|
| **CIH** | Filtros compactos, visibilidade (`ver_todas`), autocomplete por Lotação/CNPJ, payload enxuto (DBA), coluna Lotação no Excel, export por `empresa_id` + filtros (não “só meus”) |
| **Planejamento / Movimentação** | Filtros estilo Demissão nas 7 abas; `EditPayloadMapper`; listagem enxuta; Lotação nos cards; filtro Lotação sem pré-seleção |
| **Cloud / Upload** | Novos MIME types; UX de upload; multi-mover / drag para pasta |
| **Layout** | Menu vertical recolhível (preferência no `localStorage`) |

---

## Cronologia (commits)

| Data | Commit | Escopo |
|------|--------|--------|
| 28/09 18:10 | `655fdf7a` | CIH filtros compactos + `CihAcessoService` + habilidade `admissao_cih_ver_todas` |
| 28/09 19:33 | `bcb74741` | Autocomplete colaborador por CNPJ/CC + Lotação na tabela |
| 28/09 19:46 | `0df9a3d1` | Payload enxuto listagem/edit + `CihColaboradorPayloadMapper` |
| 28/09 20:03 | `7771ffbb` | Excel/CSV com coluna Lotação (`CihLotacaoResolver`) |
| 28/09 20:50 | `83bf91b0` | Export via `CihQueryBuilder::forExport` (query enxuta) |
| 28/09 21:46 | `9baa7e2a` | Menu lateral colapsável |
| 28/09 21:47 | `544cde07` | Merge PR #28 `feature/cih-melhoria` |
| 28/09 23:11 | `82f05866` | Upload/Cloud: MIME novos + lista de progresso |
| 28/09 23:17 | `fa4e353f` | Cloud: mover vários itens / drag para pasta |
| 29/09 00:21 | `4ffd0106` | Movimentação: mappers, filtros, listagem enxuta, Lotação |
| 29/09 07:42 | `d0c4299c` | Lotação filial + filtro CNPJ sem iniciar selecionado |
| 29/09 08:07 | `85b8da3d` | Fix export CIH: escopo `empresa_id` + filtros (não `vinculados`) |

---

## 1. CIH (Admissão / Apontamento)

### 1.1 UX e filtros

- Tela alinhada ao padrão **filtros compactos** (`FiltroListagem`, grade densa, `DateRangeFilter`).
- Busca unificada (nome / CÓD CIH).
- Combos de status, tipo (tag), Lotação (CNPJ), centro de custo, área, gestor.
- Label **Lotação** no lugar de CNPJ onde há filial.

**Arquivos:** `resources/js/components/admissao/apontamento/CIH.vue`, `resources/sass/_mybp-filtros-compactos.scss`

### 1.2 Visibilidade vs aprovação

| Habilidade | Efeito |
|------------|--------|
| `admissao_cih_ver_todas` | Amplia **listagem** (não amplia aprovação) |
| `admissao_cih_privilegio_adm` | Vê todas **e** override de aprovação gestor |
| Escopo padrão | `Cih::vinculados()` (gestor / lançamento / aprovação) |
| Papel legado Montisol (`grupo_id` 113) | CCs da matriz Montisol |

**Arquivos:** `app/Services/Cih/CihAcessoService.php`, seeder de habilidades, testes unitários.

**Nota de domínio:** ver todas ≠ poder aprovar todas. Detalhe em `.notebook/cih-visibilidade-aprovacao.md`.

### 1.3 Autocomplete de colaborador

- Filtra por CNPJ/Lotação e, se houver, centro de custo do modal.
- Tabela do autocomplete exibe CC e Lotação.

**Arquivos:** `AutoCompletesController`, `CihLotacaoResolver`, `.notebook/cih-autocomplete-cnpj.md`

### 1.4 Performance (DBA) — listagem / edit / autocomplete

- `CihQueryBuilder` com colunas e `with()` mínimos para cards.
- `CihColaboradorPayloadMapper` para payload flat (evita appends pesados de `FeedbackCurriculo`).

**Arquivos:** `.notebook/cih-dba-payload-enxuto.md`

### 1.5 Exportação Excel/CSV

- Coluna **Lotação** quando a empresa tem filial.
- Job usa `CihQueryBuilder::forExport` (selects/eager loads enxutos).
- **Correção 29/09:** export **não** usa escopo `vinculados`. Voltou a regra histórica:
  - `empresa_id` do usuário
  - + filtros da tela (período, status, tag, área, CNPJ/CC, gestor, busca)
- Listagem continua com regras de visibilidade; export não mistura os dois escopos.

**Arquivos:** `JobExportaCihCsvFinal`, `CihExportFormatter`, `CihQueryBuilder`, `.notebook/cih-export-query-enxuta.md`

---

## 2. Planejamento — Movimentação (7 abas)

Abas: Demissão, Férias, Admissão, Valor Extra, Mudança de Cargo, Intermitente Fixo, Transferência.

### 2.1 Filtros padronizados

- Padrão da Demissão aplicado nas demais abas: busca unificada (nome/CPF), status, Lotação (CNPJ), centro de custo, período, ordenação, query params.
- Filtros no backend via `*FilterApplier` (incluindo CNPJ/CC com matriz vs filial).

### 2.2 Edit enxuto (`EditPayloadMapper`)

Cada tipo de solicitação ganhou mapper dedicado para o payload de **edição/modal**, com colunas explícitas (menos overfetch).

Exemplos:

- `app/Services/DemissaoPrevista/DemissaoPrevistaEditPayloadMapper.php`
- `app/Services/FeriasPrevista/FeriasPrevistaEditPayloadMapper.php`
- … (Admissão, Valor Extra, Muda Cargo, Intermitente, Transferência)

### 2.3 Listagem (`atualizar`) enxuta + Lotação

- `atualizar` passa a selecionar só o necessário para os **cards**.
- Campo **Lotação** nos cards quando `temFilial` (padrão CIH).
- `LotacaoLabelResolver` centraliza rótulo (matriz/filial/CC), com batch de filiais e soft delete em Query Builder.

**Arquivo-chave:** `app/Services/Planejamento/Movimentacao/LotacaoLabelResolver.php`

### 2.4 Regras de Lotação / filtro CNPJ

| Regra | Comportamento |
|-------|----------------|
| Empresa com filial | Lotação aparece no card (`v-if="temFilial"`) |
| Registro com filial vinculada | Rótulo da filial prevalece (não cai na matriz) |
| Filtro Lotação (CNPJ) | **Não** inicia pré-selecionado; placeholder “Todas as lotações” |
| URL | `campoCnpj` **não** é restaurado da query string ao abrir a aba |
| Transferência | Bug do `temFilial` no Composition API corrigido (`authconfiguracao.temFilial`); Lotação do destino via `fromCentroCustoId` |

### 2.5 Frontend

- `resources/js/components/planejamento/movimentacao/Solicitacao*.vue` (7 arquivos).
- Testes unitários dos mappers e do `LotacaoLabelResolver`.

---

## 3. Cloud e Upload

### 3.1 MIME e UX de upload (`82f05866`)

- Novos tipos em `Arquivo` (WEBP, CSV, Markdown, MP3, MP4, EPS, AI, PSD, etc.).
- `Upload.vue`: lista de arquivos com progresso e ações.
- `Cloud.vue`: feedback visual em drag-and-drop.

### 3.2 Mover vários / drag para pasta (`fa4e353f`)

- Endpoint `moverVarios` em `ItensCloudController`.
- Seleção múltipla + arrastar para pasta no `Cloud.vue`.
- Feedback em `PastaCloud.vue`.

**Nota:** `.notebook/cloud-multi-move-drag.md`

---

## 4. Layout — menu colapsável (`9baa7e2a`)

- Toggle do menu vertical; preferência em `localStorage`.
- Ajustes em `BarraTop.vue`, `menu.blade.php`, `sistema.blade.php` e SCSS do menu.

---

## 5. Testes

Principais coberturas adicionadas/ajustadas:

- `tests/Unit/Services/Cih/CihAcessoServiceTest.php`
- `tests/Unit/Services/Cih/CihQueryBuilderVisibilidadeTest.php` (inclui export ≠ vinculados)
- `tests/Unit/Services/Cih/CihColaboradorPayloadMapperTest.php`
- `tests/Unit/Services/Cih/CihExportFormatterLotacaoTest.php`
- `tests/Unit/Services/Planejamento/Movimentacao/LotacaoLabelResolverTest.php`
- `tests/Unit/Services/{Admissoes,Demissao,Ferias,...}Prevista/*EditPayloadMapperTest.php`

PHPUnit em SQLite `:memory:` (padrão do projeto).

---

## 6. Referências rápidas (.notebook)

| Nota | Assunto |
|------|---------|
| `cih-visibilidade-aprovacao.md` | ver_todas ≠ aprovar |
| `cih-filtro-busca-id.md` | Filtro compacto CIH |
| `cih-autocomplete-cnpj.md` | Autocomplete por Lotação/CC |
| `cih-dba-payload-enxuto.md` | Payload listagem/edit |
| `cih-export-query-enxuta.md` | Export enxuto + escopo empresa |
| `cloud-multi-move-drag.md` | Multi-move no Cloud |
| `filtro-compacto-operacional.md` | Padrão UX filtros densos |

---

## 7. Checklist de regressão sugerido

### CIH

- [ ] Listagem com e sem `admissao_cih_ver_todas`
- [ ] Aprovar como gestor só no escopo correto
- [ ] Filtros + período; export Excel traz o filtrado da **empresa** (não só lançamentos do usuário logado)
- [ ] Empresa com filial: coluna Lotação no Excel e label nos cards/autocomplete
- [ ] Autocomplete colaborador restringe por Lotação/CC do modal

### Movimentação

- [ ] Cada aba: filtros, limpar filtros (Lotação volta a “Todas as lotações”)
- [ ] Cards com Lotação só se `temFilial`
- [ ] Transferência: Lotação visível no card quando há filial
- [ ] Editar/visualizar modal carrega dados corretos (mappers)

### Cloud

- [ ] Upload dos novos MIME
- [ ] Selecionar vários e mover para pasta (drag ou ação)

### Layout

- [ ] Recolher/expandir menu e persistência após F5

---

*Documento gerado a partir dos commits `655fdf7a` … `85b8da3d` e das correções de Lotação/filtro/export alinhadas a esta entrega.*

---

## 8. Continuação — Autorização (pós-entrega, mesma janela)

Trabalho posterior de **habilidades / Cloud ACL** (registry, enforcement, rename, Policies) foi pausado em **29/09/2026**.

**Checkpoint para retomar:** [`.notebook/checkpoint-autorizacao-2026-09-29.md`](../.notebook/checkpoint-autorizacao-2026-09-29.md)

Docs canônicos:

- [`docs/AUTORIZACAO_HABILIDADES.md`](AUTORIZACAO_HABILIDADES.md)
- [`docs/AUTORIZACAO_CLOUD.md`](AUTORIZACAO_CLOUD.md)
