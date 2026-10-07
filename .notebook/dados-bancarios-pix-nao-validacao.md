# DadosBancarios — Tem PIX "Não" bloqueava save

## Sintoma
Admissão avulsa: select Tem PIX? = Não, toast "Selecione se tem PIX" + "Campo obrigatório".

## Causa
1. `buscaCPF` faz `Object.assign(formAvulsa, data)` e substitui `feedback` **sem** `banco_conta` → prop `model` some/fica inconsistente; select pode mostrar Não sem `model.pix` válido.
2. Validação lia só `pixCombo` do model; desync DOM/model fazia `exigirCombobox` ver vazio com UI em "Não".
3. `false` é resposta válida ("Não") — não tratar como não selecionado.

## Fix
- API `buscaCPF`: sempre envia `feedback.banco_conta` (default `pix: false`)
- Front: `garantirBancoContaAvulsa()` após busca CPF
- `DadosBancarios`: `:value` + `@change`, `obterPixComboValor()` sincroniza select→model antes de validar

## Refs
- `resources/js/components/DadosBancarios.vue`
- `resources/js/g/admissao/processo/app.js` (`garantirBancoContaAvulsa`)
- `AdmissaoController::buscaCPF`
