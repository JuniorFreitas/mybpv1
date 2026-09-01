# Cadastro de Tipos de Dossiê — Specification

## Problem Statement

Os documentos do dossiê do colaborador são definidos na tabela `dossie_tipos`. Hoje o catálogo só muda via banco/seeder. O cliente com permissão precisa administrar esses tipos pela UI, no padrão de listagem de Treinamentos Indústria.

## Goals

- [ ] Usuário com `cadastro_dossie_tipos` acessa listagem com filtros + cards
- [ ] Pode cadastrar, editar e ativar/desativar tipos da própria empresa
- [ ] Catálogo global (`empresa_id` null) não é alterado pelo cliente; edição gera override da empresa

## Out of Scope

| Feature | Reason |
| --- | --- |
| Exclusão física | Cadastros usam ativo/inativo |
| Editar `tipo`/`chave` após criar | Identificadores de relacionamento do dossiê |
| Upload de modelo PDF nesta tela | Só metadados do tipo |

---

## Assumptions & Open Questions

| Assumption / decision | Chosen default | Rationale | Confirmed? |
| --- | --- | --- | --- |
| Permissão | `cadastro_dossie_tipos` + `_insert` + `_update` | Mesmo padrão dos cadastros | n (default seguro) |
| Escopo cliente | Override por `empresa_id`; global só leitura | `DossieTipo::ativosOrdenados()` já mescla assim | n |
| UI | FiltroListagem + cards mybp-* | Pedido: basear em Treinamentos Indústria | y |
| Menu | Cadastro > Tipos de Dossiê | Cadastro de catálogo | n |

**Open questions:** logged as assumptions above.

---

## User Stories

### P1: Administrar tipos ⭐ MVP

**User Story**: Como RH com permissão, quero listar, cadastrar, editar e ativar/desativar tipos de documento do dossiê da minha empresa.

**Acceptance Criteria**:

1. WHEN o usuário sem permissão acessa a rota THEN o sistema SHALL negar
2. WHEN lista THEN SHALL mostrar catálogo efetivo (global + override da empresa) com busca, status e escopo
3. WHEN cadastra THEN SHALL gravar com `empresa_id` do usuário e invalidar cache
4. WHEN edita um tipo global THEN SHALL criar/atualizar override da empresa sem mudar o global
5. WHEN ativa/desativa um tipo global THEN SHALL fazer o mesmo override
6. WHEN `tipo` ou `chave` já existem no catálogo efetivo THEN SHALL recusar o cadastro

---

## Requirement Traceability

| Requirement ID | Story | Status |
| --- | --- | --- |
| DOSSIE-01 | Permissão | Implementing |
| DOSSIE-02 | Listagem mesclada + filtros | Implementing |
| DOSSIE-03 | Store com empresa_id | Implementing |
| DOSSIE-04 | Update copy-on-write | Implementing |
| DOSSIE-05 | Ativa/desativa copy-on-write | Implementing |
| DOSSIE-06 | Unicidade tipo/chave | Implementing |
