# Mensagem de Aniversariante Context

**Gathered:** 2026-08-31
**Spec:** `.specs/features/mensagem-aniversariante/spec.md`
**Status:** Ready for execute

---

## Feature Boundary

Tela para o cliente editar o **corpo** do e-mail de aniversário da sua empresa. Assunto e layout oficial não mudam.

---

## Implementation Decisions

### Onde fica a tela

- Novo item no menu Administração, ao lado de Aniversariantes
- Mesmo padrão visual da Carta Oferta — Template

### O que o cliente edita

- Só o corpo da mensagem
- Assunto permanece `{empresa} - FELIZ ANIVERSÁRIO`

### Editor

- TinyMCE (config existente)
- Placeholders `{{nome}}` e `{{empresa}}`
- Botão Preview com dados simulados

### Agent's Discretion

- Permissão: reusar `administracao_aniversariantes`
- Persistência: uma linha por empresa (upsert), sem versão
- Cache da mensagem com TTL 1h e invalidação no save
- Resolver no job automático usa `withoutGlobalScopes` (multi-empresa)

### Declined / Undiscussed Gray Areas → Assumptions

- Sem botão “restaurar padrão” no MVP
- Sem customizar assunto

---

## Specific References

Espelhar `CartaOfertaTemplate.vue` + `CartaOfertaTemplateController`.

---

## Deferred Ideas

- Customizar assunto
- Restaurar mensagem padrão
- Permissão isolada
