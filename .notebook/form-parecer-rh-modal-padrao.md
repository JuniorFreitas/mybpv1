# FormParecerRh (FormRh) — padrão modal

## Escopo
- `resources/js/components/entrevistas/FormParecerRh.vue`
- Shell `mybp-modal-form mybp-filtros-compactos` + seções `fieldset.mybp-modal-secao`
- Grade densa: 1 `row` por seção (Bootstrap wrap), campos `col-md-4` / follow-ups ao lado; textos longos `col-md-8`; EPI/cursos `col-md-3`
- Sem fieldset aninhado (NR10 e Cursos são seções irmãs de “Outras informações”)
- Enums → `ComboboxAutoComplete` + opções computed; bools → `MybpBoolCombobox` (`boolean` ou `mode="simnao"` p/ `nr_dez` / comportamento)
- IDs com `hash` (SFC); `ComboboxValidation` + `onRhComboSelect` / `validarCampos()`
- Sem `<select>` nativo restante

## Refs submit
- `parecer_rh`, `gestor_rh`, `entrevista_rh`: `ref="formRh"` + `validarCampos()` em cadastrar/alterar
- Escopo da validação respeita props: parecer editável / gestor / entrevista RH

## Wrapper UI
- `resources/js/components/ui/MybpBoolCombobox.vue` — Sim/Não ↔ boolean ou `'sim'|'nao'`
