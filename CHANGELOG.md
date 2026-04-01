# Changelog

Todas as mudanças notáveis neste projeto serão documentadas neste arquivo.

O formato é baseado em [Keep a Changelog](https://keepachangelog.com/pt-BR/1.0.0/),
e este projeto adere ao [Semantic Versioning](https://semver.org/lang/pt-BR/).

## [2.0.4] - 2026-04-01

### Adicionado

- **Avaliacao de Desempenho - notificacoes e rastreabilidade**
    - Nova infraestrutura de notificacoes de avaliacao via fila (`Job`) para proxima etapa, lembrete manual e lembrete por prazo.
    - Novo command `mybp:avaliacao-pendencias` com suporte a D-3, D-2, D-1 e no dia do vencimento.
    - Nova tabela `avaliacoes_notificacoes` para rastrear canal, modo de disparo, tipo, status, payload, erro e data de envio.
    - Novo command `mybp:encerrar-avaliacoes-vencidas` para encerrar automaticamente avaliacoes abertas cujo prazo ja passou.

### Modificado

- **Avaliacao de Desempenho - nova experiencia por colaborador**
    - Tela `Minhas Avaliações` reorganizada por colaborador, com fluxo horizontal compacto, percentual de conclusao, destaque da etapa atual, visualizacao contextual por permissao e ordenacao priorizando o colaborador logado.
    - `PDI` passou a ser tratado como etapa extra de `Plano de ação`, fora do fluxo obrigatorio da avaliacao.
    - Filtro `Fluxo da avaliação` passou a refletir o passo a passo do fluxo e substituiu o uso do filtro de status na tela.
    - Filtros `Avaliador`, `Colaborador`, `Como` e `Fluxo da avaliação` passaram a ser processados no backend.
    - Usuarios sem RH passaram a ver apenas sua etapa no fluxo; colaborador autenticado vendo a propria avaliacao continua vendo o fluxo completo.

- **Avaliacao de Desempenho - acoes e permissoes**
    - Regras dos botoes do novo layout foram realinhadas com a listagem anterior, incluindo `Avaliar`, `Visualizar`, `Plano de ação (PDI)`, `Acompanhar PDI` e `Imprimir`.
    - Botao `Visualizar sua avaliação` adicionado para o usuario que ja concluiu sua etapa e nao possui privilegio de RH.
    - Botoes do card e das etapas foram padronizados com o estilo de `Atualizar lista` e ganharam icones.

- **Avaliacao de Desempenho - e-mails**
    - E-mail de notificacao reformulado com texto mais formal, bloco de destaque para dados principais e tratamento especifico para autoavaliacao.
    - Removida a informacao de quem enviou o lembrete manual no corpo do e-mail.

### Corrigido

- **Avaliacao de Desempenho - regras e estados do fluxo**
    - Fluxo concluido passou a considerar apenas as etapas obrigatorias da avaliacao; `PDI` nao entra no calculo do `100% concluído`.
    - Ajustadas regras visuais do card concluido para nao exibir proxima etapa nem prazo final quando o fluxo ja terminou.
    - Corrigidas permissoes para impedir exibicao do botao `Avaliar` em etapas nao acionaveis para o usuario.
    - Corrigido o icone do ultimo passo concluido para exibir `check` corretamente.
    - Corrigido `mybp:avaliacao-pendencias` para interpretar datas brasileiras e comparar corretamente a diferenca de dias no lembrete por prazo.

## [2.0.3] - 2026-03-12

### Corrigido

- **Solicitação de transferência – botão Salvar RH**
    - Botão "Salvar RH" deixava de aparecer ao abrir aprovação pelo RH; condição de exibição passou a usar `!form.user_rh_id` em vez de `!form.resposta_rh`, alinhado ao critério do dropdown.

### Modificado

- **Solicitação de transferência – Vue 3 e UX**
    - Componente `SolicitacaoTransferencia.vue` migrado para Vue 3 Composition API (`defineComponent` + `setup`), chamadas axios em async/await com try/catch/finally, constantes e funções reutilizáveis (Clean Code).
    - Autocomplete de colaboradores (`AutoCompletesController::colaboradores`): resposta passa a incluir `curriculo_id` e `centro_custo_id` na raiz do item para uso no frontend.

- **Solicitação de transferência – Centro de Custo Origem**
    - Ao solicitar nova transferência, ao selecionar o colaborador o campo "Centro de Custo Origem" é preenchido automaticamente com o centro de custo atual do colaborador (quando possuir).
    - Campo "Centro de Custo Origem" inicia desabilitado e só é habilitado quando o colaborador selecionado não possui centro de custo, para o usuário escolher manualmente.

## [2.0.2] - 2026-03-11

### Adicionado

- **Importação de admissões em planilha**
    - Comando `php artisan admissao:importar` para processar planilha Excel/XLSX de admissões em fila.
    - Comando `php artisan admissao:planilha-exemplo` para gerar planilha de exemplo.
    - Job `ImportacaoAdmissaoJob`: processamento assíncrono; envio de e-mail ao concluir (`ImportacaoConcluidaMail`).
    - Serviços de importação: `LeitorPlanilhaAdmissao`, `ValidadorLinhaPlanilhaAdmissao`, `MapperLinhaPlanilhaParaPayload`, `ResolvedorVagaAreaCentroCusto`, `PersistidorAdmissaoImportada`.
    - Tela e componente Vue para upload e acompanhamento da importação (`/g/admissao/import`).
    - Documentação em `docs/importacao/IMPORTACAO_ADMISSOES.md` e guia em PDF.
    - Traduções em `lang/pt_BR/importacao_admissao.php`.

- **Script SQL – centro de custo para CIH**
    - Script `docs/scripts/popular_centro_custo_cih_empresa_40568.sql`: preenche `centro_custo_id` em CIHs da empresa 40568 a partir do centro de custo da admissão dos colaboradores vinculados; inclui opção para espelhar CIH → admissão.

## [2.0.1] - 2026-03-11

### Corrigido

- **PDFs de entrevista – variável indefinida e exibição de escolaridade**
    - Views de ficha PDF (Parecer RH, Parecer Rota, Entrevista Técnica, Teste Prático): substituído uso de `$item` por `$dados` na linha de Contato (`Curriculo::getTelPrincipal`), pois `$item` só existe dentro de loops e o controller envia apenas `$dados`.
    - Escolaridade em todas as fichas de entrevista (parecer_rh, parecer_rota, teste_pratico, entrevista_tecnica, entrevista_rh): corrigida interpolação em PHP que exibia o objeto Curriculo em JSON em vez do curso; passou a usar concatenação para exibir apenas "Tipo (curso)", ex.: "Pós Graduação (GESTÃO DE PESSOAS)".

## [2.0.0] - 2026-03-10

### Modificado

- **Frontend Vue 3 e documentação**
    - Stack do projeto utiliza Vue 3 + Laravel Mix; documentação de conformidade Vue 3 e plano de migração gradual para Composition API + Services em `docs/` (ex.: `ANALISE_VUE3_CONFORMIDADE.md`, `PLANO_MIGRACAO_COMPOSITION_API_SERVICES.md`, agente `agents/migracao-frontend/README.md`).

### Corrigido

- **Exportação de recrutamento – erro de foreign key em histórico**
    - Ação "exportado" em `recrutamento_historicos` era registrada com `curriculo_id = 0`, violando a FK para `curriculos`.
    - Migration `make_curriculo_id_nullable_in_recrutamento_historicos_table`: coluna `curriculo_id` em `recrutamento_historicos` passou a aceitar `NULL`.
    - Model `RecrutamentoHistorico`: `registrar()` aceita `?int $curriculoId`; `gerarDescricao()` trata `curriculo_id` nulo (ex.: "Exportação em massa realizada por {usuário}" ou descrição armazenada).
    - `RecrutamentoController::export()` passa a chamar `RecrutamentoHistorico::registrar(null, ...)` em vez de `registrar(0, ...)`.

## [1.3.0] - 2026-03-02

### Adicionado

- **Treinamento – Segmento e Padrão de Treinamento**
    - Filtro por segmento no relatório de vencimento (frontend + backend)
    - Campo "Padrão de treinamento" no formulário de Treinamentos (seleção de segmento)
    - Endpoint `POST /g/treinamento/vencimentos-por-segmento` para atualizar lista de vencimentos por segmento
    - Segmento exibido no relatório e nas notificações de vencimento

- **Configuração por Empresa (Clientes)**
    - Campo `schedule_treinamento_vencimento` em `cliente_configs` para habilitar/desabilitar o schedule
    - Controle no cadastro de clientes para o schedule de Treinamento Vencimento

- **Sistema de Assinatura Digital**
    - Tabelas: `documento_para_assinatura`, `documento_assinatura_signatarios`, `documento_assinatura_eventos`
    - Controllers: `DocumentoAssinaturaController`, `AssinaturaPublicaController`, `VerificacaoAssinaturaController`
    - Jobs: `JobEnvioCodigoVerificacaoAssinatura`, `JobEnvioDocumentoAssinado`, `JobProcessarEnvioAssinatura`,
      `JobFinalizarDocumentoAssinado`
    - Mails: `CodigoVerificacaoAssinaturaMail`, `DocumentoAssinadoConcluidoMail`, `DocumentoParaAssinaturaMail`
    - Criação de documento de assinatura a partir de PDF existente
    - Campos de consentimento em assinaturas e documentos (migrations)
    - Suporte a apelido na verificação de assinatura digital e rotas atualizadas
    - Views para o fluxo de assinatura: validar CPF, validar código, assinar, concluído, expirado
    - Componentes Vue: `DocumentoAssinatura.vue`, `AcaoAssinaturaDocumento.vue`

- **Cotas e Alertas de Assinatura Digital**
    - Campos em `cliente_configs`: `assinaturas_digital_habilitada`, `assinaturas_mensal`, `alertas_assinatura`
    - Service `App\Services\AssinaturaDigital\AssinaturaCotaService`: controle de uso de cotas
    - Job `JobEnviarAlertaCotaAssinatura`: verificação e envio de alertas quando X limite
    - Extrato mensal de assinaturas: nova view `extrato-mensal.blade.php`
    - Middleware `VerificaAssinaturaDigitalHabilitada`: verifica se empresa tem assinatura digital ativada

- **Segmento de Treinamento**
    - Nova tabela `segmentos_treinamento`: cadastro de segmentos (ALUMAR, VALE, etc.)
    - Relacionamento `cliente_segmento_treinamento`: vínculo Many-to-Many entre clientes e segmentos
    - Coluna `segmento_treinamento_id` em `admissoes` e `vencimentos`
    - Migration `add_segmento_treinamento_id_to_vencimentos_and_admissoes`
    - Seed `seed_segmento_alumar_e_atribuir_existentes`: cria segmento ALUMAR e atribui a empresa existente

- **Carteira de Treinamento por Segmento**
    - Cabeçalhos e versos específicos por segmento: `cabecalho_carteira_vale.webp`, `verso_carteira_vale.webp`
    - Assinaturas de carteira por segmento: campo `segmento_treinamento_id` em `carteira_assinaturas`
    - Service `CarteiraImagemCache`: cache de imagens em base64 (TTL 30 dias)
    - Resolução de assinatura por segmento no PDF da carteira

- **Admissão Prevista – Filtro por Tipo de Contrato**
    - Novo método para listar tipos de contrato no frontend
    - Normalização de valores `tipo_contrato` no controller
    - `AdmissoesPrevistaFilterApplier`: suporte a filtragem por tipo de contrato

- **Melhorias na Integração com Documentos**
    - Integração de assinatura digital com `CartaOferta`, `DemissaoPrevista`, `Dossie`, `Contrato`, `Historico`
    - Marcação d'água em PDFs assinados via `PdfMarcaAssinaturaService`

### Modificado

- **Schedule Treinamento Vencimento**
    - Execução passa a respeitar `cliente_configs.schedule_treinamento_vencimento`
    - Comando agora usa lista de empresas habilitadas (com opção de executar em todas)

- **Relatórios e Exportações de Treinamento**
    - Inclusão da coluna "Padrão de Treinamento" nas planilhas
    - Relatório de vencimento filtra treinamentos por segmento do colaborador
    - E-mails de vencimento passam a exibir o segmento do colaborador

- **Treinamento – Performance e Payload**
    - Cache dos vencimentos ativos por empresa (com invalidação automática)
    - Eager loads reduzidos e payload da admissão enxugado na listagem
    - Ordenação priorizando admitidos (FeedbackCurriculoFilter)

- **Interfaces (Admin)**
    - Tela de Clientes reorganizada em cards e seções (incluindo bloco de Rotinas)
    - Tela de Treinamentos com seleção de padrão/segmento e ajustes no layout dos cards

- **Plataforma**
    - Upgrade para Laravel 12 com suporte a broadcasting via Reverb e Pusher

- **Carteira de Treinamento – Estrutura de Arquivos**
    - Componente `AssinaturaCarteira.vue` movido para `resources/js/components/cadastros/treinamentoindustria/`
    - Cadastro de assinatura agora em **Treinamento Indústria** (não mais em Treinamento SGI)

- **Assinatura de Carteira – Resolução por Segmento**
    - `Cliente::CarteiraAssinaturaSesmt()` e `CarteiraAssinaturaGestorRh()` consideram apenas assinaturas padrão (
      `whereNull('segmento_treinamento_id')`)
    - `TreinamentoController::resolverAssinaturaCarteira()` busca primeiro assinatura do segmento, depois a padrão

- **PDF Carteira – Cache de Imagens**
    - Uso de `CarteiraImagemCache` para cache de imagens em base64
    - `cabecalho_img_base64` e `verso_img_base64` no payload com fallback para `asset()`

- **Admissão Prevista – Tipos de Contrato**
    - Validação de `tipo_contrato` expandida para incluir tipos específicos de admissão

### Corrigido

- **Treinamento (Segurança e Consistência)**
    - `edit()` valida empresa do feedback e retorna 404 se não encontrado
    - Atualização de vencimentos passa a remover apenas os itens do segmento corrente

---

## [1.2.2] - 2026-02-24

### Adicionado

- **Carteira de Treinamento – Assinaturas por segmento**
    - Campo `segmento_treinamento_id` na tabela `carteira_assinaturas`: assinaturas podem ser **padrão** (null, todos os
      segmentos) ou **específicas** de um segmento (ALUMAR, VALE, etc.).
    - No PDF da carteira, cada treinamento usa a assinatura do segmento quando existir; caso contrário, a assinatura
      padrão da empresa (SESMT e Gestor/RH).
    - Cadastro de Assinatura Carteira (em Treinamento Indústria): novo campo **Segmento de treinamento** (opcional).
      Listagem exibe coluna Segmento (Padrão ou nome do segmento).
    - Serviço `App\Services\Treinamento\CarteiraImagemCache`: cache de imagens da carteira em base64 (cabeçalho, verso,
      assinaturas) com TTL 30 dias; chaves incluem `filemtime` / `updated_at` para invalidação automática ao atualizar
      arquivos ou anexos.
    - PDF da carteira passa a usar imagens em base64 a partir do cache quando disponível, reduzindo I/O e melhorando
      performance na geração do PDF.

- **Migrations**
    - `add_segmento_treinamento_id_to_carteira_assinaturas_table`: coluna `segmento_treinamento_id` (nullable, FK para
      `segmentos_treinamento`).
    - `add_ordem_to_carteira_assinaturas_anexos_table`: coluna `ordem` na pivot para compatibilidade com
      `updateExistingPivot` no cadastro de assinaturas.

### Modificado

- **Assinatura Carteira – Local no menu**
    - Cadastro de **Assinatura Carteira** deixou de ficar em **Treinamento SGI** e passou a ficar em **Treinamento
      Indústria**: botão e modal no componente `TreinamentoIndustria.vue`; componente `AssinaturaCarteira.vue` movido
      para `resources/js/components/cadastros/treinamentoindustria/`.

- **Carteira de Treinamento – Resolução de assinaturas**
    - `Cliente::CarteiraAssinaturaSesmt()` e `CarteiraAssinaturaGestorRh()` passam a considerar apenas assinaturas \*
      \*padrão\*\* (`whereNull('segmento_treinamento_id')`).
    - `TreinamentoController::resolverAssinaturaCarteira()` passa a buscar primeiro assinatura do segmento e depois a
      padrão da empresa; uso de `CarteiraImagemCache::assinaturaParaArray()` para retorno com base64 em cache.

- **PDF Carteira – Imagens em base64 com cache**
    - Cabeçalho e verso do segmento: preenchimento de `cabecalho_img_base64` e `verso_img_base64` no payload (via
      `CarteiraImagemCache::imagemPublicaParaBase64()`); view `cart_treinamento.blade.php` usa base64 quando presente,
      com fallback para `asset()`.

### Corrigido

- **Cadastro de Assinatura Carteira**
    - Erro _Unknown column 'ordem' in 'field list'_ ao atualizar assinatura: adicionada coluna `ordem` na tabela pivot
      `carteira_assinaturas_anexos` e uso de `ordem` no `attach` de novos anexos.

---

## [1.2.1] - 2026-02-21

### Modificado

- **Demissão Prevista (SolicitacaoDemissao.vue)**
    - Filtro e botões alinhados ao padrão do sistema: fieldset "Filtro" sem `mt-0`, grid com Período (col-3),
      Pesquisar (col-6), Status (col-3), Ordenar (col-3), Exibir (col-2); botões Atualizar (btn-success), Solicitar,
      EXPORTAR EXCEL e Atualizar Status no mesmo padrão de Aprovação Extra Config e Requisição de Vagas.

- **Requisição de Vagas (RequisicaoVaga.vue)**
    - Filtro e botões no mesmo layout da Demissão Prevista: date-range, Pesquisar, Status, Ordenar por em linha única;
      botões Atualizar, Solicitar e EXPORTAR EXCEL com mesma estrutura e estilos.
    - Grid de listagem (cards) no mesmo padrão da Demissão Prevista: card com badge-id, data-info (data da solicitação),
      status-badge (REPROVADO, APROVADO RH, APROVADO [Extra], APROVADO GESTOR, EM ABERTO), botão de ações circular (
      btn-actions-compact), detalhes em detail-item e fluxo de aprovação (Solicitante → Gestor → [Extra] → RH) com
      fluxo-info em coluna e estilos unificados (incl. responsividade).

## [1.2.0] - 2026-02-20

### Adicionado

- **Relatório NPS – Exportação Excel**
    - Job `JobExportaNpsExcel` para exportação em chunks com notificação ao concluir
    - Endpoint `POST /g/relatorios/nps/export` no `NpsController` para disparar a exportação
    - Botão "Exportar Excel" no componente `NpsRelatorio.vue`
    - Arquivo gerado enviado para S3 e link disponível via notificação

- **Módulo NPS – Ciclos e estrutura**
    - Migrations: `nps_perguntas`, `nps_respostas`, `nps_resposta_itens`, `nps_ciclos`
    - Migration `add_nps_ciclo_id_to_nps_respostas` para vínculo de respostas ao ciclo

## [1.1.0] - 2026-02-11

### Adicionado

- Sistema de Aprovação Extra Dinâmica para processos de RH
    - Nova tabela `aprovacao_extra_configs` para configurações personalizadas
    - Suporte para múltiplos tipos de processos (demissão, férias, mudança de cargo, etc.)
    - Campos de aprovação extra em `demissao_previstas` e `ferias_previstas`
    - Controller `AprovacaoExtraConfigController` com CRUD completo
    - Model `AprovacaoExtraConfig` com métodos de validação e permissão
    - Documentação completa em `/docs/README_APROVACAO_EXTRA.md`
    - Exemplos de implementação e componente Vue.js
    - API endpoints para gerenciamento de aprovações

- Relatório de Avaliações
    - Nova funcionalidade para exportação de avaliações completas
    - Documentação em `/docs/EXPORTACAO_AVALIACOES_COMPLETA.md`

- Melhorias no Deploy
    - Implementação de deploy para AWS ECS
    - Scripts de limpeza automática de imagens ECR
    - Solução para duplicação de jobs no ECS
    - Documentação de deploy atualizada em `/docs/README-DEPLOY.md`

- Importações de dados
    - Importação Montisol (setembro/2025)
    - Importação Maxtec (2025)

### Modificado

- **Mudança de Cargo – Aprovação**
    - Retorno de "aprovado por" com dados mínimos (id, nome) e query otimizada no `edit()` e `atualizar()`, alinhado ao
      padrão da Requisição de Vaga.
    - `MudancaCargoController`: eager load restrito no `edit()` (apenas id,nome para GestorAprovacao, RhAprovacao,
      AprovacaoExtra, etc.); `atualizar()` mapeia itens e retorna `toArray()` com `aprovacao_extra_nome`.
    - Frontend (SolicitacaoMudaCargo.vue) exibe `gestor_aprovacao.nome` e `rh_aprovacao.nome` no modal e na listagem.

- **Solicitação de Demissão**
    - Bloco de Filtro alinhado ao padrão da SolicitacaoAdmissao (formatação compacta, botão "Atualizar Status" ativo).

- **CIH (Apontamento)**
    - Filtro de período passou a usar o componente `DateRangeFilter` no lugar do checkbox + datepicker range.
    - Período inicia desligado; ao ativar, preenche automaticamente com primeiro e último dia do mês atual.
    - Busca automática ao ativar o período e ao alterar as datas (com debounce de 150 ms).
    - Sincronização de `periodo` (formato DD/MM/YYYY até DD/MM/YYYY) a partir de `dataInicio`/`dataFim` para
      compatibilidade com o backend.

- Melhorias no sistema de treinamento
    - Ajustes no fluxo de treinamento
    - Documentação atualizada em `MUDANCAS_COMANDO_TREINAMENTO.md`

- Melhorias no fluxo de RH
    - Atualização do fluxo de aprovações
    - Documentação em `/docs/ATUALIZACAO_FLUXO_RH.md`

### Corrigido

- Ajustes na funcionalidade de demissão prevista
- Correções no módulo de recrutamento
- Ajustes no kernel para melhor performance
- Correções nos jobs em background
- Melhorias no sistema de logs

## Versões Anteriores

### Laravel 8.x

- Framework base Laravel 8.12+
- PHP 8.2+
- Suporte a multi-tenancy
- Sistema de autenticação com Sanctum
- Laravel Horizon para gerenciamento de filas
- Websockets com Laravel WebSockets
- Integração com AWS S3
- Exportação e importação com Maatwebsite Excel
- Sistema de logs de atividades com Spatie Activity Log
- Geração de PDFs com DomPDF

### Funcionalidades Principais

- Gestão de RH completa
- Sistema de avaliações
- Gestão de treinamentos
- Controle de férias e demissões
- Gestão de colaboradores
- Sistema de permissões e privilégios
- Relatórios e exportações
- Notificações em tempo real
