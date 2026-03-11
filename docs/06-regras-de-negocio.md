# 06. Regras de Negocio

> Convenções usadas neste documento
> - Confirmado no código: regra explicitamente implementada.
> - Inferido: leitura derivada do comportamento observado.

## 1. Identidade e acesso

### Regras confirmadas

- Usuário inativo ou do tipo `Candidato` é desconectado no fluxo web autenticado.
- Primeiro acesso e senha temporária exigem troca de senha.
- Senha nova deve ter no mínimo 8 caracteres, com maiúscula, minúscula, número e caractere especial.
- Habilidades são resolvidas dinamicamente a partir do papel (`papel -> habilidades`).
- APIs protegidas por Sanctum exigem token com habilidade específica.

**Onde está implementado**

- `app/Http/Middleware/Authenticate.php`
- `app/Http/Middleware/CheckPasswordReset.php`
- `app/Http/Controllers/AlterarSenhaController.php`
- `app/Http/Middleware/CarregaHabilidades.php`
- `app/Http/Middleware/TemHabilidade.php`
- `app/Models/User.php`

### Regra duplicada/espalhada

- A obrigação de troca de senha está espalhada entre `LoginController`, `CheckPasswordReset` e `User`.

## 2. Recrutamento e currículos

### Regras confirmadas

- Atualização de currículo registra histórico antes/depois.
- Telefones removidos, criados e atualizados também geram histórico.
- Feedback do candidato é processado no mesmo fluxo da atualização do currículo.
- Provas, próxima etapa, desclassificação e WhatsApp são disparados conforme flags recebidas na atualização.

**Onde está implementado**

- `app/Http/Controllers/RecrutamentoController.php`
- `app/Models/RecrutamentoHistorico.php`
- `app/Jobs/Recrutamento/*`

### Regra implícita/inferida

- `FeedbackCurriculo` funciona como entidade de acompanhamento do candidato por vaga e como ponte para o restante do ciclo de RH.

## 3. Resultado integrado e abertura da admissão

### Regras confirmadas

- Para salvar resultado integrado, `documentos_entregue`, `encaminhado_exame`, `encaminhado_treinamento` e `responsavel_envio` são obrigatórios.
- Quando existe `empresa_exame_id` + `pcmso_id`, o sistema cria `ExameFuncionario` se ainda não houver encaminhamento equivalente.
- Ao salvar/atualizar resultado integrado, o sistema garante a existência de um registro em `admissoes` com status `ENCAMINHADO EXAME`.

**Onde está implementado**

- `app/Http/Controllers/ResultadoIntegradoController.php`
- `app/Models/Admissao.php`

## 4. Pré-admissão e documentos

### Regras confirmadas

- A listagem de pré-admissão filtra somente candidatos cujo `ResultadoIntegrado` marque `documentos_entregue = true`.
- A finalização de pré-admissão grava `FeedbackPreadmissao` com quem finalizou.
- Na finalização, o sistema pode abrir exame admissional e disparar comunicações.
- Os tipos de documentos exigidos na pré-admissão são configurados por empresa.

**Onde está implementado**

- `app/Http/Controllers/PreAdmissaoController.php`
- `app/Http/Controllers/DocumentosPreAdmissaoController.php`
- `app/Models/DocumentosCurriculosAdmissaoEmpresa.php`
- `app/Models/FeedbackPreadmissao.php`

### Regras espalhadas

- A regra de documentos obrigatórios do candidato está repartida entre tabela configurável por empresa, telas públicas e telas internas.

## 5. Admissão

### Regras confirmadas

- Há um conjunto fechado de status de admissão, incluindo `PENDENTE DOCUMENTO`, `PENDENTE ASO`, `PENDENTE TREINAMENTO`, `ENCAMINHADO EXAME` e `ADMITIDO`.
- Há um conjunto fechado de tipos de admissão, incluindo `FIXO`, `TEMPORARIO`, `INTERMITENTE`, `DETERMINADO`, `PJ`, `ESTÁGIO` e `APRENDIZ`.
- A avaliação de experiência/90 dias depende do tipo de admissão e do prazo de experiência.
- Para contratos intermitentes/determinados/temporários, a lógica de vencimento da avaliação difere do fluxo fixo.

**Onde está implementado**

- `app/Models/Admissao.php`
- `app/Services/AvaliacaoNoventaService.php`

## 6. Carta oferta e assinatura digital

### Regras confirmadas

- Carta oferta legada possui estados `Pendente Anexo`, `Aguardando RH`, `Aceito pelo RH`, `Recusado pelo RH` e `Expirado`.
- Na assinatura digital nova, o documento vai para `em_assinatura` e conclui quando todos os signatários assinam.
- O acesso público da assinatura exige token válido, CPF do signatário e código temporário enviado por e-mail.
- Há limite de 5 tentativas para código de verificação, bloqueio temporário e cooldown de reenvio.
- Ao concluir, o sistema gera PDF com marca d'água, registra eventos e envia documento assinado.
- Carta oferta integrada à assinatura digital atualiza o status para `Aguardando RH` após conclusão.

**Onde está implementado**

- `app/Models/CartaOferta.php`
- `app/Http/Controllers/CartaOfertaController.php`
- `app/Http/Controllers/AssinaturaPublicaController.php`
- `app/Services/AssinaturaDigital/AssinaturaDigitalService.php`
- `app/Models/DocumentoParaAssinatura.php`
- `app/Models/DocumentoAssinaturaSignatario.php`

### Regra duplicada/convivência legado-novo

- Existem dois fluxos documentais de aceite: carta oferta legada por upload e assinatura digital formal com evidências.
- Isso caracteriza convivência entre legado e novo, não substituição completa.

## 7. Requisição de vaga e aprovações

### Regras confirmadas

- Na criação/edição de requisição de vaga, vários campos de contratação são obrigatórios, inclusive `gestor_id`, `contrato`, `ppra`, `salario` e `beneficio`.
- Campos personalizados obrigatórios são validados por empresa.
- A aprovação RH só pode ocorrer após aprovação do gestor.
- Quando existe configuração de aprovação extra ativa para `requisicao_vaga`, a aprovação RH só pode ocorrer após a aprovação extra.
- Aprovadores extras podem ser usuários explicitamente autorizados ou usuários com privilégios de RH.

**Onde está implementado**

- `app/Http/Controllers/RequisicaoVagaController.php`
- `app/Models/AprovacaoExtraConfig.php`
- `app/Models/RequisicaoVagaMovimentacao.php`

## 8. Exames ocupacionais

### Regras confirmadas

- Exame pode ser encaminhado com PCMSO ou com respostas manuais de formulário.
- Ao encaminhar, o sistema pode notificar clínica e colaborador por e-mail e/ou WhatsApp.
- Resultado realizado exige campos como data, resultado, pendências e aprovação.
- Quando aprovado, apenas um resultado SESMT fica marcado como atual.

**Onde está implementado**

- `app/Http/Controllers/ControleExameController.php`
- `app/Http/Controllers/PreAdmissaoController.php`
- `app/Models/ExameFuncionario.php`
- `app/Models/Examesesmt.php`

## 9. Treinamentos

### Regras confirmadas

- O sistema grava apenas vencimentos com `fez_treinamento = true`.
- Vencimento é calculado a partir de `prazo_fixo` quando aplicável.
- Atualização remove vencimentos do segmento atual antes de reanexar os novos.
- Histórico de vencimentos é salvo por `feedback_id`, `empresa_id`, `treinamento_id` e `user_id`.

**Onde está implementado**

- `app/Http/Controllers/TreinamentoController.php`
- `app/Models/TreinamentoVencimentoHistorico.php`

## 10. Intermitente

### Regras confirmadas

- Convocação intermitente gera hash por colaborador e links de resposta `sim`/`não`.
- O sistema pode enviar a convocação por e-mail e WhatsApp.
- Convocações abertas sem resposta após `prazo_resposta_expiracao` são marcadas como expiradas pelo scheduler/job.

**Onde está implementado**

- `app/Http/Controllers/IntermitenteController.php`
- `app/Models/Intermitente.php`
- `app/Jobs/Rotinas/JobConvocacaoIntermitente.php`

## 11. Controle de ponto

### Regras confirmadas

- Não é possível registrar ponto sem foto.
- O colaborador precisa ter escala vinculada para registrar ponto.
- O registro salva foto, latitude/longitude e períodos de entrada/saída.
- O frontend verifica perímetro/geolocalização antes da marcação quando a empresa exige isso.

**Onde está implementado**

- `app/Http/Controllers/PontoEletronicoController.php`
- `app/Models/PontoEletronico.php`
- `resources/js/g/controle-ponto/ponto-eletronico/app.js`

### Inferido

A validação forte de geofence parece estar majoritariamente no frontend da marcação. O backend persiste latitude/longitude, mas o enforcement geográfico explícito não apareceu no mesmo nível de robustez no controller analisado.

## 12. Avaliação de experiência / 90 dias

### Regras confirmadas

- O relatório mostra apenas colaboradores admitidos nos últimos 180 dias.
- Apenas usuários com privilégio de RH recebem a listagem completa.
- Gestores recebem agrupamento por centro de custo/gestor quando configurados.
- No máximo 2 avaliações são consideradas por colaborador nesse fluxo.

**Onde está implementado**

- `app/Services/AvaliacaoNoventaService.php`
- `app/Models/AvaliacaoNoventaVencimento.php`

## 13. NPS

### Regras confirmadas

- O modal NPS só aparece se a feature estiver habilitada.
- Empresas excluídas não recebem a pesquisa.
- É exigido um mínimo configurável de acessos nos últimos 90 dias.
- Se houver ciclo vigente, o usuário responde no máximo uma vez por ciclo.
- Notas válidas vão de 1 a 5.
- O gerenciamento dos resultados é restrito a uma empresa de gerenciamento configurada, padrão `100`.

**Onde está implementado**

- `app/Http/Controllers/NpsController.php`
- `config/nps.php`
- `app/Models/NpsPergunta.php`
- `app/Models/NpsResposta.php`

## 14. Pesquisa de clima

### Regras confirmadas

- Há autenticação pública específica para responder a pesquisa.
- Cada pergunta exige resposta.
- Perguntas abertas são persistidas via resposta digitada quando a alternativa padrão não está definida.

**Onde está implementado**

- `app/Http/Controllers/PesquisaClimaController.php`
- `app/Models/PesquisaClimaPergunta.php`
- `app/Models/PesquisaClimaPerguntaRespostaCandidato.php`

### Inconsistência confirmada

- O fluxo usa `Auth::attempt(... tipo = 'Pessoa' ...)`, enquanto os tipos conhecidos em `User` hoje são `Administrador`, `Funcionario`, `Empresa`, `Gestor`, `Fornecedor`, `Candidato` e `ClinicaExame`.

**Onde está implementado**

- `app/Http/Controllers/PesquisaClimaController.php`
- `app/Models/User.php`
