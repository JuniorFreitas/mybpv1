# Plano: Migração Composition API + Services (frontend)

Documento de referência para a migração gradual do frontend Vue (Options API + mixins) para Composition API e Services/composables. O agente que executa a migração deve seguir [agents/migracao-frontend/README.md](../agents/migracao-frontend/README.md).

---

## 1. Estado atual

- Cerca de **130 componentes Vue** em Options API; **~80 entradas** (app.js por tela) com `createApp` + `mount`.
- **6 mixins** compartilhados: Exportacoes.js, ExportarExcelMixin.js, Configuracoes.js, Utils.js, Validacoes.js (~900+ linhas), avaliacaoNoventaMixin.js, além de configselect2 e avaliacaoMixin locais.
- **Padrões repetidos:** axios com URL_ADMIN/URL_SITE, preload, try/catch/finally, mostraErro/mostraSucesso, formReset(), _.cloneDeep(formDefault), buscar()/atualizar() muito similares.

---

## 2. Objetivos

- Introduzir **Services** (api, notificacao, config, exportacao, constants) para eliminar repetição.
- Introduzir **Composables** (usePreload, useConfig, useExportacao, useExportarExcel, useValidacoes, useConstants) para substituir mixins e permitir Composition API.
- Migração **incremental**: não reescrever tudo de uma vez; novos componentes e componentes tocados seguem o novo padrão.

---

## 3. Estrutura alvo

```
resources/js/
  services/
    api.js
    exportacao.js
    notificacao.js
    config.js
  composables/
    usePreload.js
    useExportacao.js
    useExportarExcel.js
    useConfig.js
    useValidacoes.js
    useConstants.js
  constants/
    index.js
```

- **Services:** funções/objetos sem dependência do ciclo de vida Vue (uso em qualquer lugar).
- **Composables:** funções `useX()` com `ref`/`reactive`/`onMounted`, retornando estado e métodos.

---

## 4. Fases de execução

- **Fase 1:** Criar services (api, notificacao, config, exportacao) e constants.
- **Fase 2:** Criar composables que usam os services.
- **Fase 3:** Piloto em 1–2 telas (ex.: relatório vencimento férias, um cadastro).
- **Fase 4:** Substituir gradualmente mixins por composables nos componentes alterados.
- **Fase 5:** Migrar componentes críticos para Composition API em lotes pequenos.

---

## 5. Referências

- Plano detalhado (análise e impacto): ver histórico do Cursor ou o plano "Composition API e Services frontend".
- Regras do agente de migração: [agents/migracao-frontend/README.md](../agents/migracao-frontend/README.md).
- Padrões do projeto: [AGENTS.md](../AGENTS.md).
