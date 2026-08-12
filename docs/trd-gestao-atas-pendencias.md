# TRD: Gestao de Atas e Pendencias

## Objetivo

Evoluir o modulo atual de `Ata Reuniao` do MyBP para controlar reunioes,
atas, aprovacoes, pendencias, notificacoes automaticas, acesso por ata,
historico e relatorios, sem criar uma nova aplicacao.

O modulo usa a stack atual do MyBP: Laravel 12, Vue 3, Gates por
`habilidades`, tenant por `empresa_id`, jobs, scheduler, Horizon, storage do
Laravel, e templates de e-mail Blade.

## Decisoes confirmadas

- O modulo atual `Administracao > Ata Reuniao` sera evoluido.
- Convidados externos acessam por link assinado com expiracao de 24 horas.
- O MVP de aprovacao suporta aprovacao individual e paralela.
- Supabase, Supabase Auth, Supabase Storage, e RLS nativos nao serao usados.
- O controle equivalente a RLS sera aplicado por tenant, ACL, servicos de
  acesso, validacao backend, indices, e filtros obrigatorios por `empresa_id`.

## Estado atual

O MyBP ja possui uma area legada de atas com estes arquivos principais:

- `app/Http/Controllers/AtaReuniaoController.php`
- `app/Models/AtaReuniao.php`
- `app/Models/AtaReuniaoAssunto.php`
- `app/Models/AtaReuniaoParticipante.php`
- `app/Models/AtaReuniaoAcao.php`
- `app/Models/AtaReuniaoTipo.php`
- `resources/js/components/administracao/atareuniao/AtaReuniao.vue`
- `resources/views/pdf/administracao/atareuniao/atareuniao.blade.php`

A estrutura atual cobre cadastro basico de local, datas, assuntos,
participantes livres, comentarios e acoes. Ela nao cobre aprovacao,
versionamento, acesso granular, notificacao D-2, escalonamento, anexos,
comentarios, dashboard ou compartilhamento externo.

## Requisitos funcionais do MVP

- Gerar codigo unico por empresa para cada ata.
- Registrar titulo, objetivo, status, nivel de acesso, confidencialidade,
  organizador e redator.
- Controlar acesso por ata para proprietario, editor, aprovador, leitor,
  participante, responsavel por pendencia e convidado externo.
- Bloquear acesso direto por URL quando o usuario nao tem permissao.
- Registrar pendencias com responsavel interno, prazo, status, prioridade e
  conclusao.
- Enviar e-mail automatico D-2 para pendencias abertas.
- Nao enviar D-2 para pendencias concluidas, canceladas ou sem responsavel.
- Recalcular D-2 quando o prazo muda.
- Atualizar pendencias vencidas para `atrasada`.
- Registrar notificacoes enviadas e erros de envio.
- Bloquear edicao comum de atas aprovadas.
- Permitir link externo assinado com validade de 24 horas e revogacao.

## Requisitos nao funcionais

- Todas as consultas devem respeitar `empresa_id`.
- Queries em jobs e commands devem filtrar explicitamente por `empresa_id`
  quando usarem `withoutGlobalScopes()` ou Query Builder.
- Listagens devem usar paginacao.
- Dashboards e relatorios devem usar Query Builder para agregacoes.
- E-mails devem rodar por fila.
- Logs nao devem gravar dados sensiveis desnecessarios.
- Historicos de aprovacao, prazo, responsavel, notificacao e acesso nao devem
  ser apagados silenciosamente.

## Arquitetura proposta

### Backend

- Controllers orquestram requests e respostas.
- Services concentram regras de acesso, codigo unico, notificacao e D-2.
- Models representam entidades Eloquent com `$table`, `$fillable` e `$casts`.
- Commands executam rotinas automaticas sem depender de usuario autenticado.
- Jobs enviam e-mails e registram sucesso ou falha.

### Frontend

- O componente atual `AtaReuniao.vue` sera evoluido gradualmente.
- A experiencia deve usar abas para identificacao, participantes, pauta,
  decisoes, pendencias, aprovacao e historico.
- O frontend pode ocultar acoes por permissao, mas o backend sempre valida.

## Modelo de dados

### Tabela `ata_reuniaos`

A tabela legada sera evoluida com campos de dominio:

- `codigo`
- `uuid_publico`
- `titulo`
- `objetivo`
- `status`
- `nivel_acesso`
- `classificacao_confidencialidade`
- `organizador_id`
- `redator_id`
- `aprovacao_modo`
- `versao_atual`
- `aprovada_em`
- `publicada_em`
- `bloqueada_em`
- `cancelada_em`
- `timezone`
- `link_videoconferencia`
- `observacoes`
- `deleted_at`

Indices essenciais:

- `UNIQUE (empresa_id, codigo)`
- `UNIQUE (uuid_publico)`
- `(empresa_id, status, data_inicio)`
- `(empresa_id, organizador_id, status)`
- `(empresa_id, redator_id, status)`

### Tabela `ata_reuniao_acaos`

A tabela legada de acoes sera evoluida como base de pendencias no MVP para
evitar duplicidade entre acoes antigas e pendencias novas.

Campos novos:

- `empresa_id`
- `titulo`
- `descricao`
- `responsavel_id`
- `criado_por`
- `prioridade`
- `percentual_conclusao`
- `evidencia_esperada`
- `data_conclusao`
- `validador_id`
- `validado_em`
- `deleted_at`

Indices essenciais:

- `(empresa_id, status, prazo, responsavel_id)`
- `(empresa_id, responsavel_id, status, prazo)`
- `(empresa_id, ata_reuniao_id, status)`

### Tabela `ata_reuniao_acessos`

Controla acesso granular de usuarios internos.

Campos principais:

- `empresa_id`
- `ata_reuniao_id`
- `user_id`
- `papel`
- `origem`
- `expira_em`
- `revogado_em`

Papeis suportados:

- `proprietario`
- `editor`
- `aprovador`
- `leitor`
- `participante`
- `responsavel_pendencia`

### Tabela `ata_reuniao_aprovacoes`

Registra aprovadores e decisoes do MVP.

Campos principais:

- `empresa_id`
- `ata_reuniao_id`
- `aprovador_id`
- `versao`
- `status`
- `decisao`
- `comentario`
- `respondido_em`

### Tabela `ata_reuniao_versoes`

Registra snapshots de versao.

Campos principais:

- `empresa_id`
- `ata_reuniao_id`
- `numero`
- `autor_id`
- `descricao`
- `campos_alterados`
- `snapshot`

### Tabela `ata_reuniao_notificacoes`

Funciona como outbox e historico de notificacoes.

Campos principais:

- `empresa_id`
- `ata_reuniao_id`
- `ata_reuniao_acao_id`
- `destinatario_id`
- `canal`
- `tipo`
- `modo_disparo`
- `status`
- `data_prazo_referencia`
- `destinatario_nome`
- `destinatario_email`
- `assunto`
- `payload`
- `erro`
- `enviado_em`

Indice anti-duplicidade D-2:

- `UNIQUE (empresa_id, ata_reuniao_acao_id, destinatario_id, tipo, data_prazo_referencia)`

### Tabela `ata_reuniao_compartilhamentos_externos`

Controla links externos assinados.

Campos principais:

- `empresa_id`
- `ata_reuniao_id`
- `token_hash`
- `nome_externo`
- `email_externo`
- `escopo`
- `criado_por`
- `expira_em`
- `revogado_em`
- `ultimo_acesso_em`

Regras:

- O token bruto nunca deve ser salvo.
- O link expira em 24 horas.
- O link pode ser revogado antes da expiracao.
- O acesso externo deve registrar auditoria.

### Tabela `ata_reuniao_eventos`

Registra eventos de dominio complementares ao activity log.

Campos principais:

- `empresa_id`
- `ata_reuniao_id`
- `ator_id`
- `ator_tipo`
- `tipo_evento`
- `entidade_tipo`
- `entidade_id`
- `dados`

## Fluxo de acesso

1. O usuario precisa ter a habilidade base `administracao_atareuniao`.
2. O backend localiza a ata no tenant do usuario.
3. O service de acesso verifica papel direto, participante, aprovador,
   responsavel por pendencia, criador, organizador, redator ou privilegio
   administrativo.
4. O sistema retorna `403` quando nao houver acesso.
5. Links externos seguem fluxo separado por token assinado e expiracao.

## Fluxo D-2

1. O scheduler executa `mybp:ata-pendencias` diariamente.
2. O command identifica empresas com pendencias abertas.
3. O service calcula pendencias com prazo em dois dias corridos.
4. Para cada pendencia elegivel, o sistema cria uma notificacao com chave unica.
5. Se a notificacao ja existe para o mesmo prazo, o sistema nao reenvia.
6. O job envia o e-mail e atualiza status para `enviado` ou `erro`.
7. Pendencias vencidas e abertas mudam para `atrasada`.

## Fluxo de aprovacao MVP

1. O organizador seleciona um ou mais aprovadores.
2. O sistema cria aprovacoes pendentes em modo `individual` ou `paralela`.
3. Cada aprovador registra decisao e comentario.
4. Quando a ata for aprovada, o sistema bloqueia edicao comum.
5. Alteracoes posteriores exigem nova versao ou reabertura autorizada.

## Fases de entrega

### Fase 1: base segura

- Corrigir permissao de update do controller legado.
- Adicionar campos base e indices.
- Criar modelos e services de acesso, codigo unico e notificacoes.
- Proteger `edit()` e `pdf()` contra acesso direto sem autorizacao.
- Criar command/job/e-mail para D-2 MVP.
- Criar testes dos fluxos criticos iniciais.

### Fase 2: UX de ata completa

- Evoluir a tela atual com abas e campos estruturados.
- Adicionar pauta, discussoes, decisoes, participantes vinculados e pendencias.

### Fase 3: aprovacao e versionamento completo

- Implementar fluxo de aprovacao na interface.
- Criar versoes e bloqueios visiveis.

### Fase 4: dashboards e relatorios

- Criar Minhas Atas, Minhas Pendencias e dashboard.
- Implementar relatorios e exportacoes assíncronas quando necessario.

### Fase 5: compartilhamento externo e anexos

- Implementar link externo 24 horas.
- Adicionar anexos protegidos, comentarios e ciencia.

## Criterios de aceite iniciais

- Usuario sem acesso direto recebe `403` ao abrir uma ata restrita.
- Pendencia com vencimento em D-2 gera notificacao e job de e-mail.
- Pendencia concluida nao gera e-mail D-2.
- Alteracao de prazo permite nova notificacao para a nova data.
- Pendencia vencida muda para `atrasada`.
- Ata aprovada bloqueia edicao comum.
- Responsavel interno por pendencia recebe acesso minimo necessario ou a
  atribuicao e bloqueada com erro claro.
