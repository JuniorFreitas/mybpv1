# Cadastro de Documentos da Pré-admissão — Specification

## Problem Statement

Os documentos exigidos na pré-admissão do candidato são definidos nas tabelas `documentos_curriculos_cat_adm_empresa` (categorias) e `documentos_curriculos_adm_empresa` (documentos), já filtrados por `empresa_id`. Hoje o cliente só altera isso no banco. Quem tiver permissão precisa administrar o catálogo da própria empresa pela UI, no padrão de listagem de Tipos de Dossiê.

## Goals

- [x] Usuário com `cadastro_documentos_preadmissao` acessa listagem com filtros + cards
- [x] Pode cadastrar, editar e ativar/desativar documentos da própria empresa
- [x] Pode escolher categoria existente ou criar uma nova no mesmo modal
- [x] Alterações passam a valer na pré-admissão do candidato (cache invalidado)

## Out of Scope

| Feature | Reason |
| --- | --- |
| Exclusão física de documento ou categoria | Cadastros usam ativo/inativo |
| Tela própria de categorias | Confirmado: categoria é campo/filtro; criar nova no modal |
| Editar `tipo` / `metodo` após criar | Identificadores dos anexos em `documentos_curriculos` |
| Campo `url_arquivo` | Não é usado no fluxo atual |
| Catálogo global / override | Tabelas já são por `empresa_id`; não há linha global |
| Alterar anexos já enviados pelo candidato | Só o catálogo da empresa |

---

## Assumptions & Open Questions

| Assumption / decision | Chosen default | Rationale | Confirmed? |
| --- | --- | --- | --- |
| Escopo da tela | Uma listagem de documentos; categoria é filtro e campo do modal | Escolha do cliente | y |
| CRUD | Cadastrar, editar, ativar/desativar | Escolha do cliente | y |
| Flags de anexo | Todas visíveis no modal (`obrigatorio`, `apenas_img`, `apenas_pdf`, `apenas_pdf_img`, `multiple`, `min`/`max`, `sogestao`) | Escolha do cliente | y |
| `tipo` | Gerado do nome (`snake_case`) na criação e travado na edição | Escolha do cliente; anexos usam `tipo` | y |
| Descrição | TinyMCE self-host (`tiny-mce-editor`, preset `simples`) | Escolha do cliente | y |
| Permissão | `cadastro_documentos_preadmissao` + `_insert` + `_update` | Mesmo padrão dos cadastros; papel 1 (Suporte) ganha todas no seeder | y |
| Menu | Cadastro > Documentos da Pré-admissão | Cadastro de catálogo, ao lado de Tipos de Dossiê | y |
| `metodo` | Gerado do nome (`StudlyCase`) na criação, oculto na UI, travado na edição | Compatível com o seeder; não é lido no runtime da pré-admissão | y |
| Isolamento | Só registros da `empresa_id` do usuário | Multi-tenant; tabelas já são por empresa | y |
| Cache | Invalidar `docAdmEmpresa_{empresa_id}` após criar/editar/ativar-desativar; getter deixa de dar forget a cada leitura | AGENTS.md: TTL + invalidação; getter atual zera o cache sempre | y |

**Open questions:** none — remaining items logged as assumptions above.

---

## User Stories

### P1: Administrar documentos da pré-admissão ⭐ MVP

**User Story**: Como RH com permissão, quero listar, cadastrar, editar e ativar/desativar os documentos da pré-admissão da minha empresa, com categoria e regras de anexo, para o candidato ver o catálogo correto sem intervenção no banco.

**Why P1**: Sem a tela o cliente continua dependendo de alteração manual no banco.

**Acceptance Criteria**:

1. WHEN o usuário sem `cadastro_documentos_preadmissao` acessa a rota THEN o sistema SHALL negar o acesso
2. WHEN lista THEN SHALL mostrar só documentos da `empresa_id` do usuário, com busca (nome, tipo ou ID), status e categoria, em cards `mybp-*`
3. WHEN cadastra com nome, categoria (existente ou nova), descrição opcional, ordem, ativo e flags THEN SHALL gravar com `empresa_id` do usuário, gerar `tipo` (`snake_case`) e `metodo` (`StudlyCase`) a partir do nome, e invalidar o cache `docAdmEmpresa_{empresa_id}`
4. WHEN o `tipo` gerado já existe na empresa THEN SHALL recusar o cadastro
5. WHEN edita THEN SHALL atualizar os campos permitidos sem alterar `tipo` nem `metodo`, e invalidar o cache
6. WHEN ativa/desativa THEN SHALL alternar `ativo` só na linha da empresa e invalidar o cache
7. WHEN o modal pede nova categoria com rótulo ainda inexistente na empresa THEN SHALL criar a categoria na mesma transação do documento
8. WHEN o candidato abre a pré-admissão após a alteração THEN SHALL ver o catálogo atualizado (documentos ativos, agrupados/ordenados como hoje)
9. WHEN o documento é `foto3x4` THEN SHALL recusar edição, inativação e cadastro manual; SHALL garantir o registro ativo em toda empresa

**Independent Test**: Com permissão, cadastrar um documento obrigatório numa categoria nova, conferir na listagem, desativar e confirmar que some da pré-admissão; usuário sem permissão recebe 403.

---

## Edge Cases

- WHEN usuário sem `empresa_id` tenta cadastrar THEN o sistema SHALL recusar
- WHEN tenta editar/ativar documento de outra empresa THEN o sistema SHALL responder 404
- WHEN categoria existente é selecionada THEN o sistema SHALL NÃO criar categoria duplicada
- WHEN `multiple` é verdadeiro e `max` < `min` THEN o sistema SHALL recusar
- WHEN `apenas_img`, `apenas_pdf` e `apenas_pdf_img` tiverem mais de um verdadeiro THEN o sistema SHALL recusar (mutuamente exclusivos; todos falsos = qualquer tipo aceito pelo upload)
- WHEN descrição contém `<script>` THEN o sistema SHALL sanitizar no persistir/exibir
- WHEN a empresa não tem categorias THEN o sistema SHALL exigir criar uma no modal para salvar o documento
- WHEN `sogestao` é verdadeiro THEN o documento SHALL permanecer no cadastro, mas NÃO SHALL aparecer no formulário do candidato (`Documento.vue`)
- WHEN o documento tem `tipo` `foto3x4` THEN o sistema SHALL recusar editar, inativar e cadastrar de novo; se estiver inativo, SHALL reativar

---

## Requirement Traceability

| Requirement ID | Story | Phase | Status |
| --- | --- | --- | --- |
| PREADM-01 | Permissão de acesso | Validate | ✅ Verified |
| PREADM-02 | Listagem isolada por empresa + filtros | Validate | ✅ Verified (sem UAT visual) |
| PREADM-03 | Store com empresa_id, tipo/metodo gerados, cache | Validate | ✅ Verified |
| PREADM-04 | Unicidade de tipo na empresa | Validate | ✅ Verified |
| PREADM-05 | Update sem alterar tipo/metodo + cache | Validate | ✅ Verified |
| PREADM-06 | Ativa/desativa + cache | Validate | ✅ Verified |
| PREADM-07 | Criar categoria no mesmo modal/transação | Validate | ✅ Verified |
| PREADM-08 | Pré-admissão reflete o catálogo ativo | Validate | ✅ Verified (integração automatizada/estática; sem UAT visual) |
| PREADM-09 | foto3x4 padrão do sistema, imutável, presente em toda empresa | Validate | ✅ Verified |

**Coverage:** 9 total, mapped in tasks.md

---

## Success Criteria

- [x] Cliente com permissão administra o catálogo da própria empresa sem acessar o banco
- [x] Cliente sem permissão não acessa a rota nem o item de menu
- [x] Documento novo aparece na pré-admissão; inativo some
- [x] `tipo` de documentos já existentes permanece estável (anexos antigos não órfãos)
- [x] Toda empresa tem `foto3x4` ativo; o cadastro não permite alterar nem desativar
