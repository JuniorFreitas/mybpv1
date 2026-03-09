# 02. Mapa do Sistema

> Convenções usadas neste documento
> - Confirmado no código: sustentado diretamente pelo repositório.
> - Inferido: conclusão derivada da combinação de evidências.

## Inventário geral do repositório

### Estrutura principal

**Confirmado no código**

- `app/`: código backend principal
- `bootstrap/`: bootstrap do Laravel
- `config/`: configuração da aplicação
- `database/`: migrations, factories, seeders
- `resources/js/`: apps Vue 3, componentes, mixins e utilitários frontend
- `resources/views/`: Blade, páginas internas, PDFs e e-mails
- `routes/`: rotas web, api, channels e console
- `tests/`: suíte automatizada atual
- `.deploy/`: scripts e artefatos de infraestrutura/deploy
- `docker-compose*.yml` e `Dockerfile`: runtime local/containerizado

**Arquivos-base**

- `docker-compose.yml`
- `Dockerfile`
- `bootstrap/app.php`

### Volume aproximado observado

**Confirmado no código**

| Área | Quantidade observada |
|---|---:|
| Arquivos em `app/` | 744 |
| Arquivos em `database/` | 608 |
| Arquivos em `resources/` | 1817 |
| Controllers PHP | 157 |
| Models PHP | 234 |
| Jobs PHP | 130 |
| Services PHP | 60 |
| Mails PHP | 68 |
| Events PHP | 9 |
| Form Requests | 2 |
| Components frontend | 151 |
| Views internas (`resources/views/g`) | 113 |
| Views PDF | 73 |
| Views de e-mail | 73 |
| Arquivos de teste | 7 |

### Volume validado em runtime

**Confirmado no runtime**

- `1329` rotas expostas pelo Laravel em execução
- Prefixos com maior volume: `g` (`1245`), `api` (`28`), `{apelido}` (`13`), `publico` (`12`)
- Há grupo público de exportação com prefixo secreto estático (`7` rotas)

**Arquivos-base**

- `routes/web.php`
- `routes/api.php`

## Tecnologias usadas

### Backend

**Confirmado no código**

- PHP 8.2
- Laravel 12
- Laravel Sanctum
- Laravel Horizon
- Laravel Reverb
- Spatie Activitylog
- Maatwebsite Excel
- Barryvdh DomPDF
- Flysystem S3
- Predis
- Guzzle

**Arquivos-base**

- `composer.json`
- `config/queue.php`
- `config/horizon.php`
- `config/broadcasting.php`

### Frontend

**Confirmado no código**

- Vue 3
- Laravel Mix 6 / Webpack 5
- Bootstrap 4
- Axios
- jQuery
- Laravel Echo
- Pusher JS
- Chart.js
- Leaflet
- TinyMCE
- SweetAlert2
- vue-multiselect
- vuedraggable

**Arquivos-base**

- `package.json`
- `webpack.mix.js`
- `resources/js/bootstrap.js`
- `resources/js/registerGlobals.js`

### Infraestrutura e runtime

**Confirmado no código**

- Docker Compose para ambiente local
- Nginx + container Laravel customizado
- Supervisor/Horizon expostos no container
- Schedule e workers habilitáveis por variáveis de ambiente

**Confirmado no runtime**

- Container `mybpdp` ativo com Laravel `12.53.0` e PHP `8.2.8`
- Drivers ativos: `mysql`, `redis`, `smtp`, `pusher`
- `views` cacheadas; `config`, `routes` e `events` não cacheados
- O scheduler não está registrando tarefas no runtime atual, apesar das definições em `app/Console/Kernel.php`

**Arquivos-base**

- `docker-compose.yml`
- `Dockerfile`
- `.deploy/scripts/start`
- `app/Console/Kernel.php`
- `bootstrap/app.php`

## Como a aplicação está organizada

### Organização backend

**Confirmado no código**

- `app/Http/Controllers`: orquestração HTTP, ainda com muita regra embutida
- `app/Models`: entidades Eloquent e parte relevante das regras de domínio
- `app/Services`: extrações mais recentes para assinatura, exportação, treinamento, CIH e movimentações
- `app/Jobs`: processamento assíncrono, exportações, notificações e rotinas
- `app/Mail`: templates e envelopes de e-mail
- `app/Events`: broadcasting para weekly report, chat e notificações
- `app/Tenant` e `app/Scopes`: escopo multiempresa

### Organização frontend

**Confirmado no código**

- Shell principal em Blade (`resources/views/layouts/sistema.blade.php`)
- Um `app.js` por tela/módulo em `resources/js/g/**/app.js`
- Registro global de componentes compartilhados e diretivas
- Mistura de Vue 3 modular com views Blade legadas e Vue inline

**Arquivos-base**

- `resources/views/layouts/sistema.blade.php`
- `resources/js/app.js`
- `resources/js/registerGlobals.js`
- `resources/js/g/admissao/processo/app.js`
- `resources/js/g/planejamento/requisicao-vagas/app.js`
- `resources/views/auth/login.blade.php`

## Padrões arquiteturais encontrados

### Confirmado no código

- Monólito modular por contexto funcional
- MVC Laravel com domínio parcialmente espalhado entre controller, model, job e service
- Autorização por Gates dinâmicos carregados do banco, e não por policies dedicadas
- Multi-tenant por `empresa_id` e `TenantTrait` com global scope
- Eventos de broadcast em tempo real para módulos colaborativos
- Processamento pesado deslocado para jobs/filas em várias áreas
- Padrão mais novo de exportadores/formatters/query builders em módulos recentes

**Arquivos-base**

- `app/Http/Middleware/CarregaHabilidades.php`
- `app/Providers/AuthServiceProvider.php`
- `app/Tenant/Traits/TenantTrait.php`
- `app/Tenant/Scopes/ScopeEmpresa.php`
- `app/Services/Cih/*`
- `app/Services/RequisicaoVaga/*`
- `app/Services/FeriasPrevista/*`

### Inferido

A arquitetura real não é puramente em camadas. Há convivência de três estilos:

- legado centrado em controllers/models grandes;
- camada intermediária com jobs e helpers coordenando fluxos;
- núcleos mais novos mais orientados a services e jobs específicos.

Essa convivência é um traço importante do sistema atual e precisa ser preservada em qualquer evolução progressiva.

## Dependências principais do produto

### Dependências funcionais

**Confirmado no código**

- Banco de dados relacional com grande volume de migrations históricas
- Redis para Horizon/locks/cache distribuído em alguns jobs
- Storage configurável local/S3 para anexos e exports
- Fila ativa para exportação, assinatura, exames, treinamentos e notificações
- Websocket/broadcast para chat, weekly report e notificações

### Dependências organizacionais

**Inferido**

- Muito conhecimento de negócio está implícito no código e em nomes internos de módulos de RH.
- A manutenção segura depende de entender os fluxos entre `FeedbackCurriculo`, `Admissao`, `ResultadoIntegrado`, `Treinamento`, `ExameFuncionario`, `DocumentoParaAssinatura` e `RequisicaoVagaMovimentacao`.
