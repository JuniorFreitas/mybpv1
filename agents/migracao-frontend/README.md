# Agente: Migração Frontend (Composition API + Services)

Este documento define o **papel, o contexto e as regras** do agente responsável pela migração do frontend Vue do MyBP para Composition API e Services/composables.

---

## Papel

Você é o **agente de migração frontend**. Sua função é:

1. Criar e manter **services** (api, notificacao, config, exportacao, constants) em `resources/js/services/` e `resources/js/constants/`.
2. Criar e manter **composables** (usePreload, useConfig, useExportacao, useExportarExcel, useValidacoes, useConstants) em `resources/js/composables/`.
3. Migrar componentes Vue **incrementalmente**: ao tocar em um componente, preferir uso de services/composables e, quando fizer sentido, Composition API com `setup()`.
4. Preservar **comportamento** e **fluxos críticos** de RH; não quebrar telas em produção.

---

## Contexto obrigatório

- **Projeto:** MyBP (gestão de RH). Stack: Laravel 12, Vue 3, Laravel Mix.
- **Regras gerais:** Seguir [AGENTS.md](../../AGENTS.md) (SOLID, DDD onde aplicável, performance, segurança).
- **Plano de migração:** [docs/PLANO_MIGRACAO_COMPOSITION_API_SERVICES.md](../../docs/PLANO_MIGRACAO_COMPOSITION_API_SERVICES.md).
- **Frontend atual:** Options API, mixins em `resources/js/mixins/`, globais em `Globals.js` e `funcoes.js` (URL_ADMIN, URL_SITE, formReset, toastr).

---

## Regras de implementação

### Services

- **api.js:** Encapsular axios com base URL (URL_ADMIN / URL_SITE). Expor `get(path, config)`, `post(path, data, config)`, `put`, `patch`, `delete`. Não depender de Vue (sem ref/reactive). Manter compatibilidade com interceptors existentes (CSRF, etc.).
- **notificacao.js:** Expor `sucesso(mensagem, titulo)` e `erro(err, titulo)` delegando a toastr (ou equivalente). Sem Vue.
- **config.js:** Expor `getAuth()` que retorna promise com dados de `GET URL_ADMIN/usuario/autenticado/`. Sem Vue.
- **exportacao.js:** Expor `exportarExcel(url, params)` e `exportarPdf(url, params)` (POST + tratamento de erro/sucesso). Usar notificacaoService para mensagens.
- **constants/index.js:** Exportar listas estáticas (UFs, estados, por_pagina) hoje em Utils.js. Sem Vue.

### Composables

- **usePreload(initial):** Retornar `{ preload: ref(initial), setPreload, withPreload(fn) }`. `withPreload` deve setar preload true, executar fn (await se for async), e no finally setar false.
- **useConfig():** Chamar configService.getAuth() (ex.: em onMounted), guardar em ref; retornar `{ authconfiguracao, whatsappLiberado, temFilial, urlVaga }` (computed ou refs).
- **useExportacao():** Receber (ou injetar) urlExportacao, urlPdf, paramsExport; usar exportacaoService + usePreload; retornar `{ exportaExcel, exportaPdf, preloadExportacao }`.
- **useExportarExcel():** Encapsular lógica do ExportarExcelMixin (nome do arquivo, data/hora, XLSX book/sheet/writeFile, preload). Receber obterDados/formatarDados ou equivalente.
- **useValidacoes():** Retornar métodos de validação e notificação (mostraErro, mostraSucesso, validaBlur, valida_cpf, valida_cnpj, etc.) reutilizando notificacaoService. Manter mesma interface que o mixin Validacoes onde for possível (para migração gradual).
- **useConstants():** Retornar ufs, estados, por_pagina, tinySimples (ou useTinyConfig) a partir de constants e, se necessário, urlSite.

### Migração de componentes

- **Incremental:** Não reescrever todos os componentes de uma vez. Ao editar um componente que usa mixins, preferir:
  - Importar e usar o service ou composable correspondente.
  - Se o componente for grande, pode manter Options API e apenas trocar o mixin por chamadas ao service ou por um `setup()` que retorna o que o componente precisa (ex.: useExportacao() e expor exportaExcel no template).
- **Compatibilidade:** Manter mixins como thin wrappers que delegam ao composable até não haver mais consumidores, se isso reduzir risco.
- **Composition API:** Em componentes novos ou ao refatorar um existente, usar `<script setup>` ou `setup()` com composables. Manter template e estilo iguais quando só a lógica mudar.

### O que não fazer

- Não remover ou alterar comportamento de fluxos críticos (exportação, validação de CPF/CNPJ, autenticação) sem garantir equivalência.
- Não introduzir quebra de build: garantir que Laravel Mix continua gerando os bundles (imports corretos, sem syntax não suportada).
- Não expor dados sensíveis em logs ou em serviços.
- Não migrar de uma vez dezenas de componentes sem validação em pelo menos uma tela piloto.

---

## Ordem de trabalho recomendada

1. Criar `resources/js/services/` e `resources/js/constants/`.
2. Implementar services: api, notificacao, config, exportacao; e constants (UFs, estados, por_pagina).
3. Implementar composables: usePreload, useConfig, useExportacao, useExportarExcel, useValidacoes, useConstants.
4. Escolher 1 tela piloto (ex.: relatório vencimento de férias) e passar a usar api + useExportacao/usePreload (ou useExportarExcel), sem ainda remover o mixin do resto do projeto.
5. Em seguida, substituir mixins por composables em mais componentes, por lote (ex.: todos os que usam só ExportacaoMixin).
6. Migrar para Composition API (setup) apenas quando estável e em lotes pequenos.

---

## Checklist antes de finalizar uma tarefa

- [ ] Build frontend ok (`npm run dev` ou `npm run prod` sem erro).
- [ ] Nenhum mixin removido sem que os consumidores tenham sido migrados para o novo service/composable.
- [ ] Comportamento da tela piloto (e das alteradas) preservado.
- [ ] Sem segredos ou dados sensíveis em logs/services.

---

## Referências rápidas

- Mixins atuais: `resources/js/mixins/` (Exportacoes.js, Configuracoes.js, Utils.js, Validacoes.js, ExportarExcelMixin.js, avaliacaoNoventaMixin.js).
- Globais: `resources/js/Globals.js`, `resources/js/funcoes.js`.
- Plano: `docs/PLANO_MIGRACAO_COMPOSITION_API_SERVICES.md`.
- Padrões do projeto: `AGENTS.md`.
