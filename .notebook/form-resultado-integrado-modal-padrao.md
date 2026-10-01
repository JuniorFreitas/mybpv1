# FormResultadoIntegrado — padrão modal

## Escopo
- `resources/js/components/entrevistas/FormResultadoIntegrado.vue`
- Seções `mybp-modal-secao` + labels densos + `form-control-sm` / datepicker `formsm`
- Grade estável `col-md-4`: campos principais numa linha; data/notificações em linha seguinte
- Notificações: faixa `mybp-ri-notificacoes` (switch Bootstrap), sem competir com inputs
- Exceção/Autorizado/Responsável sempre na mesma linha (Autorizado desabilitado se não houver exceção)
- Sem fieldset externo “RESULTADO INTEGRADO” (evita seção duplicada)
- Bools (docs/exame/trein/exceção) + PCMSO + Empresa Exame → `ComboboxAutoComplete`
- IDs com `hash` (SFC; evita colisão avulsa + admitir na mesma página)
- `validarCampos()`: docs, **exame**, trein, exceção, responsável; PCMSO/empresa só se `exigePcmsoEmpresa` (cliente ≠ 78862)
- Autorizado por: obrigatório **somente** se `excessao === true`; sem `onblur` quando Não; submit usa `:input:visible:enabled`

## Refs submit
- Admissão: `formResultadoIntegradoModal` / `formResultadoIntegradoAvulsa`
- Entrevistas RI: `formResultadoIntegrado` em cadastrar/alterar
