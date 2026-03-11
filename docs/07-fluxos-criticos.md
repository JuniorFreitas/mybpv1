# 07. Fluxos Criticos

> Convenções usadas neste documento
> - Confirmado no código: fluxo observado diretamente em controllers/services/jobs/models.
> - Inferido: lacuna preenchida por correlação entre arquivos.

## 1. Recrutamento e atualização de candidato

### Entrada

- Tela interna de recrutamento com dados de currículo, telefones e feedback.
- Atualização via `PUT/PATCH` no fluxo de `RecrutamentoController`.

### Processamento

- Normaliza flags do request.
- Atualiza `Curriculo`.
- Registra histórico antes/depois.
- Processa criação, edição e remoção de telefones.
- Processa feedback do candidato e flags de notificação.

### Persistência

- `curriculos`
- `telefone_curriculos`
- `feedback_curriculos`
- `recrutamento_historicos`

### Saída

- Retorno JSON 201 em sucesso.
- Histórico atualizado para rastreabilidade.

### Tratamento de erro

- Transação com `DB::beginTransaction()` e rollback.
- Log detalhado com usuário em caso de exceção.

### Arquivos envolvidos

- `app/Http/Controllers/RecrutamentoController.php`
- `app/Models/Curriculo.php`
- `app/Models/RecrutamentoHistorico.php`
- `app/Models/FeedbackCurriculo.php`

## 2. Resultado integrado -> exame -> abertura da admissão

### Entrada

- Dados consolidados das entrevistas e decisão de encaminhamento.

### Processamento

- Valida obrigatórios do resultado integrado.
- Cria `ResultadoIntegrado`.
- Cria `ExameFuncionario` se houver empresa de exame + PCMSO e não existir encaminhamento equivalente.
- Garante processo de admissão com status `ENCAMINHADO EXAME` quando ainda inexistente.
- Dispara notificação associada ao resultado.

### Persistência

- `resultado_integrados`
- `exame_funcionarios`
- `admissoes`

### Saída

- JSON 201.

### Tratamento de erro

- Transação com rollback.
- Log de erro com usuário.

### Arquivos envolvidos

- `app/Http/Controllers/ResultadoIntegradoController.php`
- `app/Models/ResultadoIntegrado.php`
- `app/Models/ExameFuncionario.php`
- `app/Models/Admissao.php`

## 3. Pré-admissão e upload público de documentos

### Entrada

- Candidato acessa URL pública por `apelido` da empresa.
- Autenticação por CPF + data de nascimento.

### Processamento

- Busca `Curriculo` sem global scopes.
- Exige que exista `Feedback` da empresa com `ResultadoIntegrado.documentos_entregue = true`.
- Carrega checklist documental por empresa.
- Permite atualização de dados básicos, telefones e anexos.

### Persistência

- `curriculos`
- `telefone_curriculos`
- `documentos_curriculos`
- `arquivos`

### Saída

- JSON com currículo e documentos exigidos.
- Página pública `documentos.index`.

### Tratamento de erro

- Validação de campos obrigatórios.
- Rollback nas atualizações.
- Exceções capturadas retornam erro 400.

### Arquivos envolvidos

- `routes/web.php` grupo `documentospreadmissao`
- `app/Http/Controllers/DocumentosPreAdmissaoController.php`
- `app/Models/DocumentosCurriculosAdmissaoEmpresa.php`
- `resources/js/documentos/app.js`

## 4. Carta oferta e assinatura digital pública

### Entrada

- Signatário acessa link público `/{apelido}/assinatura/{token}`.
- Fluxo de validação exige CPF e código temporário.

### Processamento

- Valida token contra empresa do apelido.
- Salva CPF se o signatário ainda não tiver CPF cadastrado.
- Gera código de verificação via cache + job de e-mail.
- Limita tentativas e cooldown de reenvio.
- Ao assinar, grava IP, user agent, geolocalização aproximada, consentimento e hash de evidência.
- Ao concluir todos os signatários, dispara job de pós-conclusão para gerar PDF com marca d'água e enviar documento assinado.

### Persistência

- `documento_para_assinatura`
- `documento_assinatura_signatarios`
- `documento_assinatura_eventos`
- `arquivos`

### Saída

- Views públicas: validação CPF, validação código, assinar, concluído, expirado.
- PDF original ou assinado para visualização.

### Tratamento de erro

- 404 para token inválido/cancelado.
- 403 para validação pendente.
- Resposta JSON ou redirect com mensagem em caso de CPF/código inválido.

### Arquivos envolvidos

- `routes/web.php`
- `app/Http/Controllers/AssinaturaPublicaController.php`
- `app/Services/AssinaturaDigital/AssinaturaDigitalService.php`
- `app/Models/DocumentoParaAssinatura.php`
- `app/Models/DocumentoAssinaturaSignatario.php`

## 5. Requisição de vaga com aprovação em cadeia

### Entrada

- Usuário autenticado registra requisição de vaga via tela de planejamento.

### Processamento

- Valida campos estruturais e campos custom obrigatórios.
- Persiste dados consolidados em `RequisicaoVagaMovimentacao`.
- Dispara `JobNotificacaoRecursiva`.
- Gestor aprova/reprova.
- Se houver aprovação extra ativa, o fluxo segue para a etapa extra.
- Após isso, RH aprova/reprova.

### Persistência

- `requisicao_vagas_movimentacao`
- `aprovacao_extra_configs`
- `exportacoes` em caso de export

### Saída

- JSON 201 em criação/aprovação.
- Export CSV assíncrono.

### Tratamento de erro

- Validação de sequência de aprovação.
- Rollback nas mudanças de status.
- Notificações reencadeadas por job.

### Arquivos envolvidos

- `app/Http/Controllers/RequisicaoVagaController.php`
- `app/Models/RequisicaoVagaMovimentacao.php`
- `app/Models/AprovacaoExtraConfig.php`
- `app/Jobs/RequisicaoVaga/JobNotificacaoRecursiva.php`
- `app/Jobs/JobExportaRequisicaoVaga.php`

## 6. Encaminhamento e resultado de exame ocupacional

### Entrada

- RH ou processo de pré-admissão informa empresa de exame, tipo/PCMSO, data e flags de envio.

### Processamento

- Cria `ExameFuncionario` com token.
- Pode enviar e-mail para clínica e colaborador.
- Pode enviar WhatsApp ao colaborador quando habilitado.
- Posteriormente registra resultado SESMT, anexos e vencimento.
- Se aprovado, marca apenas um resultado como atual.

### Persistência

- `exame_funcionarios`
- `examesesmts`
- `arquivos`

### Saída

- JSON de sucesso.
- PDF público de ficha de encaminhamento.

### Tratamento de erro

- Transações com rollback.
- Logs formatados.

### Arquivos envolvidos

- `app/Http/Controllers/ControleExameController.php`
- `app/Http/Controllers/PreAdmissaoController.php`
- `routes/web.php` grupo `publico`
- `app/Jobs/ControleExames/JobExame.php`

## 7. Convocação intermitente

### Entrada

- RH lança convocação com período, área, centro de custo e lista de colaboradores.

### Processamento

- Gera hash de convocação por colaborador.
- Cria registro `Intermitente`.
- Monta links públicos de resposta sim/não.
- Envia e-mail e/ou WhatsApp quando habilitado.
- Scheduler expira convocações sem resposta após o prazo.

### Persistência

- `intermitentes`
- `intermitente_prorrogacoes`
- `arquivos`

### Saída

- JSON de sucesso.
- Endpoint público de resposta em `routes/api.php`.

### Tratamento de erro

- Validação de lista mínima de colaboradores.
- Rollback em transação.

### Arquivos envolvidos

- `app/Http/Controllers/IntermitenteController.php`
- `app/Models/Intermitente.php`
- `routes/api.php`
- `app/Jobs/Rotinas/JobConvocacaoIntermitente.php`
- `app/Jobs/JobConvocacaoIntermitentes.php`

## 8. Ponto eletrônico

### Entrada

- Usuário autenticado registra ponto com foto capturada pelo frontend.

### Processamento

- Frontend inicializa mapa, captura geolocalização e compara com perímetros.
- Backend exige foto e escala vinculada.
- Se houver período em aberto, fecha saída; caso contrário abre novo período.
- Salva latitude/longitude, foto e duração em minutos.
- Recalcula durações acumuladas do dia.

### Persistência

- `ponto_eletronicos`
- `periodo_ponto_eletronicos`
- `arquivos`

### Saída

- JSON com registros do dia, duração da jornada e minutos trabalhados.

### Tratamento de erro

- Rollback em exceção.
- Exclusão do arquivo salvo se falhar a persistência.

### Arquivos envolvidos

- `app/Http/Controllers/PontoEletronicoController.php`
- `app/Models/PontoEletronico.php`
- `resources/js/g/controle-ponto/ponto-eletronico/app.js`

## 9. Exportações em background

### Entrada

- Usuário solicita exportação de CIH, requisição de vaga, NPS e outros relatórios.

### Processamento

- Controller enfileira job.
- Job adquire lock distribuído em Redis quando aplicável.
- Worker autentica o usuário no contexto do job quando necessário.
- Gera CSV/XLSX/PDF em arquivo temporário.
- Faz upload no disco de exportação.
- Dispara notificação em tempo real e registra em `exportacoes`.

### Persistência

- `exportacoes`
- storage `disco-exportacao`
- caches/locks Redis

### Saída

- Mensagem imediata ao usuário.
- Download posterior na área de exports.

### Tratamento de erro

- Retry do queue worker.
- Logs detalhados e liberação de lock no `finally`/`failed`.

### Arquivos envolvidos

- `app/Jobs/JobExportaCihCsvFinal.php`
- `app/Jobs/JobExportaRequisicaoVaga.php`
- `app/Jobs/JobExportaNpsExcel.php`
- `app/Events/Notificacoes/NotificacaoEvent.php`

## 10. NPS interno

### Entrada

- Usuário autenticado recebe modal NPS quando elegível.

### Processamento

- Verifica feature flag, empresa excluída, mínimo de acessos e ciclo vigente.
- Carrega perguntas ativas para a empresa.
- Salva resposta mestre + itens por pergunta.
- Gerenciamento central gera resumo, distribuição por nota e exportação.

### Persistência

- `nps_ciclos`
- `nps_perguntas`
- `nps_respostas`
- `nps_resposta_itens`

### Saída

- Modal Vue com perguntas.
- Relatório gerencial interno com filtros por período/empresa/ciclo.

### Tratamento de erro

- Validação 422 para payload inválido.
- Erro 500 em falha de persistência.

### Arquivos envolvidos

- `app/Http/Controllers/NpsController.php`
- `config/nps.php`
- `resources/js/components/NpsModal.vue`
