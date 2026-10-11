---
name: versioning-changelog
description: Commit, versionamento, changelog, release, package.json e composer.json. Use when the user asks to commit changes, prepare a release, bump version, or generate/update CHANGELOG.md with synchronized package.json and composer.json versions.
---

# Versioning Changelog

Use esta skill quando o usuario pedir para efetuar commit, versionar, preparar release, atualizar changelog, gerar notas de versao, ou sincronizar versoes entre `package.json` e `composer.json`.

## Objetivo

Antes de criar um commit solicitado explicitamente, entender todas as mudancas do worktree, definir o bump semantico adequado, atualizar changelog e sincronizar a versao em:

- `package.json`
- `composer.json`
- `package-lock.json`, apenas se ele ja possuir campo de versao raiz ou se for regenerado por comando seguro
- `CHANGELOG.md`

## Regras de seguranca

- Nunca criar commit se o usuario nao pediu commit explicitamente na conversa atual.
- Nunca usar `git reset --hard`, `git checkout --`, `git clean`, force push, amend, rebase interativo ou comandos destrutivos sem aprovacao explicita.
- Nunca reverter mudancas existentes que nao foram feitas por voce.
- Se houver mudancas nao relacionadas ao pedido, preserve-as e stage apenas os arquivos intencionais.
- Se houver conflito entre mudancas do usuario e o fluxo de versionamento, pare e pergunte como proceder.
- Nao alterar segredos, `.env` real ou credenciais.

## Fluxo Obrigatorio

1. Inspecionar o repositorio:
   - `git status --short`
   - `git diff --stat`
   - `git diff`
   - `git log --oneline -10`
2. Identificar o que foi feito:
   - Agrupar alteracoes por area funcional.
   - Separar features, fixes, seguranca, refactors, docs, testes, build e chores.
   - Detectar breaking changes por diff, mensagens existentes ou instrucao explicita do usuario.
3. Definir a versao alvo:
   - Se o usuario informou versao ou tipo de bump (`major`, `minor`, `patch`), seguir isso.
   - `major`: breaking change ou incompatibilidade publica.
   - `minor`: nova funcionalidade compatibilidade preservada.
   - `patch`: bugfix, security fix, ajuste interno, docs, testes, build, chore ou refactor sem nova funcionalidade.
   - Se `package.json` e `composer.json` tiverem versoes diferentes, perguntar antes de sobrescrever, exceto quando o usuario informar a versao alvo.
4. Atualizar arquivos:
   - Atualizar `version` em `package.json`.
   - Atualizar `version` em `composer.json`.
   - Atualizar ou criar `CHANGELOG.md`.
   - Preservar indentacao e estilo dos JSONs existentes.
5. Formato do changelog:
   - Seguir o padrao `Keep a Changelog` e versionamento `Semantic Versioning`.
   - Usar `# Changelog` no topo quando criar arquivo novo.
   - Incluir a frase padrao: `All notable changes to this project will be documented in this file.`
   - Incluir referencias padrao: `The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).`
   - Manter uma secao `## [Unreleased]` no topo.
   - Criar entrada de release como `## [x.y.z] - YYYY-MM-DD` abaixo de `Unreleased`.
   - Usar apenas categorias oficiais quando houver itens reais: `Added`, `Changed`, `Deprecated`, `Removed`, `Fixed`, `Security`.
   - Nao usar categorias fora do padrao, como `Tests`, `Docs`, `Build` ou `Chore`; nesses casos, classificar em `Changed` ou omitir se nao for relevante ao usuario final.
   - Escrever todos os itens em portugues do Brasil.
   - Registrar tudo que foi feito de forma objetiva, baseado no diff real, sem marketing e sem inventar mudancas.
   - Cada item deve ter referencia rastreavel no proprio bullet, usando `Referencias: caminho/do/arquivo`, issue, PR ou identificador equivalente quando existir.
   - Quando uma mudanca envolver muitos arquivos, agrupar por funcionalidade e listar as referencias principais em vez de repetir todos os arquivos.
   - Exemplo: `- Adicionado controle de acesso por empresa no portal de documentos. Referencias: app/Http/Controllers/DocumentController.php, routes/web.php.`
   - Se houver links de comparacao no changelog existente, manter e atualizar o formato de links no rodape.
6. Validar:
   - Validar JSON de `package.json` e `composer.json`.
   - Rodar testes ou checks relevantes quando viavel.
   - Se nao for viavel rodar tudo, explicar exatamente o que nao foi executado.
7. Commit:
   - Revisar `git diff` final.
   - Stage apenas arquivos relacionados.
   - Criar commit com mensagem convencional quando adequado.
   - Preferir `chore(release): vX.Y.Z` quando o commit incluir versionamento/changelog de release.
   - Nao criar tag nem push sem pedido explicito.

## Saida Esperada

Ao finalizar, informe de forma curta:

- Versao anterior e nova versao.
- Arquivos alterados para versionamento/changelog.
- Hash do commit criado, se commit foi solicitado e executado.
- Validacoes executadas e qualquer limitacao.

## Delegacao Para Agent

Quando a tarefa for mais do que uma edicao simples, delegue para o agent `version-changelog` com contexto suficiente sobre o pedido do usuario e o estado do repositorio.
