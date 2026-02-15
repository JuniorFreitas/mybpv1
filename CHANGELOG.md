# Changelog

Todas as mudanças notáveis neste projeto serão documentadas neste arquivo.

O formato é baseado em [Keep a Changelog](https://keepachangelog.com/pt-BR/1.0.0/),
e este projeto adere ao [Semantic Versioning](https://semver.org/lang/pt-BR/).

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
