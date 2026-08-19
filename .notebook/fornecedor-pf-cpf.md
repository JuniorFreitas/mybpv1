# Fornecedor PF / CPF

## Entry
- UI ativa: `resources/views/g/administracao/fornecedores/index.blade.php` + `resources/js/g/administracao/fornecedores/app.js`
- `FornecedoresComponent.vue` existe, mas **não** é a tela montada nesta rota
- Backend já suportava PF: `FornecedorController::validarDados()`, constantes em `Fornecedor::PESSOA_FISICA`

## Flow
1. Select `tipo` → Fornecedor | Parceiro | Parceiro Externo | Terceiro (`Fornecedor::TIPO_*`, coluna string, sem ENUM)
2. Select `tipo_pessoa` → PJ (CNPJ + razão social) ou PF (CPF + nome)
3. `POST administracao/fornecedor` cria `User` + `Fornecedor`
4. Duplicidade: `GET fornecedor/buscar-cpf` → `Sistema::verificaCpfCadastrado`

## Gotcha
- Campos de PF estavam comentados na blade; só PJ aparecia no select
- `tipo` não tem whitelist no `validarDados()`; valor vem da constante
- Serviços: só `TIPO_FORNECEDOR` pede vencimento; demais (Parceiro, Parceiro Externo, Terceiro) usam data início/encerramento
- Build: `webpack.mix.js` → `public/js/g/fornecedores/app.js`

Updated: 2026-08-18
