# Changelog

Todas as mudanças notáveis neste projeto serão documentadas neste arquivo.

O formato é baseado em [Keep a Changelog](https://keepachangelog.com/pt-BR/1.0.0/),
e este projeto adere ao [Semantic Versioning](https://semver.org/lang/pt-BR/).

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
