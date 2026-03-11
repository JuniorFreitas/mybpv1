# Refatoração Controle de Exames — Vue 3 Component

Documento de referência da migração da tela Controle de Exames de Blade + app.js inline para componente Vue 3, com constantes e helpers reutilizáveis.

## Objetivos

- Componente Vue 3 (`defineComponent`) como ponto único de entrada da tela.
- Modais preparadas para mais de uma: abertura/fechamento via `abrirModal(refName)` e `fecharModal(refName)`.
- Constantes para IDs de modais, refs e URLs (sem magic strings).
- Blade enxuta; app.js apenas monta o componente.
- Integridade dos dados e fluxos preservados (carregaResposta, salvaUpdate, resultado, salvaResultado, atualizar).

## Constantes (constants.js)

- **REFS_MODAL**: `janelaParecerEntrevista`, `modalValidaSesmt`, `modalFiltroColunas`
- **MODAL_IDS**: ids das modais para acessibilidade/SEO
- **URLs**: carregaResposta, salvaUpdate, resultado, salvaResultado, atualizar, anexo (base com URL_ADMIN)
- **Form defaults**: estruturas iniciais de `form` e `abasesmt.form` para `_.cloneDeep`

## Helpers de modal (no container)

- `abrirModal(refName)`: `this.$refs[refName]?.abrirModal?.()`
- `fecharModal(refName)`: `this.$refs[refName]?.fecharModal?.()`

Uso: `abrirModal(REFS_MODAL.JANELA_PARCER)`, `fecharModal(REFS_MODAL.VALIDA_SESMT)`.

## Dados e fluxos preservados

- Paginação: `controle.dados.porPagina` (e `pages` onde o filtro "Exibir" bindar) alinhado a ControlePaginacao.
- Formulário encaminhar: `form`, `formDefault`, `formulario_id`, `historico`, `listaPcmsos`, `listaExameTipos`, `listaEmpresasExames`.
- Formulário resultado SESMT: `abasesmt.form`, `abasesmt.formDefault`, `abasesmt.tituloJanela`, `abasesmt.preload`.
- Filtro: `controle.dados` (filtroPeriodo, periodo, campoBusca, campoCPF, status, pages, campoCnpj, campoCentroCusto).
- Lista e listas auxiliares: `lista`, `lista_ccs`, `carregou`/`carregando`/`atualizar`.

## Arquivos alterados/criados

- `resources/js/components/controle-exames/constants.js` (novo)
- `resources/js/components/controle-exames/ControleExames.vue` (novo) — template migrado do Blade; script com defineComponent, REFS_MODAL, API_PATHS, abrirModal(refName)/fecharModal(refName)
- `resources/views/g/controle-exames/index.blade.php` (simplificado) — apenas `<controle-exames></controle-exames>`
- `resources/js/g/controle-exames/app.js` (apenas mount do componente e registerGlobals)

## Notas de implementação

- Paginação: `ControlePaginacao` recebe `:por-pagina="controle.dados.pages"` (mantido para compatibilidade com o payload enviado ao backend).
- Modal ValidaSesmt: aberta via `abrirModal(REFS_MODAL.MODAL_VALIDA_SESMT)` após `formResultado()` carregar os dados (sem `data-target`).
- `whatsappLiberado` e `authconfiguracao` vêm do mixin Configuracoes.
