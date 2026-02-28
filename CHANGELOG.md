# Changelog

Todas as mudanças notáveis neste projeto serão documentadas neste arquivo.

O formato é baseado em [Keep a Changelog](https://keepachangelog.com/pt-BR/1.0.0/),
e este projeto adere ao [Semantic Versioning](https://semver.org/lang/pt-BR/).

## [1.3.0] - 2026-02-28

### Adicionado

-   **Sistema de Assinatura Digital**

    -   Tabelas: `documento_para_assinatura`, `documento_assinatura_signatarios`, `documento_assinatura_eventos`
    -   Controllers: `DocumentoAssinaturaController`, `AssinaturaPublicaController`, `VerificacaoAssinaturaController`
    -   Jobs: `JobEnvioCodigoVerificacaoAssinatura`, `JobEnvioDocumentoAssinado`, `JobProcessarEnvioAssinatura`, `JobFinalizarDocumentoAssinado`
    -   Mails: `CodigoVerificacaoAssinaturaMail`, `DocumentoAssinadoConcluidoMail`, `DocumentoParaAssinaturaMail`
    -   Views para o fluxo de assinatura: validar CPF, validar código, assinar, concluído, expirado
    -   Componentes Vue: `DocumentoAssinatura.vue`, `AcaoAssinaturaDocumento.vue`

-   **Cotas e Alertas de Assinatura Digital**

    -   Campos em `cliente_configs`: `assinaturas_digital_habilitada`, `assinaturas_mensal`, `alertas_assinatura`
    -   Service `App\Services\AssinaturaDigital\AssinaturaCotaService`: controle de uso de cotas
    -   Job `JobEnviarAlertaCotaAssinatura`: verificação e envio de alertas quando 接近 limite
    -   Extrato mensal de assinaturas: nova view `extrato-mensal.blade.php`
    -   Middleware `VerificaAssinaturaDigitalHabilitada`: verifica se empresa tem assinatura digital ativada

-   **Segmento de Treinamento**

    -   Nova tabela `segmentos_treinamento`: cadastro de segmentos (ALUMAR, VALE, etc.)
    -   Relacionamento `cliente_segmento_treinamento`: vínculo Many-to-Many entre clientes e segmentos
    -   Coluna `segmento_treinamento_id` em `admissoes` e `vencimentos`
    -   Migration `add_segmento_treinamento_id_to_vencimentos_and_admissoes`
    -   Seed `seed_segmento_alumar_e_atribuir_existentes`: cria segmento ALUMAR e atribui a empresa existente

-   **Carteira de Treinamento por Segmento**

    -   Cabeçalhos e versos específicos por segmento: `cabecalho_carteira_vale.webp`, `verso_carteira_vale.webp`
    -   Assinaturas de carteira por segmento: campo `segmento_treinamento_id` em `carteira_assinaturas`
    -   Service `CarteiraImagemCache`: cache de imagens em base64 (TTL 30 dias)
    -   Resolução de assinatura por segmento no PDF da carteira

-   **Admissão Prevista – Filtro por Tipo de Contrato**

    -   Novo método para listar tipos de contrato no frontend
    -   Normalização de valores `tipo_contrato` no controller
    -   `AdmissoesPrevistaFilterApplier`: suporte a filtragem por tipo de contrato

-   **Melhorias na Integração com Documentos**
    -   Integração de assinatura digital com `CartaOferta`, `DemissaoPrevista`, `Dossie`, `Contrato`, `Historico`
    -   Marcação d'água em PDFs assinados via `PdfMarcaAssinaturaService`

### Modificado

-   **Carteira de Treinamento – Estrutura de Arquivos**

    -   Componente `AssinaturaCarteira.vue` movido para `resources/js/components/cadastros/treinamentoindustria/`
    -   Cadastro de assinatura agora em **Treinamento Indústria** (não mais em Treinamento SGI)

-   **Assinatura de Carteira – Resolução por Segmento**

    -   `Cliente::CarteiraAssinaturaSesmt()` e `CarteiraAssinaturaGestorRh()` consideram apenas assinaturas padrão (`whereNull('segmento_treinamento_id')`)
    -   `TreinamentoController::resolverAssinaturaCarteira()` busca primeiro assinatura do segmento, depois a padrão

-   **PDF Carteira – Cache de Imagens**

    -   Uso de `CarteiraImagemCache` para cache de imagens em base64
    -   `cabecalho_img_base64` e `verso_img_base64` no payload com fallback para `asset()`

-   **Admissão Prevista – Tipos de Contrato**
    -   Validação de `tipo_contrato` expandida para incluir tipos específicos de admissão

---

## [1.2.2] - 2026-02-24

### Adicionado

-   **Carteira de Treinamento – Assinaturas por segmento**

    -   Campo `segmento_treinamento_id` na tabela `carteira_assinaturas`: assinaturas podem ser **padrão** (null, todos os segmentos) ou **específicas** de um segmento (ALUMAR, VALE, etc.).
    -   No PDF da carteira, cada treinamento usa a assinatura do segmento quando existir; caso contrário, a assinatura padrão da empresa (SESMT e Gestor/RH).
    -   Cadastro de Assinatura Carteira (em Treinamento Indústria): novo campo **Segmento de treinamento** (opcional). Listagem exibe coluna Segmento (Padrão ou nome do segmento).
    -   Serviço `App\Services\Treinamento\CarteiraImagemCache`: cache de imagens da carteira em base64 (cabeçalho, verso, assinaturas) com TTL 30 dias; chaves incluem `filemtime` / `updated_at` para invalidação automática ao atualizar arquivos ou anexos.
    -   PDF da carteira passa a usar imagens em base64 a partir do cache quando disponível, reduzindo I/O e melhorando performance na geração do PDF.

-   **Migrations**
    -   `add_segmento_treinamento_id_to_carteira_assinaturas_table`: coluna `segmento_treinamento_id` (nullable, FK para `segmentos_treinamento`).
    -   `add_ordem_to_carteira_assinaturas_anexos_table`: coluna `ordem` na pivot para compatibilidade com `updateExistingPivot` no cadastro de assinaturas.

### Modificado

-   **Assinatura Carteira – Local no menu**

    -   Cadastro de **Assinatura Carteira** deixou de ficar em **Treinamento SGI** e passou a ficar em **Treinamento Indústria**: botão e modal no componente `TreinamentoIndustria.vue`; componente `AssinaturaCarteira.vue` movido para `resources/js/components/cadastros/treinamentoindustria/`.

-   **Carteira de Treinamento – Resolução de assinaturas**

    -   `Cliente::CarteiraAssinaturaSesmt()` e `CarteiraAssinaturaGestorRh()` passam a considerar apenas assinaturas **padrão** (`whereNull('segmento_treinamento_id')`).
    -   `TreinamentoController::resolverAssinaturaCarteira()` passa a buscar primeiro assinatura do segmento e depois a padrão da empresa; uso de `CarteiraImagemCache::assinaturaParaArray()` para retorno com base64 em cache.

-   **PDF Carteira – Imagens em base64 com cache**
    -   Cabeçalho e verso do segmento: preenchimento de `cabecalho_img_base64` e `verso_img_base64` no payload (via `CarteiraImagemCache::imagemPublicaParaBase64()`); view `cart_treinamento.blade.php` usa base64 quando presente, com fallback para `asset()`.

### Corrigido

-   **Cadastro de Assinatura Carteira**
    -   Erro _Unknown column 'ordem' in 'field list'_ ao atualizar assinatura: adicionada coluna `ordem` na tabela pivot `carteira_assinaturas_anexos` e uso de `ordem` no `attach` de novos anexos.

---

## [1.2.1] - 2026-02-21

### Modificado

-   **Demissão Prevista (SolicitacaoDemissao.vue)**

    -   Filtro e botões alinhados ao padrão do sistema: fieldset "Filtro" sem `mt-0`, grid com Período (col-3), Pesquisar (col-6), Status (col-3), Ordenar (col-3), Exibir (col-2); botões Atualizar (btn-success), Solicitar, EXPORTAR EXCEL e Atualizar Status no mesmo padrão de Aprovação Extra Config e Requisição de Vagas.

-   **Requisição de Vagas (RequisicaoVaga.vue)**
    -   Filtro e botões no mesmo layout da Demissão Prevista: date-range, Pesquisar, Status, Ordenar por em linha única; botões Atualizar, Solicitar e EXPORTAR EXCEL com mesma estrutura e estilos.
    -   Grid de listagem (cards) no mesmo padrão da Demissão Prevista: card com badge-id, data-info (data da solicitação), status-badge (REPROVADO, APROVADO RH, APROVADO [Extra], APROVADO GESTOR, EM ABERTO), botão de ações circular (btn-actions-compact), detalhes em detail-item e fluxo de aprovação (Solicitante → Gestor → [Extra] → RH) com fluxo-info em coluna e estilos unificados (incl. responsividade).

## [1.2.0] - 2026-02-20

### Adicionado

-   **Relatório NPS – Exportação Excel**

    -   Job `JobExportaNpsExcel` para exportação em chunks com notificação ao concluir
    -   Endpoint `POST /g/relatorios/nps/export` no `NpsController` para disparar a exportação
    -   Botão "Exportar Excel" no componente `NpsRelatorio.vue`
    -   Arquivo gerado enviado para S3 e link disponível via notificação

-   **Módulo NPS – Ciclos e estrutura**
    -   Migrations: `nps_perguntas`, `nps_respostas`, `nps_resposta_itens`, `nps_ciclos`
    -   Migration `add_nps_ciclo_id_to_nps_respostas` para vínculo de respostas ao ciclo

## [1.1.0] - 2026-02-11

### Adicionado

-   Sistema de Aprovação Extra Dinâmica para processos de RH

    -   Nova tabela `aprovacao_extra_configs` para configurações personalizadas
    -   Suporte para múltiplos tipos de processos (demissão, férias, mudança de cargo, etc.)
    -   Campos de aprovação extra em `demissao_previstas` e `ferias_previstas`
    -   Controller `AprovacaoExtraConfigController` com CRUD completo
    -   Model `AprovacaoExtraConfig` com métodos de validação e permissão
    -   Documentação completa em `/docs/README_APROVACAO_EXTRA.md`
    -   Exemplos de implementação e componente Vue.js
    -   API endpoints para gerenciamento de aprovações

-   Relatório de Avaliações

    -   Nova funcionalidade para exportação de avaliações completas
    -   Documentação em `/docs/EXPORTACAO_AVALIACOES_COMPLETA.md`

-   Melhorias no Deploy

    -   Implementação de deploy para AWS ECS
    -   Scripts de limpeza automática de imagens ECR
    -   Solução para duplicação de jobs no ECS
    -   Documentação de deploy atualizada em `/docs/README-DEPLOY.md`

-   Importações de dados
    -   Importação Montisol (setembro/2025)
    -   Importação Maxtec (2025)

### Modificado

-   **Mudança de Cargo – Aprovação**

    -   Retorno de "aprovado por" com dados mínimos (id, nome) e query otimizada no `edit()` e `atualizar()`, alinhado ao padrão da Requisição de Vaga.
    -   `MudancaCargoController`: eager load restrito no `edit()` (apenas id,nome para GestorAprovacao, RhAprovacao, AprovacaoExtra, etc.); `atualizar()` mapeia itens e retorna `toArray()` com `aprovacao_extra_nome`.
    -   Frontend (SolicitacaoMudaCargo.vue) exibe `gestor_aprovacao.nome` e `rh_aprovacao.nome` no modal e na listagem.

-   **Solicitação de Demissão**

    -   Bloco de Filtro alinhado ao padrão da SolicitacaoAdmissao (formatação compacta, botão "Atualizar Status" ativo).

-   **CIH (Apontamento)**

    -   Filtro de período passou a usar o componente `DateRangeFilter` no lugar do checkbox + datepicker range.
    -   Período inicia desligado; ao ativar, preenche automaticamente com primeiro e último dia do mês atual.
    -   Busca automática ao ativar o período e ao alterar as datas (com debounce de 150 ms).
    -   Sincronização de `periodo` (formato DD/MM/YYYY até DD/MM/YYYY) a partir de `dataInicio`/`dataFim` para compatibilidade com o backend.

-   Melhorias no sistema de treinamento

    -   Ajustes no fluxo de treinamento
    -   Documentação atualizada em `MUDANCAS_COMANDO_TREINAMENTO.md`

-   Melhorias no fluxo de RH
    -   Atualização do fluxo de aprovações
    -   Documentação em `/docs/ATUALIZACAO_FLUXO_RH.md`

### Corrigido

-   Ajustes na funcionalidade de demissão prevista
-   Correções no módulo de recrutamento
-   Ajustes no kernel para melhor performance
-   Correções nos jobs em background
-   Melhorias no sistema de logs

## Versões Anteriores

### Laravel 8.x

-   Framework base Laravel 8.12+
-   PHP 8.2+
-   Suporte a multi-tenancy
-   Sistema de autenticação com Sanctum
-   Laravel Horizon para gerenciamento de filas
-   Websockets com Laravel WebSockets
-   Integração com AWS S3
-   Exportação e importação com Maatwebsite Excel
-   Sistema de logs de atividades com Spatie Activity Log
-   Geração de PDFs com DomPDF

### Funcionalidades Principais

-   Gestão de RH completa
-   Sistema de avaliações
-   Gestão de treinamentos
-   Controle de férias e demissões
-   Gestão de colaboradores
-   Sistema de permissões e privilégios
-   Relatórios e exportações
-   Notificações em tempo real
