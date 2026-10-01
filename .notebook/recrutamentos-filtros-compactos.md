# Recrutamentos — filtros compactos + modal

## Escopo
- Tela: `/g/curriculos/recrutamentos`
- UI: `resources/views/g/curriculos/recrutamento/index.blade.php`
- JS: `resources/js/g/curriculos/recrutamento/app.js` → `public/js/g/recrutamento/app.js`
- Backend: `RecrutamentoController` filtro período

## Filtros
- `FiltroListagem` + `mybp-filtros-compactos`
- Primários: período (`DateRangeFilter`), Candidato/CPF unificado, Cargo, UF, Lido, PCD, Exibir
- Sem painel “Mais filtros” (todos visíveis)
- Combos `rec-filtro-*` (IDs estáticos)
- Export: `btn-outline-primary`; Buscar no lugar de Atualizar
- Paginacao: `:por-pagina="controle.dados.pages"`

## Modal
- Shell `mybp-modal-form mybp-filtros-compactos` + `fieldset.mybp-modal-secao`
- Grade `col-md-4` + `form-control-sm` / labels densos
- Sexo/Estado civil/Selecionado → `ComboboxAutoComplete` (`rec-modal-*`)
- Bools do feedback → `MybpBoolCombobox`
- Formação/Experiências/Qualificações em campos disabled densos
- Experiências: bloco `.mybp-modal-bloco-experiencia`; `principais_atv` e Observações em `textarea` (CSS sem height fixa)
- Validação submit: `exigirCombobox` + `:input:visible:enabled`

## Listagem (cards)
- `mybp-cards-lista` + badge por status (Selecionado / Não selecionado / Stand by / Lido / Não lido)
- Campos: CPF, UF, Vaga, PCD, Selecionado, Contato, Experiências, Qualificações, Interesse, Data, Lido
- Contagens via `withCount` (`experiencias_count` / `qualificacoes_count`) no `filtro()`
- Dropdown: Recrutar, Gerar PDF, Remover
- Cards topo: KPIs densos `.mybp-kpi-row` (total / 90d / selecionados 90d + taxa %)

## Período
- Front: `dataInicio`/`dataFim` ISO + sync `periodo` BR (`dd/mm/yyyy até dd/mm/yyyy`)
- Backend: preferir ISO; fallback `periodo` BR
