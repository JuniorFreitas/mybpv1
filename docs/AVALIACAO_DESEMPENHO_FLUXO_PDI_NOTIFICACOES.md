# Avaliacao de Desempenho: Fluxo, PDI e Notificacoes

## Objetivo

Documentar as mudancas realizadas na tela de `Avaliacao de Desempenho`, no fluxo de `PDI`, nas regras visuais da listagem por colaborador e na infraestrutura de notificacoes manuais e automaticas.

## Escopo implementado

## 1. Nova leitura da tela por colaborador

- A listagem deixou de ser orientada por `feedback individual` e passou a ser orientada por `colaborador`.
- Cada card agora representa um colaborador dentro de uma avaliacao.
- Dentro do card, o fluxo configurado da avaliacao e exibido em etapas.
- O usuario pode ver os avaliadores de cada etapa, quem concluiu e quem ainda esta pendente.

### Regras visuais importantes

- RH com `privilegio_gestao_rh` ve o fluxo completo.
- O colaborador autenticado vendo a propria avaliacao ve o fluxo completo.
- Demais usuarios sem RH veem apenas ate a propria etapa no fluxo.
- Quando o usuario sem RH ja concluiu a propria etapa e nao tem mais acao pendente, o card resume para:
    - `Sua avaliacao foi realizada`
    - percentual concluido
    - botao `Visualizar sua avaliacao`

## 2. Timeline do fluxo

- A timeline passou para um formato horizontal e compacto.
- As etapas usam o fluxo configurado da avaliacao.
- A leitura do fluxo obrigatorio ficou separada do `PDI`.

### Regras do fluxo

- `100% concluido` considera apenas as etapas obrigatorias do fluxo da avaliacao.
- `PDI` nao e tratado como etapa obrigatoria do fluxo.
- Quando a avaliacao esta `Finalizada`, o fluxo e exibido como `Fluxo concluido`.

## 3. PDI como etapa extra, fora do fluxo obrigatorio

- O `PDI` foi tratado como uma etapa extra de `Plano de acao e oportunidades de melhoria`.
- O usuario principal pode criar/editar/acompahar o PDI apos a conclusao da avaliacao.
- O backend existente de salvamento foi reaproveitado.

### Regras de exibicao do PDI

- `Plano de acao pendente`: quando o fluxo obrigatorio concluiu, mas o fechamento/PDI ainda nao foi feito.
- `Plano de acao concluido`: quando a avaliacao esta `Finalizada`.
- A tela passou a usar textos coerentes com esse conceito:
    - `Plano de acao (PDI)`
    - `Acompanhar PDI`

## 4. Regras de botoes

As acoes do card novo passaram a respeitar as mesmas regras funcionais da listagem anterior:

- `Avaliar`
    - somente quando o feedback esta pendente e a etapa e realmente acionavel para o usuario
- `Visualizar`
    - quando o usuario pode acessar aquele feedback ja salvo
- `Plano de acao (PDI)`
    - quando a avaliacao chegou no ponto de abertura do PDI
- `Acompanhar PDI`
    - quando a avaliacao esta `Finalizada` e o usuario principal pode acompanhar o plano
- `Imprimir`
    - apenas quando a avaliacao esta `Finalizada`
    - respeitando as mesmas regras centrais da tela

### Observacao de seguranca

- O fluxo completo pode ser visivel no card, mas os botoes de acao dependem das permissoes reais do usuario.
- Isso evita expor `Avaliar` para etapas que nao pertencem ao usuario.

## 5. Filtros da tela

### Mantidos / adicionados

- Ano
- Avaliacao
- Fluxo da avaliacao
- Avaliador
- Colaborador
- Como

### Removido

- Filtro de `Status`

### Regra atual

- O filtro de `Fluxo da avaliacao` substitui o uso do filtro de status.
- Os filtros passaram a bater no backend.
- O frontend deixou de aplicar filtro local de fluxo.

### Textos do filtro de fluxo

- `Passo 1: falta autoavaliacao`
- `Passo 1: autoavaliacao concluida`
- `Passo 2: falta avaliacao do par`
- `Passo 2: avaliacao do par concluida`
- `Passo 3: falta avaliacao do gestor`
- `Passo 3: avaliacao do gestor concluida`
- `Fluxo concluido`
- `Acompanhamento plano de acao`

Para avaliacoes sem autoavaliacao:

- `Passo 1: falta avaliacao do gestor`
- `Passo 1: avaliacao do gestor concluida`
- `Fluxo concluido`
- `Acompanhamento plano de acao`

## 6. Ordenacao da listagem

- O colaborador logado aparece primeiro quando ele esta vendo a propria avaliacao como colaborador.
- Depois disso, os demais cards seguem em ordem alfabetica.
- A mesma intencao foi mantida no frontend para a ordenacao do agrupamento por colaborador.

## 7. Notificacoes de avaliacao

Foi criada uma infraestrutura nova para notificacoes da avaliacao, em background, com rastreabilidade.

### Tipos implementados

- Notificacao manual individual de etapa pendente
- Notificacao manual em lote para pendentes filtrados
- Notificacao automatica de proxima etapa quando uma avaliacao e concluida
- Notificacao automatica para o avaliador final quando o fluxo chega na etapa dele
- Lembretes automaticos por prazo:
    - D-3
    - D-2
    - D-1
    - no dia do vencimento

### Regras tecnicas

- Tudo via `Job` em fila
- Tudo usando o template padrao de e-mail do projeto
- Rotina automatica considera apenas avaliacoes com status `Aberta`
- Queries de background usam `withoutGlobalScopes()`
- Queries de background filtram por `empresa_id`
- As rotinas nao dependem de autenticacao

## 8. Rastreabilidade das notificacoes

Foi criada a tabela `avaliacoes_notificacoes`.

### Campos principais

- `empresa_id`
- `avaliacao_id`
- `avaliacao_feedback_id`
- `funcionario_id`
- `avaliador_id`
- `usuario_solicitante_id`
- `canal`
- `modo_disparo`
- `tipo`
- `status`
- `destinatario_nome`
- `destinatario_email`
- `destinatario_telefone`
- `assunto`
- `payload`
- `erro`
- `enviado_em`

### Finalidade

- Registrar quando a notificacao foi criada
- Registrar se foi manual ou automatica
- Registrar o meio de envio
- Deixar o modelo pronto para futuro envio via WhatsApp
- Registrar erro de envio quando houver falha no job

## 9. E-mail de notificacao

- O texto foi ajustado para um tom mais formal.
- O template agora destaca melhor:
    - avaliacao
    - colaborador
    - etapa
    - prazo final
- O e-mail nao informa mais quem disparou manualmente o lembrete.
- A autoavaliacao passou a ter texto proprio.

## 10. Rotinas agendadas relacionadas

### 10.1 Pendencias de avaliacao

- Command: `mybp:avaliacao-pendencias`
- Agendamento: diario, `07:00`
- Aceita `--data-base=` para simulacao/manual

### 10.2 Encerramento automatico de avaliacoes vencidas

- Command: `mybp:encerrar-avaliacoes-vencidas`
- Agendamento: diario, `00:10`
- Regra:
    - busca avaliacoes `Aberta`
    - com `data_fim_prazo < hoje`
    - faz `update` para `Encerrada` via `DB::update`
    - limpa cache por empresa

## 11. Arquivos principais envolvidos

### Frontend

- `resources/js/components/cadastros/avaliacoes/avaliar/index.vue`
- `resources/views/g/cadastros/avaliacoes/avaliar/index.blade.php`

### Backend de avaliacao

- `app/Http/Controllers/AvaliacaoController.php`
- `app/Models/AvaliacaoNotificacao.php`
- `app/Services/Avaliacoes/AvaliacaoNotificacaoService.php`

### Jobs / Mail / Views

- `app/Jobs/Avaliacoes/SendAvaliacaoPendenciaMailJob.php`
- `app/Mail/Avaliacoes/AvaliacaoPendenciaMail.php`
- `resources/views/email/avaliacoes/pendencia-fluxo.blade.php`

### Rotinas

- `app/Console/Commands/AvaliacaoPendenciasCommand.php`
- `app/Console/Commands/EncerrarAvaliacoesVencidasCommand.php`
- `app/Console/Kernel.php`

### Banco

- `database/migrations/2026_04_01_010000_create_avaliacoes_notificacoes_table.php`

## 12. Pendencias naturais futuras

- Tela/consulta de historico de notificacoes por avaliacao
- Reenvio manual com consulta ao historico
- Suporte a canal `whatsapp`
- Indicador visual na tela quando ja houve notificacao manual/automatica da etapa
