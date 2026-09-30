# CIH — modal no padrão operacional MyBP

- Arquivo: `resources/js/components/admissao/apontamento/CIH.vue`
- Form: `mybp-modal-form mybp-filtros-compactos`
- Seções: Ocorrência → Envolvidos → Detalhes → Aprovação Gestor → Aprovação RH
- Data: `mybp-modal-campo-data` + `corrigiDatepicker`
- Combos: `limparComboboxInvalido` / `exigirCombobox` (mixin `ComboboxValidation`)
- Aprovações: Status `col-md-4` + Observação `col-md-8`; meta em `p.text-muted`
- Grid densos: `col-12 col-md-4` (Lotação/CC/Área/Tipo)
