# Mensagem de Aniversariante — Specification

## Problem Statement

O e-mail de parabéns de aniversário usa um texto fixo para todos os clientes. Cada empresa precisa escrever a própria mensagem, sem perder o layout oficial do e-mail nem o assunto padrão.

## Goals

- [ ] Cliente com acesso a Aniversariantes consegue editar o corpo da mensagem da sua empresa
- [ ] Envio manual e automático usam a mensagem customizada (ou o texto padrão se não houver customização)
- [ ] Placeholders `{{nome}}` e `{{empresa}}` são substituídos no envio e no preview

## Out of Scope

| Feature | Reason |
| --- | --- |
| Customizar assunto do e-mail | Confirmado: permanece `{empresa} - FELIZ ANIVERSÁRIO` |
| Trocar o layout oficial do e-mail | Template MyBP obrigatório |
| Versionamento do template | Uma mensagem vigente por empresa |
| WhatsApp / outros canais | Só e-mail de aniversário |

---

## Assumptions & Open Questions

| Assumption / decision | Chosen default | Rationale | Confirmed? |
| --- | --- | --- | --- |
| Onde fica a tela | Menu Administração, item próprio (como Carta Oferta) | Escolha do cliente | y |
| O que edita | Só o corpo | Escolha do cliente | y |
| Editor | TinyMCE + placeholders + preview | Escolha do cliente | y |
| Permissão | Reusa `administracao_aniversariantes` | Quem já envia parabéns customiza sem papel extra | y |
| Sem template | Usa o texto padrão atual, com `{{nome}}` | Não quebra envios existentes | y |
| Escopo | 1 mensagem por `empresa_id` (upsert) | Multi-tenant; sem versionar | y |

**Open questions:** none.

---

## User Stories

### P1: Customizar e enviar ⭐ MVP

**User Story**: Como RH da empresa, quero editar o corpo do e-mail de aniversário com editor rico e placeholders, para que os colaboradores recebam a mensagem da minha empresa.

**Why P1**: Sem isso a mensagem continua padrão.

**Acceptance Criteria**:

1. WHEN o RH abre Administração > Mensagem de Aniversário THEN o sistema SHALL exibir o editor com a mensagem salva ou, se não houver, o texto padrão atual
2. WHEN o RH salva um HTML não vazio THEN o sistema SHALL persistir uma mensagem vigente só daquela `empresa_id`
3. WHEN o RH pede preview THEN o sistema SHALL substituir `{{nome}}` e `{{empresa}}` por dados simulados e sanitizar tags perigosas
4. WHEN o e-mail é enviado (manual ou JobAniversariantesDia) e existe mensagem da empresa THEN o sistema SHALL usar esse HTML no corpo, dentro do layout oficial, com placeholders preenchidos
5. WHEN não existe mensagem (ou está vazia) THEN o sistema SHALL usar o texto padrão atual
6. WHEN o assunto é montado THEN o sistema SHALL manter `{nome da empresa} - FELIZ ANIVERSÁRIO`

**Independent Test**: Salvar mensagem com `{{nome}}`, gerar preview, disparar e-mail e conferir corpo + assunto.

---

## Edge Cases

- WHEN `conteudo_html` vazio no save THEN o sistema SHALL recusar (validação)
- WHEN placeholder desconhecido THEN o sistema SHALL substituir por string vazia
- WHEN HTML contém `<script>` THEN o sistema SHALL remover a tag no render/preview
- WHEN o job automático envia para várias empresas THEN cada e-mail SHALL usar a mensagem da `empresa_id` do colaborador (sem ScopeEmpresa de sessão)

---

## Requirement Traceability

| Requirement ID | Story | Phase | Status |
| --- | --- | --- | --- |
| ANIV-01 | P1: editor carrega salvo ou padrão | Execute | Pending |
| ANIV-02 | P1: persistir por empresa | Execute | Pending |
| ANIV-03 | P1: preview com placeholders + sanitização | Execute | Pending |
| ANIV-04 | P1: envio usa mensagem customizada | Execute | Pending |
| ANIV-05 | P1: fallback padrão | Execute | Pending |
| ANIV-06 | P1: assunto inalterado | Execute | Pending |

**Coverage:** 6 total

---

## Success Criteria

- [ ] Tela no menu Administração, padrão Carta Oferta
- [ ] Envio manual e automático respeitam a mensagem da empresa
- [ ] Sem template, o texto de hoje continua igual
