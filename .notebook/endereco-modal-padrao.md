# Endereco — padrão modal

## Escopo
- `resources/js/components/Endereco.vue`: grade densa `mybp-filtro-campo` + `form-control-sm`
- UF → `ComboboxAutoComplete` (27 UFs)
- CEP: busca ViaCEP com botão `btn-sm` + Enter
- Sem fieldset próprio (pai usa seção Endereço)
- `validarCampos()` só quando `obrigatorio=true` (admissão usa `false`)

## Refs admissão
- `enderecoModal` / `enderecoAvulsa`
