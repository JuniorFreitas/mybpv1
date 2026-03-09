# 03. Modulos de Negocio

> Convenções usadas neste documento
> - Confirmado no código: comportamento ou estrutura sustentados por arquivos do repositório.
> - Inferido: leitura arquitetural derivada de múltiplas evidências.

## Mapa de domínios

### 1. Identidade e acesso

**Responsabilidade confirmada**

- Login web
- Login API com Sanctum
- Ativação/desativação de usuários
- Reset e troca obrigatória de senha
- Habilidades por papel/grupo
- Restrições por usuário ativo e tipo de usuário

**Arquivos-base**

- `app/Http/Controllers/Auth/LoginController.php`
- `app/Http/Controllers/AlterarSenhaController.php`
- `app/Models/User.php`
- `app/Http/Middleware/Authenticate.php`
- `app/Http/Middleware/CheckPasswordReset.php`
- `app/Http/Middleware/CarregaHabilidades.php`
- `config/auth.php`
- `config/sanctum.php`

### 2. Administração e cadastros

**Responsabilidade confirmada**

- Gestão de clientes
n- fornecedores
- documentos legais
- templates de carta oferta
- configuração de assinatura digital
- configuração de aprovação extra
- cadastros mestres de área, centro de custo, cargo, treinamento, exame, benefício, projeto, avaliações

**Arquivos-base**

- `routes/web.php` nos grupos `administracao` e `cadastro`
- `app/Http/Controllers/ClientesController.php`
- `app/Http/Controllers/FornecedorController.php`
- `app/Http/Controllers/CartaOfertaTemplateController.php`
- `app/Http/Controllers/DocumentoAssinaturaController.php`
- `app/Http/Controllers/AprovacaoExtraConfigController.php`

### 3. Recrutamento e currículos

**Responsabilidade confirmada**

- cadastro e atualização de currículo
- telefones do candidato
- feedback por vaga/cliente
- seleção, desclassificação, próxima etapa, provas e comunicação com candidato
- histórico de alterações do recrutamento

**Arquivos-base**

- `app/Http/Controllers/RecrutamentoController.php`
- `app/Models/Curriculo.php`
- `app/Models/FeedbackCurriculo.php`
- `app/Models/RecrutamentoHistorico.php`
- `app/Jobs/Recrutamento/*`
- `app/Http/Controllers/Api/IntegracaoVagaAbertaController.php`

### 4. Entrevistas e resultado integrado

**Responsabilidade confirmada**

- parecer RH
- parecer rota
- parecer técnico
- parecer teste prático
- entrevistas RH e gestor
- consolidação do resultado integrado
- encaminhamento para exame e abertura de processo de admissão

**Arquivos-base**

- `app/Http/Controllers/ParecerRhController.php`
- `app/Http/Controllers/ResultadoIntegradoController.php`
- `app/Models/ResultadoIntegrado.php`
- `app/Models/ExameFuncionario.php`

### 5. Admissão e pré-admissão

**Responsabilidade confirmada**

- processo de admissão
- coleta de documentos pré-admissionais
- geração e acompanhamento de carta oferta
- CIH e intermitente
- histórico de admissão e pós-admissão

**Arquivos-base**

- `app/Http/Controllers/AdmissaoController.php`
- `app/Http/Controllers/PreAdmissaoController.php`
- `app/Http/Controllers/DocumentosPreAdmissaoController.php`
- `app/Http/Controllers/CartaOfertaController.php`
- `app/Http/Controllers/IntermitenteController.php`
- `app/Models/Admissao.php`
- `app/Models/CartaOferta.php`
- `app/Models/FeedbackPreadmissao.php`

### 6. Assinatura digital

**Responsabilidade confirmada**

- envio de PDF para assinatura
- coleta de evidências de assinatura
- auditoria de eventos
- fluxo público por token com validação de CPF + código por e-mail
- geração de PDF final com marca d'água
- extrato mensal e controle de cota por empresa

**Arquivos-base**

- `app/Http/Controllers/DocumentoAssinaturaController.php`
- `app/Http/Controllers/AssinaturaPublicaController.php`
- `app/Services/AssinaturaDigital/AssinaturaDigitalService.php`
- `app/Services/AssinaturaDigital/AssinaturaCotaService.php`
- `app/Models/DocumentoParaAssinatura.php`
- `app/Models/DocumentoAssinaturaSignatario.php`
- `app/Models/DocumentoAssinaturaEvento.php`

### 7. Planejamento e movimentações

**Responsabilidade confirmada**

- requisição de vaga
- demissão prevista
- férias prevista
- mudança de cargo
- transferência
- intermitente para fixo
- valor extra
- admissões previstas
- mobilização
- aprovação por gestor, aprovação extra e RH

**Arquivos-base**

- `routes/web.php` no grupo `planejamento`
- `app/Http/Controllers/RequisicaoVagaController.php`
- `app/Http/Controllers/DemissaoPrevistaController.php`
- `app/Http/Controllers/FeriasPrevistaController.php`
- `app/Http/Controllers/MudancaCargoController.php`
- `app/Http/Controllers/TransferenciaPrevistaController.php`
- `app/Http/Controllers/IntermitenteFixoPrevistaController.php`
- `app/Http/Controllers/ValorExtraPrevistaController.php`
- `app/Models/RequisicaoVagaMovimentacao.php`
- `app/Models/AprovacaoExtraConfig.php`

### 8. Controle de exames e treinamentos

**Responsabilidade confirmada**

- encaminhamento de exames ocupacionais
- armazenamento de resultados ASO/SESMT
- agendamento/comunicação com clínica e colaborador
- gestão de treinamentos e vencimentos
- carteira/certificados e exportações

**Arquivos-base**

- `app/Http/Controllers/ControleExameController.php`
- `app/Http/Controllers/TreinamentoController.php`
- `app/Models/ExameFuncionario.php`
- `app/Models/Examesesmt.php`
- `app/Models/Treinamento.php`
- `app/Services/Treinamento/FeedbackCurriculoFilter.php`

### 9. Controle de ponto

**Responsabilidade confirmada**

- configuração de perimetros, escalas, feriados e ocorrências
- marcação de ponto com foto e geolocalização
- ajustes de jornada
- folha de ponto e relatório sintético

**Arquivos-base**

- `routes/web.php` no grupo `controle-ponto`
- `app/Http/Controllers/PontoEletronicoController.php`
- `app/Models/PontoEletronico.php`
- `app/Models/PeriodoPontoEletronico.php`
- `resources/js/g/controle-ponto/ponto-eletronico/app.js`

### 10. Relatórios, clima e NPS

**Responsabilidade confirmada**

- relatórios operacionais de RH
- avaliação de experiência / 90 dias
- pesquisa de clima
- NPS com ciclos e modal interno
- exportações em background

**Arquivos-base**

- `routes/web.php` no grupo `relatorios`
- `app/Services/AvaliacaoNoventaService.php`
- `app/Http/Controllers/NpsController.php`
- `app/Http/Controllers/PesquisaClimaController.php`
- `app/Models/NpsCiclo.php`
- `app/Models/NpsResposta.php`

### 11. Colaboração e apoio operacional

**Responsabilidade confirmada**

- weekly report estilo quadro/lista/tarefa
- chat interno
- notificações em tempo real
- cloud para anexos
- downloads de exportação

**Arquivos-base**

- `app/Http/Controllers/TarefasController.php`
- `app/Http/Controllers/ChatController.php`
- `app/Events/WeeklyReport/*`
- `app/Events/Chat/MensagemChatEvent.php`
- `app/Events/Notificacoes/NotificacaoEvent.php`
- `routes/channels.php`

### 12. Financeiro

**Responsabilidade confirmada**

- fluxo de caixa
- classificação de plano de conta
- plano de conta
- formas de pagamento

**Arquivos-base**

- `routes/web.php` no grupo `financeiro`
- `app/Http/Controllers/FluxoCaixaController.php`
- `app/Http/Controllers/CategoriaPlanoContaController.php`
- `app/Http/Controllers/PlanoContaController.php`
- `app/Http/Controllers/FormaPagamentoController.php`

## Fluxos entre módulos

### Fluxo central do ciclo do colaborador

**Confirmado no código**

`Curriculo` -> `FeedbackCurriculo` -> pareceres/entrevistas -> `ResultadoIntegrado` -> `ExameFuncionario` -> `Admissao` -> `Treinamento`/`Ponto`/`Historico`/`Demissao`

**Arquivos-base**

- `app/Models/FeedbackCurriculo.php`
- `app/Http/Controllers/ResultadoIntegradoController.php`
- `app/Http/Controllers/PreAdmissaoController.php`
- `app/Models/Admissao.php`
- `app/Http/Controllers/TreinamentoController.php`
- `app/Http/Controllers/PontoEletronicoController.php`

### Fluxo de vaga e contratação

**Confirmado no código**

`RequisicaoVagaMovimentacao` alimenta necessidade de contratação. O recrutamento atua sobre `VagasAbertas`, gera `FeedbackCurriculo` e, após o resultado integrado, abre processo de admissão.

**Arquivos-base**

- `app/Http/Controllers/RequisicaoVagaController.php`
- `app/Http/Controllers/RecrutamentoController.php`
- `app/Http/Controllers/ResultadoIntegradoController.php`

### Fluxo documental

**Confirmado no código**

Documentos podem seguir pelo menos três caminhos:

- upload de documentos pré-admissionais pelo candidato;
- carta oferta pública com upload do anexo assinado;
- assinatura digital formal com evidências e PDF final.

**Arquivos-base**

- `app/Http/Controllers/DocumentosPreAdmissaoController.php`
- `app/Http/Controllers/CartaOfertaController.php`
- `app/Http/Controllers/AssinaturaPublicaController.php`
- `app/Services/AssinaturaDigital/AssinaturaDigitalService.php`

## Principais entidades do domínio

### Entidades centrais confirmadas

- `User`
- `Cliente`
- `Curriculo`
- `FeedbackCurriculo`
- `ResultadoIntegrado`
- `Admissao`
- `Treinamento`
- `ExameFuncionario`
- `Examesesmt`
- `RequisicaoVagaMovimentacao`
- `DocumentoParaAssinatura`
- `DocumentoAssinaturaSignatario`
- `CartaOferta`
- `PontoEletronico`
- `Intermitente`
- `NpsResposta`

## Acoplamentos relevantes

### Confirmado no código

- `FeedbackCurriculo` é o principal hub de RH e se relaciona com grande parte dos fluxos de entrevistas, exames, admissão, treinamento, documentos, dossiê, férias e desligamento.
- `User` representa múltiplos papéis de negócio: empresa, funcionário, gestor, fornecedor, clínica e candidato.
- `ClienteConfig` concentra chaves funcionais que alteram comportamento de vários módulos.
- `Sistema` atua como utilitário transversal e também como ponto de regra/configuração, o que aumenta acoplamento.

**Arquivos-base**

- `app/Models/FeedbackCurriculo.php`
- `app/Models/User.php`
- `app/Models/ClienteConfig.php`
- `app/Models/Sistema.php`

### Inferido

Os bounded contexts existem mais por convenção de pastas e nomenclatura do que por isolamento técnico forte. Os módulos compartilham tabelas, models e helpers com frequência, especialmente via `FeedbackCurriculo`, `User`, `Cliente` e `Arquivo`.
