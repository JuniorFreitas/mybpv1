# Padrão — Validação visual do ComboboxAutoComplete

Não usar `valida_campo_vazio()` no input do combobox: o `invalid-feedback` entra no flex do `.input-group` e esmaga o campo.

## Util e mixin

- Util: `resources/js/utils/comboboxValidation.js`
  - `marcarComboboxObrigatorio(inputId, mensagem?)`
  - `limparComboboxInvalido(inputId)`
  - `exigirCombobox(valor, inputId, { mensagem, toast, toastMsg })`
- Mixin: `resources/js/mixins/ComboboxValidation.js` (expõe os mesmos métodos)
- CSS: `resources/sass/_mybp-listagem-ui.scss` — `.mybp-combobox-wrap > .invalid-feedback`

## Template

```vue
<div class="mybp-combobox-wrap">
  <combobox-auto-complete
    :input-id="`status-${hash}`"
    v-model="form.status"
    :options="opcoes"
    @select="limparComboboxInvalido('status-' + hash)"
  />
</div>
```

## Submit (status aprovação)

```js
aprovar() {
  if (!this.exigirCombobox(this.form.status, `status-${this.hash}`, {
    toastMsg: 'Selecione o status da aprovação'
  })) {
    return
  }
}
```

## Submit (solicitação — campos obrigatórios ativos)

```js
if (!this.exigirCampoData(this.form.data_x, `data-${this.hash}`, { toastMsg: 'Informe a data' })) return
if (!this.exigirCombobox(this.form.tipo, `tipo-${this.hash}`, { toastMsg: 'Selecione...' })) return
return this.validarInputsAtivosVisiveis(this.hash, { preservarIds: [`data-${this.hash}`] })
```

## Ref. viva

`SolicitacaoDemissao.vue` / `SolicitacaoMudaCargo.vue` — solicitação + aprovação.
