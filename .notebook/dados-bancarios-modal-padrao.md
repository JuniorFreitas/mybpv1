# DadosBancarios — padrão modal

## Escopo
- `resources/js/components/DadosBancarios.vue`: `mybp-modal-secao` + `mybp-label` + `form-control-sm`
- Tem PIX e Tipo de Chave → **`<select>` nativo** (valores string) + `ComboboxValidation` no submit
- Tipo chave: domínio `UsuarioConta::TIPOS_CHAVES` (`cpf`, `cnpj`, `email`, `telefone`, `aleatoria`); legado em maiúsculo é normalizado
- `validarCampos()` no submit via refs `dadosBancariosModal` / `dadosBancariosAvulsa`

## Gotcha — Tem PIX quebrado com Combobox
- Migração para `ComboboxAutoComplete` (Teleport) no rodapé do modal de admissão impedia selecionar Sim/Não; campo obrigatório bloqueava o save
- **Não** voltar a `<option :value="true|false">` — boolean em select nativo do Vue é instável
- Padrão seguro: `:value` + `@change` com string `''|sim|nao` ↔ `model.pix` boolean (`obterPixComboValor` no submit)
- `model.pix` continua boolean no payload/API; `false` = Não (válido)
- Ver também: [dados-bancarios-pix-nao-validacao](dados-bancarios-pix-nao-validacao.md)

## Refs
- Blade: `resources/views/g/admissao/processo/index.blade.php`
- Model: `app/Models/UsuarioConta.php`
