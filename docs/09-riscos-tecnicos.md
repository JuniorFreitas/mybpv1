# 09. Riscos Tecnicos

> Convenções usadas neste documento
> - Confirmado no código: risco sustentado diretamente pelos arquivos analisados.
> - Inferido: risco derivado da combinação de evidências.
>
> **Atualização 2026-11-23 (Housekeeping):** coluna Status revalidada no código. Detalhes históricos nas seções abaixo podem estar datados — confiar no Status da tabela. Board ativo: `.harness/backlog/INDEX.md`.

## Tabela de riscos

| ID | Risco | Tipo | Severidade | Status (2026-11-23) | Evidência principal |
|---|---|---|---|---|---|
| R1 | Credenciais hardcoded (ZapDynamus) | Segurança | Crítica | **Mitigado no código** (key via `ZAPDYNAMUS_API_KEY`; **rotacionar no provedor** antes do próximo deploy) | `config/services.php`, `ZapDynamusService.php` |
| R2 | Rotas de exportação por segredo na URL | Segurança | Crítica | **Resolvido** (`can:` nas rotas) | `routes/web.php` |
| R3 | `CORS` `*` com credentials | Segurança | Alta | **Resolvido** (`CORS_ALLOWED_ORIGINS`) | `config/cors.php` |
| R4 | Controllers/models gigantes | Manutenibilidade | Alta | **Aberto** (TD-002) | `AdmissaoController.php` |
| R5 | Cobertura de testes baixa em fluxos críticos | Qualidade | Alta | **Parcial** (regressões ASO/Recaptcha/Zap; QA-001 restante) | `tests/*` |
| R6 | `withoutGlobalScopes()` multi-tenant | Segurança | Alta | **Parcial** (TD-003 DONE + SEC-008 corrigido; bypasses legítimos restantes em jobs/APIs públicas) | `app/Tenant/*`, `ScopeClientesEmpresa`, services/jobs |
| R7 | Quase sem Form Request | Qualidade | Alta | **Aberto** (TD-001) | `app/Http/Requests/*` |
| R8 | Blade/jQuery + Vue 3 | Arquitetura | Média | **Aberto** (FE-001; build já é Vite) | `resources/js/g/*` |
| R9 | `setDataAsoAttribute` zera valor | Funcional | Alta | **Resolvido** (BUG-001) | `app/Models/Admissao.php` |
| R10 | Cache documental `forget` indevido | Performance | Média | **Resolvido** | `DocumentosCurriculosAdmissaoEmpresa` |
| R11 | Null em docs pré-admissionais | Funcional | Média | **Hipótese** (revalidar) | `DocumentosPreAdmissaoController` |
| R12 | Typo `filesystemzs` | Funcional | Média | **Resolvido** | — |
| R13 | Troca obrigatória de senha inconsistente | Segurança/UX | Média | **Aberto** (inferido) | Login + CheckPasswordReset |
| R14 | Middleware `configuracao_habilidades` | Funcional | Média | **Resolvido** (`can:`) | `routes/web.php` |
| R15 | `ssh root@` em comando | Operação | Alta | **Mitigado** (env `MYBP_DUMP_*`) | `routes/MybpCommand.php` |
| R16 | Dockerfile remove `composer.lock` | Build | Alta | **Resolvido** | `Dockerfile` |
| R17 | Horizon só `id=1` | Governança | Média | **Resolvido** (Gate + env) | `HorizonServiceProvider` |
| R18 | `docs/README.md` ausente | Governança | Média | **Resolvido** | `docs/README.md` |
| R19 | Scheduler inativo | Operação | Crítica | **Resolvido** (`routes/schedule.php`) | `bootstrap/app.php` |

## Achados detalhados

### R1. Credenciais hardcoded

**Confirmado no código**

- `app/Services/Dynamus/ZapDynamusService.php` contém `apikey` literal.
- `resources/views/home.blade.php` contém configuração Firebase completa embutida.

### R2. Segredo de exportação na URL

**Confirmado no código**

Há um grupo de rotas em `routes/web.php` exposto por um prefixo longo estático em vez de autenticação/autorização convencional.

### R3. Política de CORS perigosa

**Confirmado no código**

`config/cors.php` usa `allowed_origins = ['*']` com `supports_credentials = true`.

### R4. Alto acoplamento estrutural

**Confirmado no código**

- `AdmissaoController` com 2499 linhas
- `TreinamentoController` com 1087 linhas
- `Sistema` com 1132 linhas
- `Admissao` com 1012 linhas

### R5. Cobertura de testes insuficiente

**Confirmado no código**

Os testes existentes se concentram em assinatura digital e exportadores mais novos. Não foram encontrados testes relevantes para recrutamento, admissão, planejamento, ponto, exames ou financeiro.

### R6. Risco multi-tenant

**Confirmado no código**

O sistema usa `TenantTrait` e global scope por empresa, mas muitos fluxos críticos desabilitam os scopes para operar sobre links públicos, jobs ou integrações. Isso exige disciplina manual nos filtros.

### R7. Validações heterogêneas

**Confirmado no código**

Só há 2 Form Requests (`Fornecedor`). O restante usa validação ad hoc em controllers, frequentemente repetida.

### R8. Frontend em transição

**Confirmado no código**

A base mistura Vue 3 com mixins, jQuery/Bootstrap modal, e páginas Blade com `Vue.createApp` inline.

### R9. Bug de `data_aso`

**Confirmado no código**

`app/Models/Admissao.php` define `data_aso = null` fora do `else`, anulando qualquer valor válido atribuído.

### R10. Cache documental inválido

**Confirmado no código**

`DocumentosCurriculosAdmissaoEmpresa::getDocumentoCurriculoAdmissaoEmpresa()` faz `Cache::forget()` antes de `Cache::get()`, inutilizando o cache.

### R11. Fluxo de documentos pré-admissionais frágil

**Confirmado no código**

`DocumentosPreAdmissaoController::autenticar()` usa `$candidato->docs_curriculo_pre_adm` antes de verificar se `$candidato` é nulo.

### R12. Typo em URL de download

**Confirmado no código**

`PreAdmissaoController` monta `url_download` usando `config('filesystemzs...')`.

### R13. Troca obrigatória de senha inconsistente

**Inferido com base no código**

O login inicial atualiza `password_changed_at` e força redirect, mas o middleware posterior depende de `isFirstAccess()` e `password_reset_days`, o que pode enfraquecer a obrigação caso o usuário navegue fora do redirect esperado.

### R14. Middleware não registrado

**Confirmado no código**

Rotas usam `configuracao_habilidades` como middleware, mas `bootstrap/app.php` não registra esse alias.

### R15. Operação sensível embutida em comando local

**Confirmado no código**

`routes/MybpCommand.php` executa `ssh root@159.89.154.53` para dump remoto.

### R16. Build não reprodutível

**Confirmado no código**

`Dockerfile` remove `composer.lock` antes de `composer install`, quebrando determinismo de dependências.

### R17. Observabilidade e governança do Horizon

**Confirmado no código**

O acesso a Horizon em ambiente não local depende de `auth()->id() == 1`, sem política mais flexível.

### R18. Déficit documental

**Confirmado no código**

O `README.md` referencia documentação inexistente e a pasta `docs/` atual não cobre o sistema de ponta a ponta.

### R19. Scheduler inativo no runtime

**Confirmado no código**

`app/Console/Kernel.php` declara tarefas para férias, avaliação de experiência, exportações, aniversariantes, ponto, Horizon e outros processos recorrentes.

**Confirmado no runtime**

No container em execução, `php artisan schedule:list` retornou `No scheduled tasks have been defined`.

**Inferido**

Há quebra de registro entre a agenda declarada e o bootstrap efetivo do Laravel 12. O impacto provável é ausência de execuções automáticas que o time acredita estarem ativas.
