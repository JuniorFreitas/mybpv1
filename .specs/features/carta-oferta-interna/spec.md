# Carta Oferta interna configurável — Specification

## Problem Statement

A carta oferta nasce quase só via SGI. O cliente MyBP precisa criar, configurar o texto (template) e conduzir o aceite 100% dentro do sistema, com dados reais do candidato/vaga.

## Goals

- [ ] RH cria carta interna (`local=MyBPIN`) a partir de currículo + vaga aberta
- [ ] Template publicado por empresa alimenta o PDF (assinatura digital)
- [ ] Variáveis `setor`, `salario`, `data_inicio` preenchíveis e renderizadas
- [ ] Aceite RH interno não chama integração SGI
- [ ] Listagem no padrão cards/filtros MyBP + ação Nova carta
- [ ] Editor de template com placeholders clicáveis, preview e restaurar padrão

## Out of Scope

| Feature | Reason |
| --- | --- |
| Multi-template por cargo/filial | Um publicado por empresa |
| Expiração automática | Fora deste ciclo |
| Remover fluxo SGI | Legado mantido |
| Checklist S3 configurável na UI | Fora deste ciclo |

## Assumptions

| Assumption | Default | Rationale |
| --- | --- | --- |
| Status inicial interno | `Pendente Anexo` | Compatível com envio assinatura existente |
| Sem assinatura digital | E-mail/WhatsApp com link público (upload legado) | Fallback |
| Campos oferta | `setor`, `salario`, `data_inicio` na carta | Template precisa de fonte persistida |
| Duplicidade | Bloqueia se já existe carta aberta (Pendente/Aguardando) do currículo na empresa | Evita spam |

## User Stories

### P1: Criar e enviar carta interna

**AC:**
1. WHEN RH cria carta com currículo + vaga e template publicado THEN sistema SHALL criar `CartaOferta` com `local=MyBPIN` e token
2. WHEN assinatura digital habilitada THEN sistema SHALL enfileirar `JobProcessarEnvioAssinatura`
3. WHEN assinatura não habilitada THEN sistema SHALL enfileirar e-mail de link da carta
4. WHEN não há template publicado THEN sistema SHALL recusar criação (422)

### P1: Template e variáveis

**AC:**
5. WHEN PDF/preview é gerado THEN sistema SHALL preencher placeholders com dados reais ou "Não informado"
6. WHEN RH restaura padrão THEN editor SHALL carregar HTML padrão do sistema

### P1: Aceite RH

**AC:**
7. WHEN RH aceita/recusa carta `local=MyBPIN` em `Aguardando RH` THEN sistema SHALL atualizar status sem chamar `IntegraSgiMybpController::integra`
8. WHEN carta `local=SGI` e aceite THEN comportamento SGI atual permanece

### P2: UX listagem

**AC:**
9. WHEN RH abre listagem THEN sistema SHALL exibir filtros + cards MyBP e ação Nova carta

## Requirement Traceability

| ID | Story | Status |
| --- | --- | --- |
| COI-01 | Criar carta MyBPIN | Pending |
| COI-02 | Exigir template publicado | Pending |
| COI-03 | Enviar assinatura ou e-mail | Pending |
| COI-04 | Contexto variáveis reais | Pending |
| COI-05 | Restaurar padrão template | Pending |
| COI-06 | Aceite sem SGI (MyBPIN) | Pending |
| COI-07 | Listagem padrão MyBP | Pending |
