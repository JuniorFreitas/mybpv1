# Fornecedor PF / CPF

## Entry
- UI ativa: `resources/views/g/administracao/fornecedores/index.blade.php` + `resources/js/g/administracao/fornecedores/app.js`
- `FornecedoresComponent.vue` existe, mas **não** é a tela montada nesta rota
- Backend já suportava PF: `FornecedorController::validarDados()`, constantes em `Fornecedor::PESSOA_FISICA`

## Flow
1. Select `tipo_pessoa` → PJ (CNPJ + razão social) ou PF (CPF + nome)
2. `POST administracao/fornecedor` cria `User` + `Fornecedor`
3. Duplicidade: `GET fornecedor/buscar-cpf` → `Sistema::verificaCpfCadastrado`

## Gotcha
- Campos de PF estavam comentados na blade; só PJ aparecia no select
- Build: `webpack.mix.js` → `public/js/g/fornecedores/app.js`
