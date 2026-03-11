# 01. Visao Geral

> Convenções usadas neste documento
> - Confirmado no código: comportamento ou estrutura sustentados diretamente por arquivos do repositório.
> - Inferido: conclusão derivada da combinação de evidências, sem ponto único e explícito de confirmação.

## Resumo executivo

### O que é o sistema

**Confirmado no código**

MyBP é uma aplicação monolítica em Laravel com frontend híbrido Blade + Vue 3 voltada para operações de RH. O sistema concentra recrutamento, entrevistas, admissões, pré-admissão, treinamentos, exames ocupacionais, controle de ponto, movimentações de pessoal, assinatura digital, relatórios, chat interno, weekly report e módulos administrativos/financeiros.

**Arquivos-base**

- `composer.json`
- `package.json`
- `routes/web.php`
- `routes/api.php`
- `resources/views/layouts/sistema.blade.php`
- `webpack.mix.js`

### Qual problema resolve

**Confirmado no código**

A aplicação orquestra o ciclo de vida do colaborador desde a vaga aberta e o currículo até admissão, treinamento, exames, ponto, histórico e desligamento. Também entrega fluxos públicos controlados por token para candidato/signatário, além de automações por fila e rotinas previstas em código.

**Arquivos-base**

- `app/Models/FeedbackCurriculo.php`
- `app/Models/Admissao.php`
- `app/Models/RequisicaoVagaMovimentacao.php`
- `app/Models/DocumentoParaAssinatura.php`
- `app/Http/Controllers/RecrutamentoController.php`
- `app/Http/Controllers/ResultadoIntegradoController.php`
- `app/Http/Controllers/PreAdmissaoController.php`
- `app/Http/Controllers/PontoEletronicoController.php`
- `app/Console/Kernel.php`

### Principais módulos

**Confirmado no código**

- Identidade e acesso
- Administração e cadastros
- Recrutamento e currículos
- Entrevistas e resultado integrado
- Admissão, pré-admissão e pós-admissão
- Carta oferta e assinatura digital
- Planejamento e movimentações de pessoal
- Controle de exames
- Treinamentos
- Controle de ponto
- Relatórios, NPS e pesquisa de clima
- Weekly report, chat e notificações
- Financeiro
- Cloud/storage de anexos

**Arquivos-base**

- `routes/web.php`
- `routes/api.php`
- `app/Http/Controllers/*`
- `resources/js/g/**/app.js`

### Principais integrações

**Confirmado no código**

- E-mail e filas do Laravel para notificações, exports e assinatura digital
- Redis/Horizon para filas e métricas de jobs
- Reverb/Pusher + Laravel Echo para recursos em tempo real
- S3/Flysystem e múltiplos discos para anexos e exports
- WhatsApp via API da Dynamus
- Google reCAPTCHA no login
- Telegram como canal de log
- Geolocalização por IP em assinatura digital e por navegador no ponto eletrônico
- SGI para integração de vagas/carta oferta
- Excel/PDF para exportação e evidências

**Arquivos-base**

- `config/queue.php`
- `config/horizon.php`
- `config/broadcasting.php`
- `config/filesystems.php`
- `app/Services/Dynamus/ZapDynamusService.php`
- `app/Services/AssinaturaDigital/AssinaturaDigitalService.php`
- `app/Http/Controllers/Api/IntegraSgiMybpController.php`
- `app/Jobs/JobExportaCihCsvFinal.php`

### Principais riscos técnicos

**Confirmado no código**

- Segredos e chaves hardcoded em código-fonte
- Rotas de exportação protegidas por string fixa embutida na URL
- `CORS` aberto para qualquer origem com credenciais habilitadas
- Controllers, models e jobs muito grandes concentrando regra de negócio
- Cobertura automatizada extremamente baixa para os fluxos críticos legados
- Dependência forte de `withoutGlobalScopes()` em cenários multiempresa
- Validação concentrada em `Validator::make` nos controllers, com apenas 2 Form Requests no projeto
- Mistura de legado Blade/jQuery/Vue inline com Vue 3 mais recente
- Divergência entre scheduler declarado no código e scheduler efetivamente ativo no runtime

**Arquivos-base**

- `app/Services/Dynamus/ZapDynamusService.php`
- `resources/views/home.blade.php`
- `routes/web.php`
- `config/cors.php`
- `app/Http/Controllers/AdmissaoController.php`
- `app/Models/Sistema.php`
- `tests/*`
- `app/Http/Requests/*`
- `app/Console/Kernel.php`
- `bootstrap/app.php`

### Nível de maturidade percebido

**Inferido**

O sistema aparenta ser funcionalmente maduro e rico em regras de negócio, mas tecnicamente heterogêneo. Há sinais de evolução recente em assinatura digital, NPS, aprovação extra e exportadores padronizados; ao mesmo tempo, o núcleo legado mantém alto acoplamento, baixa testabilidade e forte dependência de conhecimento tácito.

**Evidências que sustentam a inferência**

- Módulos novos com services específicos, migrations recentes e testes direcionados: `app/Services/AssinaturaDigital/*`, `tests/Unit/AssinaturaDigitalServiceTest.php`, `database/migrations/2026_02_24_000001_create_documento_para_assinatura_table.php`
- Núcleo legado grande e distribuído: `app/Http/Controllers/AdmissaoController.php`, `app/Http/Controllers/RecrutamentoController.php`, `app/Models/Admissao.php`, `app/Models/Sistema.php`
- Plano explícito de migração frontend ainda em andamento: `docs/PLANO_MIGRACAO_COMPOSITION_API_SERVICES.md`

## Leitura do estado atual

### Confirmado no código

- O repositório é grande e centraliza backend, frontend, infraestrutura Docker, jobs e documentação parcial.
- O backend tem 157 controllers PHP, 234 models, 130 jobs, 60 services, 68 mails e 14 comandos console.
- O frontend tem 151 componentes reutilizáveis, 113 views internas `resources/views/g`, 73 views PDF e 73 templates de e-mail.
- Há 608 arquivos em `database/`, sendo 407 migrations somente em 2021 e 49 em 2026.
- Há apenas 7 arquivos de teste no repositório.
- O runtime validado no container expõe Laravel `12.53.0`, PHP `8.2.8`, `mysql`, `redis`, `smtp` e `pusher`.
- O runtime validado via `php artisan route:list --json` expõe `1329` rotas.
- O runtime validado via `php artisan schedule:list` retornou `No scheduled tasks have been defined`, apesar de `app/Console/Kernel.php` declarar tarefas.

**Arquivos-base**

- Estrutura do repositório em `app/`, `database/`, `resources/`, `routes/`, `tests/`
- `phpunit.xml`

### Lacunas de documentação encontradas

**Confirmado no código**

- O `README.md` menciona `docs/README.md`, mas esse arquivo não existe hoje.
- A documentação em `docs/` está focada em migração Vue 3 e impactos pontuais, não em uma spec do sistema como produto.
- Não há documentação consolidada do modelo de domínio, fluxos críticos, integração externa e contratos operacionais.

**Arquivos-base**

- `README.md`
- `docs/ANALISE_VUE3_CONFORMIDADE.md`
- `docs/PLANO_MIGRACAO_COMPOSITION_API_SERVICES.md`
- `docs/VUE3_MODAL_SWEEP*.md`
