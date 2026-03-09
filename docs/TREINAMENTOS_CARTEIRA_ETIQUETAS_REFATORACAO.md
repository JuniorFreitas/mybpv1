# Refatoração Treinamentos Carteira/Etiquetas — Vue 3 Component

Documento de referência da migração da tela Treinamentos (Carteira/Etiquetas) de Blade + app.js inline para componente Vue 3, com constantes e modais preparadas para várias.

## Objetivos

- Componente Vue 3 (`defineComponent`) como ponto único de entrada da tela.
- Modais preparadas para mais de uma: abertura/fechamento via `abrirModal(refName)` e `fecharModal(refName)`.
- Constantes para refs das 5 modais e paths de API (sem magic strings).
- Blade enxuta; app.js apenas monta o componente.
- Integridade dos fluxos preservada (treinamento individual, massa, enviar carteira, enviar aviso, filtro colunas, exportação, paginação).

## Constantes (constants.js)

- **REFS_MODAL**: `modalFiltroColunas`, `janelaTreinamento`, `janelaTreinamentoMassa`, `janelaEnviar`, `janelaEnviarAviso`
- **MODAL_IDS**: ids das modais (filtroColunas, janelaTreinamento, etc.)
- **API_PATHS**: vencimentos-por-segmento, carteiras, editar, store, salvar-massa, enviar-carteira, proximovencimento, uploadAnexos, export; segmentos habilitados (cadastro)

## Helpers de modal (no container)

- `abrirModal(refName)`: `this.$refs[refName]?.abrirModal?.()`
- `fecharModal(refName)`: `this.$refs[refName]?.fecharModal?.()`

## Modais

1. **filtroColunas** — Mostrar e Ocultar Treinamentos (`listaColunasTreinamentos`)
2. **janelaTreinamento** — Formulário treinamento individual (`form`, `formDefault`)
3. **janelaTreinamentoMassa** — Treinamento em massa (`formMassa`)
4. **janelaEnviar** — Enviar Carteira e Etiqueta (`formEnviar`)
5. **janelaEnviarAviso** — Próximo vencimento e-mail (`formEnviarAviso`)

## Fluxos preservados

- Listagem: `controle`, `lista`, `carregou`/`carregando`/`atualizar`, ControlePaginacao ref `componente`.
- Treinamento individual: `formAlterar(curriculo_id)` → GET editar → abrir modal; `salvar()` → POST treinamento → fechar e atualizar.
- Treinamento massa: abrir modal massa, `salvarMassa()` → POST salvar-massa.
- Enviar carteira: `abriJanelaEnviar(obj)` → `enviar()` → POST enviar-carteira.
- Enviar aviso: `abriJanelaEnviarAviso()` → `enviarAviso()` → POST proximovencimento.
- Exportação: ExportacaoMixin, `paramsExport`, `urlExportacao`.

## Arquivos

- `resources/js/components/treinamentos-carteira-etiquetas/constants.js`
- `resources/js/components/treinamentos-carteira-etiquetas/TreinamentosCarteiraEtiquetas.vue`
- `resources/views/g/treinamentos/index.blade.php` (simplificado)
- `resources/js/g/treinamentos/app.js` (apenas mount)
