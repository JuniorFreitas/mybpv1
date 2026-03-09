# Análise de conformidade Vue 3 no projeto MyBP

Análise do uso de Vue no projeto para verificar se **todas as partes que chamam Vue estão no formato correto do Vue 3**.

---

## 1. Resumo executivo

- **Versão Vue:** 3.5.29 (`package.json`).
- **Resultado:** O projeto está **em linha com Vue 3**. Não foram encontrados usos de APIs removidas ou incompatíveis (filters, `$listeners`, `$children`, `.native`, `$set`, event bus, `new Vue()`, etc.).
- **Ponto de atenção:** `resources/js/app.js` expõe `window.Vue = require('vue')`; em Vue 3 não existe mais construtor `new Vue()`. Nenhum código usa `new Vue()`, então isso é redundante ou apenas para inspeção; pode ser ajustado para evitar confusão.

---

## 2. O que foi verificado

### 2.1 Bootstrap da aplicação (createApp vs new Vue)

- **Resultado:** OK.
- **Detalhe:** Todas as entradas (ex.: `resources/js/g/*/app.js`, `recuperasenha/app.js`, `documentos/app.js`, etc.) usam:
  - `import { createApp } from 'vue'`
  - `const app = createApp({ ... })`
  - `app.mount('#app')`
- Nenhum arquivo usa `new Vue({ ... })`.

### 2.2 APIs removidas ou alteradas no Vue 3

| Item | Status no projeto |
|------|-------------------|
| **filters** (opção `filters: {}`) | Não utilizado. |
| **Vue.filter()** | Não utilizado. |
| **$listeners** | Não utilizado. |
| **$children** | Não utilizado (apenas variáveis locais `$children` em Select2/jQuery, não Vue). |
| **$on / $off / $once** (event bus) | Não utilizado. |
| **Modificador .native** (ex.: `@click.native`) | Não utilizado. |
| **Vue.set / this.$set** | Não utilizado. |
| **slot="nome"** (sintaxe antiga) | Não utilizado em código ativo; apenas em trechos comentados. |
| **slot-scope** | Não utilizado. |
| **functional: true** (componentes funcionais) | Não utilizado. |
| **beforeDestroy / destroyed** | Não utilizado (em Vue 3 seriam beforeUnmount / unmounted). |
| **$destroy()** | Não utilizado. |
| **keyCode** em modifiers | Não utilizado em componentes Vue (apenas em libs como jquery.mask). |

### 2.3 Slots

- **Resultado:** OK.
- **Detalhe:** Uso de **v-slot** (compatível com Vue 2.6+ e Vue 3):
  - Ex.: `v-slot:conteudo`, `v-slot:rodape`, `v-slot:template`.
- Arquivos com slots ativos: `cadastros/avaliacoes/avaliacao/index.vue`, `vinculaAvaliador.vue`, `avaliacaotopico/index.vue`, `avaliacaotipo/index.vue`, `avaliadortipo/index.vue`, `Weekly-report.vue`.
- Comentários com `slot="conteudo"` em `Intermitente.vue` e `Documento.vue` não afetam o runtime.

### 2.4 v-model e componentes

- **Resultado:** OK.
- Não foi encontrado uso do padrão antigo `:value` + `@input` para “v-model customizado” em desacordo com Vue 3.
- Nenhum uso de `v-model.sync` em template (apenas menção em comentário em `DateRangeFilter.vue`).

### 2.5 Uso de `window.Vue` (app.js)

- **Arquivo:** `resources/js/app.js` (carregado no layout principal).
- **Status:** Corrigido. O `require('vue')` e a atribuição `window.Vue = Vue` foram removidos. O bootstrap das telas continua sendo feito via `createApp` nas entradas de cada tela.

### 2.6 registerGlobals e plugins

- **Arquivo:** `resources/js/registerGlobals.js`.
- **Resultado:** OK.
- Usa `app.config.compilerOptions`, `app.use()`, `app.component()`, `app.directive()`, compatíveis com Vue 3.

### 2.7 Componentes .vue

- **Estrutura:** Uso de **Options API** (`export default { ... }`), sem `<script setup>` nem TypeScript.
- **Resultado:** OK para Vue 3. Options API é suportada; Composition API e `<script setup>` são opcionais para evolução futura.

### 2.8 Variáveis de ambiente

- Uso de `process.env.MIX_*` em alguns arquivos (ex.: `Configuracoes.js`, `utils.js`). Isso é adequado para **Laravel Mix**. Se no futuro o projeto migrar para Vite, será preciso trocar para `import.meta.env.VITE_*`. Não é um problema de “formato Vue 3”.

---

## 3. Recomendações (opcionais)

1. **app.js:** Já tratado — `window.Vue` e o `require('vue')` foram removidos.
2. **v-for / :key:** Ajustado em vários componentes: onde o item tem id estável (ex.: `item.id`, `avaliacaotipo.id`, `periodo.id`, `download.arquivo`, `obj.id`, `lan.id`, `acao.tipo`), passou a ser usado como `:key`. Nos casos sem id (ex.: `por_pagina` com números, `v-for="(qnt, index) in 10"`), manteve-se `:key="index"`.
3. **Evolução:** Para novos componentes, considerar `<script setup>` e Composition API quando fizer sentido; não é obrigatório para estar “no formato Vue 3”.

---

## 4. Conclusão

Em todas as partes do projeto que usam Vue, o **formato está correto para Vue 3**:

- Bootstrap via `createApp` + `mount`.
- Sem APIs removidas (filters, $listeners, $children, .native, $set, event bus, etc.).
- Slots com `v-slot`.
- Lifecycle e uso de componentes compatíveis com Vue 3.

A exposição de `window.Vue` em `app.js` foi removida; o uso de `:key` estável em v-for foi aplicado onde há id disponível (ver secção 3).

---

*Análise realizada com base em buscas no código em `resources/js` (arquivos .vue e .js).*
