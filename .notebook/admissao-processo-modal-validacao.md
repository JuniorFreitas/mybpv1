# Admissão > Processo — modal + validação

## Escopo
- `formAdmissao.vue`: SFC `mybp-modal-form` + combos + `validarCampos()` (status, função, prazo FIXO, data)
- Admitir + Avulsa: `mybp-modal-form` + seções irmãs (sem fieldset aninhado) + grade `col-md-4` + `ComboboxAutoComplete` com **IDs estáticos**
- Massa / Demitir: mesmo padrão; demitir valida `demitir-data`

## Layout (UX)
- Seções flat (Admitir): Dados Pessoais → Endereço → Contato → Documentos → Formação → EPI → Técnica → Sobre a Vaga → Rota → Testes → RI → form-admissao → Dependentes → Bancários → Foto
- Sem wrappers `col-12` / `row` em volta de componentes (`endereco`, `telefone`, Formação)
- Legends title-case (não ALL CAPS); Documentos em `col-md-4` (não `col-lg-2`)
- Endereço 2ª linha: Complemento/Bairro `col-md-3` + Município `col-md-4` + UF `col-md-2`
- Telefone sem obs: Tipo `col-md-4` + Número `col-md-5` + Principal `col-md-3`
- Legenda obrigatórios: `mybp-campo-obrigatorio-legenda mybp-modal-legenda`
- Bools via computed `*Combo` (`sim`/`nao` ↔ boolean)

## Regra Blade (crítico)
- Nunca usar `` `id-${hash}` `` nem `'id-' + hash` / `hash')` em `@select` / `@opening` / `:input-id` do Combobox
- Usar `input-id="avulsa-pcd"` / `adm-sexo` e `@select="limparComboboxInvalido('avulsa-pcd')"`
- Autocomplete de vaga pode usar hash no id (`vaga_${hash}` avulsa / `vaga_edit_${hash}` admitir)

## Validação submit
- Avulsa (`CadastraAvulsa`): PCD (`avulsa-pcd`) + Indicado (`avulsa-indicacao`) + refs `validarCampos` + vaga
- Admitir (`alterar`): PCD + Formação + refs
- Demitir: `exigirCampoData(..., 'demitir-data')`
- Formação: `window.__MYBP_ESCOLARIDADES` → `opcoesFormacaoModal`
