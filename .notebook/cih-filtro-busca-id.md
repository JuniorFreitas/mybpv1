# CIH — busca por CÓD no filtro

- Filtro `campoBusca` em `CihFilterApplier::applySearchFilter()`: nome do curriculo (like) **ou** `cihs.id`.
- UI: placeholder "Buscar por nome ou CÓD" em `resources/js/components/admissao/apontamento/CIH.vue`.
- Filtros e formulário: selects → `ComboboxAutoComplete` (status, tipo, área, CC, gestores + modal).
- Exportação usa o mesmo applier via listagem.
