# 04. Arquitetura Tecnica

> Convenções usadas neste documento
> - Confirmado no código: sustentado diretamente por arquivos do repositório.
> - Inferido: conclusão derivada de múltiplas evidências.

## Frameworks e bibliotecas principais

### Backend

**Confirmado no código**

- Laravel 12 como framework base
- Sanctum para autenticação API/stateful SPA
- Horizon para supervisão de filas
- Reverb e Pusher para broadcasting
- DomPDF e Maatwebsite Excel para geração documental
- Spatie Activitylog para trilha de alterações em muitos models
- Guzzle para integrações HTTP
- Intervention Image para processamento de imagem
- Endroid QR Code, FPDF e FPDI para documentos/PDFs

**Arquivos-base**

- `composer.json`
- `config/sanctum.php`
- `config/horizon.php`
- `config/broadcasting.php`

### Frontend

**Confirmado no código**

- Vue 3 em múltiplas entradas por tela
- Laravel Mix em vez de Vite
- Bootstrap 4 + jQuery ainda presentes
- Axios como cliente HTTP principal
- Echo para tempo real
- Leaflet para mapa do ponto eletrônico
- TinyMCE em editores ricos

**Arquivos-base**

- `package.json`
- `webpack.mix.js`
- `resources/js/bootstrap.js`

## Organização de camadas

### Camada HTTP

**Confirmado no código**

- Rotas definidas em `routes/web.php`, `routes/api.php`, `routes/channels.php`, `routes/console.php`
- Controllers fazem orquestração e, em muitos casos, validação, persistência e notificação no mesmo arquivo
- Middlewares customizados participam fortemente da segurança e autorização

**Arquivos-base**

- `bootstrap/app.php`
- `routes/web.php`
- `routes/api.php`
- `app/Http/Middleware/*`

### Camada de domínio/aplicação

**Confirmado no código**

- Parte da regra mora em models Eloquent
- Parte da regra mora em services mais recentes
- Parte da regra mora em jobs, principalmente para fluxos notificados ou em batch
- Não há camada de repositories generalizada
- Não há policies mapeadas em `AuthServiceProvider`

**Arquivos-base**

- `app/Models/*`
- `app/Services/*`
- `app/Jobs/*`
- `app/Providers/AuthServiceProvider.php`

### Camada de persistência

**Confirmado no código**

- Predominância de Eloquent
- Uso recorrente de Query Builder em listagens/exportações pesadas e em áreas legadas
- Soft delete presente em vários models
- Multi-tenant por `empresa_id` via global scope em parte do domínio

**Arquivos-base**

- `app/Tenant/Traits/TenantTrait.php`
- `app/Tenant/Scopes/ScopeEmpresa.php`
- `app/Scopes/ScopeEmpresa.php`
- `app/Services/Cih/CihQueryBuilder.php`
- `app/Services/RequisicaoVaga/RequisicaoVagaExportQueryBuilder.php`

## Rotas

### Web

**Confirmado no código**

Principais grupos encontrados em `routes/web.php`:

- `publico`: utilitários públicos, anexos cloud e ficha de exame
- `g/*`: autenticação web
- grupo secreto de exportações por URL fixa
- grupo autenticado `auth + habilidades + check.password.reset`
- submódulos: `administracao`, `cadastro`, `planejamento`, `curriculos`, `entrevistas`, `admissao`, `historico`, `cloud`, `relatorios`, `controle-ponto`, `weekly-report`, `chat`, `configuracoes`, `financeiro`, `acesso-clinica`
- grupos públicos por token: `provas`, `documentospreadmissao`, `pesquisaclima`

**Confirmado no runtime**

- `1329` rotas no total
- `1245` rotas sob prefixo `g`
- `28` rotas sob prefixo `api`
- `13` rotas sob prefixo `{apelido}`
- `12` rotas sob prefixo `publico`
- `7` rotas sob o prefixo secreto de exportação

### API

**Confirmado no código**

- autenticação por token fixo (`apitoken`) para integrações específicas
- login API e recursos protegidos por Sanctum
- APIs públicas/integração de vagas abertas
- endpoint SGI para carta oferta
- endpoint de resposta de convocação intermitente

**Arquivos-base**

- `routes/web.php`
- `routes/api.php`
- `routes/channels.php`

## Controllers, Services, Models, Jobs, Events

### Controllers

**Confirmado no código**

Há controllers muito grandes e multiuso. Os maiores observados:

| Controller | Linhas observadas |
|---|---:|
| `app/Http/Controllers/AdmissaoController.php` | 2499 |
| `app/Http/Controllers/TreinamentoController.php` | 1087 |
| `app/Http/Controllers/ClientesController.php` | 968 |
| `app/Http/Controllers/RecrutamentoController.php` | 946 |
| `app/Http/Controllers/AvaliacaoController.php` | 828 |

### Services

**Confirmado no código**

Há uma camada de services mais consistente em módulos recentes:

- assinatura digital
- avaliação de 90 dias
- renderização de carta oferta
- histórico de medida administrativa
- exportadores/formatters/query builders para CIH e movimentações

Os maiores observados:

| Service | Linhas observadas |
|---|---:|
| `app/Services/AssinaturaDigital/AssinaturaDigitalService.php` | 1083 |
| `app/Services/AvaliacaoNoventaService.php` | 951 |
| `app/Services/Treinamento/FeedbackCurriculoFilter.php` | 644 |

### Models

**Confirmado no código**

Os models também carregam muita regra. Maiores observados:

| Model | Linhas observadas |
|---|---:|
| `app/Models/Sistema.php` | 1132 |
| `app/Models/Admissao.php` | 1012 |
| `app/Models/FeriasCalculoAvos.php` | 974 |
| `app/Models/FeedbackCurriculo.php` | 895 |
| `app/Models/Arquivo.php` | 738 |

### Jobs

**Confirmado no código**

Há 130 jobs. Eles cobrem:

- exportações
- notificações recursivas de aprovação
- assinatura digital
- exames e treinamentos
- rotinas operacionais
- avaliação 90 dias

**Arquivos-base**

- `app/Jobs/*`
- `app/Console/Kernel.php`

### Events e broadcasting

**Confirmado no código**

Há 9 eventos focados em tempo real:

- weekly report
- chat
- notificações

Todos implementam broadcasting imediato (`ShouldBroadcastNow`) e usam canais privados/presence.

**Arquivos-base**

- `app/Events/WeeklyReport/*`
- `app/Events/Chat/MensagemChatEvent.php`
- `app/Events/Notificacoes/NotificacaoEvent.php`
- `routes/channels.php`

## Filas, cache, sessão, logs, scheduler e workers

### Filas

**Confirmado no código**

- `QUEUE_CONNECTION` é configurável; `sync`, `database`, `redis`, `sqs` e `beanstalkd` estão previstos
- Horizon usa conexão `redis`
- Jobs críticos usam locks em Redis em algumas exportações

**Confirmado no runtime**

- `queue` ativa em `redis`

**Arquivos-base**

- `config/queue.php`
- `config/horizon.php`
- `app/Jobs/JobExportaCihCsvFinal.php`
- `app/Jobs/JobExportaRequisicaoVaga.php`

### Cache

**Confirmado no código**

- Cache é usado para locks de exportação, status de código de assinatura, dados de treinamento/documentos e várias otimizações pontuais
- A invalidação é inconsistente entre módulos

**Confirmado no runtime**

- `cache` ativa em `redis`

**Arquivos-base**

- `app/Services/AssinaturaDigital/AssinaturaDigitalService.php`
- `app/Services/AssinaturaDigital/AssinaturaCotaService.php`
- `app/Models/DocumentosCurriculosAdmissaoEmpresa.php`

### Scheduler

**Confirmado no código**

O código declara rotinas de:

- jornadas de ponto
- vencimento e saída de férias
- avaliação de experiência
- limpeza de exportação
- aniversariantes
- convocação intermitente
- férias
- cálculo de avos
- correção de ponto
- snapshot do Horizon
- vencimento ASO
- treinamento vencido

**Arquivos-base**

- `app/Console/Kernel.php`

**Confirmado no runtime**

- `php artisan schedule:list` retornou `No scheduled tasks have been defined`

**Inferido**

Há divergência entre a agenda declarada em `app/Console/Kernel.php` e o bootstrap efetivo do Laravel 12 em `bootstrap/app.php`. Na prática, as rotinas acima não estão registradas no runtime validado.

### Logs

**Confirmado no código**

- Canal padrão em arquivo local
- Stack custom opcional com `stderr` + Telegram
- Diversos controllers/loggers gravam mensagens detalhadas, às vezes com payload operacional

**Confirmado no runtime**

- Driver de log ativo em `stack / single`

**Arquivos-base**

- `config/logging.php`
- `app/Services/Log/LogTelegram.php`

### Sessão

**Confirmado no código**

- Guarda `web` usa sessão padrão Laravel
- API autenticada usa Sanctum + `EnsureFrontendRequestsAreStateful`

**Confirmado no runtime**

- `session` ativa em `redis`

**Arquivos-base**

- `config/auth.php`
- `config/sanctum.php`
- `bootstrap/app.php`

## Estratégia de storage, e-mail, notificações e autenticação

### Storage

**Confirmado no código**

O sistema usa múltiplos discos nomeados por contexto funcional: cliente, fornecedor, ponto eletrônico, dossiê, exames, assinatura, documento para assinatura, exportação, cloud, pré-admissão, movimentação etc.

**Arquivos-base**

- `config/filesystems.php`
- `app/Models/Arquivo.php`

### E-mail

**Confirmado no código**

- Há 68 classes de mail e 73 templates em `resources/views/email`
- E-mails importantes são enfileirados em diversos jobs
- Ainda existe envio acoplado em partes legadas

### Notificações

**Confirmado no código**

- Notificações em tempo real via evento broadcast
- Notificações por e-mail em aprovação/rotinas
- Notificações WhatsApp via job dedicado

### Autenticação e autorização

**Confirmado no código**

- Web: sessão Laravel
- API: Sanctum e token middleware específico
- Autorização principal: Gates dinâmicos carregados a partir da tabela `habilidades`
- Policies: inexistentes no estado atual

**Arquivos-base**

- `app/Providers/AuthServiceProvider.php`
- `app/Http/Middleware/CarregaHabilidades.php`
- `app/Http/Middleware/TemHabilidade.php`
- `app/Http/Middleware/UsuarioAtivo.php`

## Estado real do frontend

### Confirmado no código

- O projeto já usa Vue 3, mas não está em arquitetura SPA unificada.
- O padrão real é: Blade entrega o shell + um entrypoint específico de Vue monta a tela.
- Há uso recorrente de mixins, `axios` direto, estado local volumoso e integração forte com jQuery/Bootstrap modal.
- Há documentação interna indicando migração gradual para Composition API + services, ainda não concluída.

**Arquivos-base**

- `resources/js/g/admissao/processo/app.js`
- `resources/js/g/planejamento/requisicao-vagas/app.js`
- `resources/views/auth/login.blade.php`
- `docs/PLANO_MIGRACAO_COMPOSITION_API_SERVICES.md`
