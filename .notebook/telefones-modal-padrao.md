# Telefones — padrão modal

## Escopo
- `resources/js/components/Telefones.vue`: grade densa `mybp-filtro-campo` + `form-control-sm`
- Tipo → `ComboboxAutoComplete` (whatsapp/celular/residencial/comercial)
- Sem fieldset próprio (pai já usa `Contato` / `Telefones`)
- IDs com `hash` (corrige colisão do switch Principal que usava só `index`)
- `validarCampos()`: `qnt_min`, tipo e número
- Principal: sempre 1 quando há telefones; único fica principal e switch desabilitado; ao remover o principal, o primeiro restante assume; trocar só marcando outro


## Refs admissão
- `telefonesModal` / `telefonesAvulsa` no submit
