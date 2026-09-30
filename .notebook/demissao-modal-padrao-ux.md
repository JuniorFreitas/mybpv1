# Demissão — modal no padrão UX MyBP

- Arquivo: `resources/js/components/planejamento/movimentacao/SolicitacaoDemissao.vue`
- Modal segue padrão CIH/Transferência: `fieldset` + `mybp-label` + legenda `mybp-campo-obrigatorio-legenda` + `ComboboxAutoComplete`
- Layout: Colaborador → CC | Lotação → Data | Tipo aviso → Gestor → Obs → Anexos
- Lotação (readonly): mesmo formato dos cards (`nome - CNPJ`) via `labelLotacaoAtual` (auth `cnpjs` / vínculo CC filial / `lista_ccs`)
- Modal: `demissao-modal-form mybp-filtros-compactos` (mesma altura/fonte dos filtros) + gap `--mybp-fc-gap: 0.75rem`
- DatePicker: esconder label vazia aninhada e zerar `corrigiDatepicker` (evita sumir com densificação)
- Edit payload: `DemissaoPrevistaEditPayloadMapper` formata `data_demissao`/`data_aprovacao*` em `d/m/Y` (Carbon no JSON virava ISO → "Invalid date" no DatePicker)
- DatePicker: apply+hide emitem v-model; limpa "hoje" fantasma no mount; `cadastrar()` sincroniza DOM→`form.data_demissao` antes de validar
- CC opcional: `centro_custo_id` nullable (migration); aviso no modal + `confirm` no cadastro; store/update normalizam ''→null
- Tipo de aviso e status de aprovação: combobox; validação explícita em `cadastrar()` / `aprovar*`
- Filtros da listagem continuam com `mybp-filtros-compactos`
