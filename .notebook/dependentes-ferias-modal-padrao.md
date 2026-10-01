# Dependentes + Férias adquiridas — padrão modal

## Dependentes
- `resources/js/components/admissao/processo/Dependentes.vue`
- `mybp-modal-secao` + grade densa; tipo → combobox (`/admissao/tipos_dependentes`)
- `validarCampos()` se houver itens: tipo, nome, especifique (outro)
- Refs: `dependentesModal` / `dependentesAvulsa`

## Férias / avulsa
- Avulsa: `ref="formAdmissaoAvulsa"` + `validarCampos()` no `CadastraAvulsa`
- Backend: `Admissao::FeriasAdquiridasCriaOuAtualiza` ignora linha sem `periodo_gozado`/`proximo_periodo` (evita 1048 com ConvertEmptyStringsToNull)
