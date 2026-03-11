# 09. Riscos Tecnicos

> Convenções usadas neste documento
> - Confirmado no código: risco sustentado diretamente pelos arquivos analisados.
> - Inferido: risco derivado da combinação de evidências.

## Tabela de riscos

| ID | Risco | Tipo | Severidade | Confirmado/Inferido | Evidência principal |
|---|---|---|---|---|---|
| R1 | Credenciais hardcoded em código-fonte | Segurança | Crítica | Confirmado | `app/Services/Dynamus/ZapDynamusService.php`, `resources/views/home.blade.php` |
| R2 | Rotas de exportação protegidas por segredo na URL | Segurança | Crítica | Confirmado | `routes/web.php` |
| R3 | `CORS` liberado para `*` com credenciais | Segurança | Alta | Confirmado | `config/cors.php` |
| R4 | Controllers e models gigantes com regra espalhada | Manutenibilidade | Alta | Confirmado | `app/Http/Controllers/AdmissaoController.php`, `app/Models/Sistema.php`, `app/Models/Admissao.php` |
| R5 | Cobertura automatizada baixa fora dos módulos novos | Qualidade | Alta | Confirmado | `tests/*`, `phpunit.xml` |
| R6 | Dependência de `withoutGlobalScopes()` em fluxo multiempresa | Segurança/consistência | Alta | Confirmado | `app/Services/AssinaturaDigital/AssinaturaDigitalService.php`, `app/Jobs/RequisicaoVaga/JobNotificacaoRecursiva.php`, `app/Http/Controllers/DocumentosPreAdmissaoController.php` |
| R7 | Validação dispersa em controllers; quase sem Form Request | Qualidade | Alta | Confirmado | `app/Http/Requests/*`, `app/Http/Controllers/*` |
| R8 | Legado Blade/jQuery/Vue inline convivendo com Vue 3 modular | Arquitetura | Média | Confirmado | `resources/views/auth/login.blade.php`, `resources/js/g/*/app.js`, `docs/PLANO_MIGRACAO_COMPOSITION_API_SERVICES.md` |
| R9 | Bug provável em `setDataAsoAttribute()` | Funcional | Alta | Confirmado | `app/Models/Admissao.php` |
| R10 | Cache documental ineficaz por `Cache::forget()` imediato | Performance | Média | Confirmado | `app/Models/DocumentosCurriculosAdmissaoEmpresa.php` |
| R11 | Uso de objeto nulo em autenticação de documentos pré-admissionais | Funcional | Média | Confirmado | `app/Http/Controllers/DocumentosPreAdmissaoController.php` |
| R12 | URL de download montada com typo `filesystemzs` | Funcional | Média | Confirmado | `app/Http/Controllers/PreAdmissaoController.php` |
| R13 | Fluxo de troca obrigatória de senha parece incoerente entre login e middleware | Segurança/UX | Média | Inferido forte | `app/Http/Controllers/Auth/LoginController.php`, `app/Http/Middleware/CheckPasswordReset.php`, `app/Models/User.php` |
| R14 | Middleware `configuracao_habilidades` usado em rotas, mas não registrado | Funcional | Média | Confirmado | `routes/web.php`, `bootstrap/app.php` |
| R15 | Operação sensível via comando que executa `ssh root@...` | Operação/segurança | Alta | Confirmado | `routes/MybpCommand.php` |
| R16 | Dockerfile remove `composer.lock` antes do install | Build/reprodutibilidade | Alta | Confirmado | `Dockerfile` |
| R17 | `Horizon` liberado somente ao usuário `id=1` | Operação/governança | Média | Confirmado | `app/Providers/HorizonServiceProvider.php` |
| R18 | Documentação principal apontada no README não existe | Governança | Média | Confirmado | `README.md`, ausência de `docs/README.md` |
| R19 | Scheduler declarado no código, mas inativo no runtime | Operação/automação | Crítica | Confirmado + inferido | `app/Console/Kernel.php`, `bootstrap/app.php` |

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
