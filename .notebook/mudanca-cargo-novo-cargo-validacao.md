# Mudança de Cargo — validação Novo Cargo

## Gotcha
- UI/API usam `nova_vaga_aberta_id` + `autocomplete_label_vaga_nova` (`selecionaVagaNovo`)
- Em `validarCamposSolicitacao()` **não** usar `novo_cargo_id` (campo de outro módulo `MudaCargoPrevista`)
- Checkmark (`:valido`) deve olhar o **id**, não só o label (texto digitado sem selecionar da lista)

## Refs
- `resources/js/components/planejamento/movimentacao/SolicitacaoMudaCargo.vue`
- Backend: `MudancaCargoController` → `nova_vaga_aberta_id` required_if mantem_cargo false
