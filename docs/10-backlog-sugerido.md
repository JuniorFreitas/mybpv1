# 10. Backlog Sugerido

> Convenções usadas neste documento
> - Itens priorizados pelo risco e pelo efeito cascata na manutenção do sistema atual.
> - Esforço estimado: Baixo / Médio / Alto.

## Quick wins

| Título | Descrição | Impacto | Esforço | Prioridade | Arquivos afetados |
|---|---|---|---|---|---|
| Remover segredos hardcoded | Externalizar apikeys/keys e rotacionar credenciais expostas | Segurança imediata | Baixo | P0 | `app/Services/Dynamus/ZapDynamusService.php`, `resources/views/home.blade.php`, `routes/web.php` |
| Registrar o scheduler real | Migrar/registrar a agenda no bootstrap compatível com Laravel 12 e validar `schedule:list` | Reativa automações críticas | Baixo | P0 | `app/Console/Kernel.php`, `bootstrap/app.php`, `routes/console.php` |
| Corrigir `data_aso` | Ajustar mutator que hoje apaga o valor informado | Evita bug funcional na admissão | Baixo | P0 | `app/Models/Admissao.php` |
| Fechar CORS | Restringir origens e revisar `supports_credentials` | Reduz superfície de ataque | Baixo | P0 | `config/cors.php` |
| Corrigir autenticação de documentos pré-admissionais | Validar null antes de acessar `$candidato` | Corrige erro em fluxo público | Baixo | P1 | `app/Http/Controllers/DocumentosPreAdmissaoController.php` |
| Corrigir typo `filesystemzs` | Ajustar URL de download da pré-admissão | Corrige comportamento quebrado | Baixo | P1 | `app/Http/Controllers/PreAdmissaoController.php` |
| Remover `Cache::forget()` indevido | Restaurar cache documental por empresa | Melhora performance | Baixo | P1 | `app/Models/DocumentosCurriculosAdmissaoEmpresa.php` |
| Corrigir middleware inexistente | Trocar `configuracao_habilidades` por middleware/`can:` válido | Evita erro de rota e inconsistência | Baixo | P1 | `routes/web.php`, `bootstrap/app.php` |
| Criar `docs/README.md` real | Consolidar índice da documentação nova | Governança/documentação | Baixo | P1 | `README.md`, `docs/` |

## Melhorias estruturais

| Título | Descrição | Impacto | Esforço | Prioridade | Arquivos afetados |
|---|---|---|---|---|---|
| Normalizar autorização | Migrar partes críticas para policies/serviços de autorização explícitos | Clareza e segurança | Médio | P1 | `app/Providers/AuthServiceProvider.php`, `app/Http/Middleware/CarregaHabilidades.php`, controllers críticos |
| Formalizar contratos multi-tenant | Reduzir `withoutGlobalScopes()` ad hoc e encapsular bypasses | Reduz risco cross-tenant | Alto | P1 | `app/Tenant/*`, `app/Services/*`, jobs públicos |
| Padronizar validações | Introduzir Form Requests nos fluxos críticos | Menos duplicação e menos regressão | Médio | P1 | `app/Http/Requests/*`, controllers de RH |
| Consolidar camada de exportação | Expandir padrão CIH/export builder para relatórios legados | Consistência operacional | Médio | P2 | `app/Services/*Export*`, jobs de export |
| Padronizar feature flags por empresa | Centralizar leitura/escrita de `ClienteConfig` em serviços | Menos regra espalhada | Médio | P2 | `app/Models/ClienteConfig.php`, `app/Models/Sistema.php`, services/controladores |

## Refatorações

| Título | Descrição | Impacto | Esforço | Prioridade | Arquivos afetados |
|---|---|---|---|---|---|
| Quebrar `AdmissaoController` | Separar listagem, edição, importação, exportação, anexos e regras transacionais | Alto ganho de manutenção | Alto | P1 | `app/Http/Controllers/AdmissaoController.php` |
| Extrair domínio de recrutamento | Tirar regras de histórico, feedback e comunicação do controller | Reduz acoplamento | Alto | P1 | `app/Http/Controllers/RecrutamentoController.php`, `app/Models/FeedbackCurriculo.php` |
| Extrair domínio de ponto | Separar marcação, cálculo e auditoria em services | Reduz risco em operação | Médio | P2 | `app/Http/Controllers/PontoEletronicoController.php`, `app/Models/PontoEletronico.php` |
| Reduzir `Sistema.php` | Fatiar helpers por contexto (auth, notificações, tenant, utilidades) | Menos acoplamento transversal | Alto | P2 | `app/Models/Sistema.php` |
| Migrar frontend tocado para services/composables | Seguir plano já existente, módulo a módulo | Evolução segura do frontend | Médio | P2 | `resources/js/g/*`, `docs/PLANO_MIGRACAO_COMPOSITION_API_SERVICES.md` |

## Riscos críticos a tratar primeiro

| Título | Descrição | Impacto | Esforço | Prioridade | Arquivos afetados |
|---|---|---|---|---|---|
| Revisar rotas públicas/tokenizadas | Auditar assinatura, documentos, provas, export e cloud | Segurança e LGPD | Médio | P0 | `routes/web.php`, controllers públicos |
| Revisar logs e dados sensíveis | Garantir que CPF/IP/documentos só sejam exibidos quando a configuração permitir | Segurança/compliance | Médio | P0 | `app/Services/AssinaturaDigital/AssinaturaDigitalService.php`, `config/logging.php`, controllers |
| Restaurar automações agendadas | Garantir que férias, aniversariantes, avaliação de experiência, ponto e limpeza de export executem de fato | Impacto operacional direto | Baixo | P0 | `app/Console/Kernel.php`, `bootstrap/app.php`, `routes/console.php` |
| Remover operação remota sensível via command | Substituir `ssh root@...` por pipeline/runner seguro | Segurança operacional | Médio | P0 | `routes/MybpCommand.php` |
| Reprodutibilidade do build | Parar de remover `composer.lock` no Docker | Estabilidade de deploy | Baixo | P0 | `Dockerfile` |
| Cobertura mínima dos fluxos críticos | Criar testes para recrutamento, admissão, requisição de vaga, ponto e exames | Reduz regressão | Alto | P1 | `tests/Feature/*`, `tests/Unit/*` |

## Oportunidades de produto

| Título | Descrição | Impacto | Esforço | Prioridade | Arquivos afetados |
|---|---|---|---|---|---|
| Unificar carta oferta legada e assinatura digital | Reduzir coexistência de dois fluxos documentais | Melhor UX e menos suporte | Médio | P2 | `app/Http/Controllers/CartaOfertaController.php`, `app/Services/AssinaturaDigital/*`, `app/Models/CartaOferta.php` |
| Dashboard operacional por etapa do ciclo | Visão ponta a ponta de candidato -> admissão -> treinamento -> ponto | Alto valor gerencial | Médio | P2 | `app/Models/FeedbackCurriculo.php`, relatórios, dashboard |
| Centro de notificações consolidado | Unificar e-mail, realtime e WhatsApp por regra de negócio | Maior previsibilidade | Alto | P3 | jobs, mails, eventos, services |
| APIs externas mais formais | Estruturar integração pública de vagas e SGI com contratos e versionamento real | Menos acoplamento externo | Alto | P3 | `routes/api.php`, `app/Http/Controllers/Api/*` |

## Sequência recomendada

1. Segurança imediata: segredos, CORS, rotas de export, operação remota.
2. Correções de bug confirmadas: `data_aso`, pré-admissão pública, typo de storage, cache documental.
3. Blindagem de fluxo: testes mínimos para recrutamento, admissão, requisição de vaga, assinatura e ponto.
4. Refatoração progressiva dos maiores controladores e do utilitário `Sistema`.
5. Migração gradual do frontend usando o plano existente de Composition API + services.
