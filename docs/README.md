# Documentação MyBPIN

Índice da pasta `docs/`. Stack atual: **Laravel 12 · PHP 8.3 · Vue 3 · Vite 6 · MySQL · Redis · Docker `mybpdp`**.

## Visão e mapa

| Doc | Conteúdo |
|-----|----------|
| [01-visao-geral.md](./01-visao-geral.md) | O que é o sistema, módulos, integrações |
| [02-mapa-do-sistema.md](./02-mapa-do-sistema.md) | Inventário de pastas e volumes |
| [03-modulos-de-negocio.md](./03-modulos-de-negocio.md) | Domínios de RH |
| [04-arquitetura-tecnica.md](./04-arquitetura-tecnica.md) | Camadas, rotas, jobs, frontend (**build: Vite**) |
| [05-banco-de-dados.md](./05-banco-de-dados.md) | Modelo de dados |
| [06-regras-de-negocio.md](./06-regras-de-negocio.md) | Regras |
| [07-fluxos-criticos.md](./07-fluxos-criticos.md) | Fluxos ponta a ponta |
| [08-integracoes.md](./08-integracoes.md) | Integrações |
| [09-riscos-tecnicos.md](./09-riscos-tecnicos.md) | Riscos (parcialmente atualizado em 2026-11-23) |
| [10-backlog-sugerido.md](./10-backlog-sugerido.md) | Backlog histórico; board ativo em `.harness/backlog/` |

## API e cliente

| Doc | Conteúdo |
|-----|----------|
| [API_V3_EXTERNA.md](./API_V3_EXTERNA.md) | Guia cliente API v3 |
| [API_V3_EXTERNA_REFERENCE.md](./API_V3_EXTERNA_REFERENCE.md) | Reference |
| [CONFIGURACAO_NOVO_CLIENTE.md](./CONFIGURACAO_NOVO_CLIENTE.md) | Implantação de cliente |

## UX frontend (obrigatório)

| Doc | Conteúdo |
|-----|----------|
| [PADRAO_UX_LISTAGEM_CADASTROS.md](./PADRAO_UX_LISTAGEM_CADASTROS.md) | Cards + filtros |
| [PADRAO_UX_FILTROS_COMPACTOS.md](./PADRAO_UX_FILTROS_COMPACTOS.md) | Filtros operacionais |
| [PADRAO_UX_CARD_DETALHE.md](./PADRAO_UX_CARD_DETALHE.md) | Card detalhe |
| [PADRAO_VALIDACAO_COMBOBOX.md](./PADRAO_VALIDACAO_COMBOBOX.md) | Combobox |
| [PLANO_MIGRACAO_COMPOSITION_API_SERVICES.md](./PLANO_MIGRACAO_COMPOSITION_API_SERVICES.md) | Migração Vue Composition API |

## Operação / agentes

- Backlog Harness: [`.harness/backlog/INDEX.md`](../.harness/backlog/INDEX.md)
- Regras de agentes: [`AGENTS.md`](../AGENTS.md)
- Build frontend: `npm run hot` · `npm run build` · `npm run prod` (Vite — **não** usar Laravel Mix)

## Nota sobre docs 01–04

Alguns trechos ainda podem citar Mix/`webpack.mix.js` por histórico. A fonte de verdade do build é `package.json` + `vite.config.js`.
