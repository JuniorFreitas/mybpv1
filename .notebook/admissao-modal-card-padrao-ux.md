# Admissão — modal e cards no padrão UX MyBP

- Arquivo: `resources/js/components/planejamento/movimentacao/SolicitacaoAdmissao.vue`
- Modal: `mybp-modal-form` + fieldsets Colaborador → Lotação → Solicitação → Detalhes → Aprovações
- Lotação no modal: **1º Lotação (CNPJ)** via `lista_ccs` → **2º CC filtrado** pela lotação (`formListaCentroCustoLotacao`)
  - Matriz/Filial inferidos do item do CC (`matriz` / `filial_id`); sem combo Matriz/Filial separado
  - `formLotacaoCnpj` + `resolverLotacaoDoForm()` no edit
- Data admissão: mapper em `d/m/Y`; listagem formata via `getRawOriginal` + `DataHora` (não usar `DATE_FORMAT` no Eloquent — quebra o cast `date:d/m/Y`)
- Front normaliza ISO/`Invalid date` no datepicker (igual demissão)
- Cards: `MybpCardCampo` + `MybpFluxoAprovacao` + `MybpStatusBadge`
  - Seções: Data/Tipo/Salário → Lotação/CC/Solicitante → Fluxo
- CSS scoped local mínimo (só hint de filtro); sem `fluxo-*` / card-corpo legado
