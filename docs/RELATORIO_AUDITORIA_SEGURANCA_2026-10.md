# Relatório de Auditoria de Segurança — MyBP

**Data:** 2026-10-08 · **Branch:** `master` (`d2ecfe97`) · **Tipo:** revisão estática manual (white-box)
**Stack:** Laravel 12.61 / PHP 8.2 / Sanctum 4.3 / Horizon / Reverb / Vue 3

## 1. Escopo e limitações (leia primeiro)

Foi feita leitura direcionada de rotas (`routes/web.php` ~1080 rotas, `routes/api.php`), middlewares, autenticação, upload/download de arquivos, uso de SQL cru, `exec`, `v-html`/`{!! !!}`, configurações (`config/*`, `.env*.example`, Docker), escopos multi-tenant e busca por segredos no código e no histórico git.

**Não foi feito:** teste dinâmico/pentest em ambiente rodando; `composer audit` (composer não instalado nesta máquina); `npm audit` (não há `package-lock.json` rastreado); revisão de todos os 155 controllers e 247 models. Portanto **ausência de achado não prova ausência de falha** — os controllers fora dos fluxos citados devem passar pelo mesmo crivo (ver §6). As versões de dependências foram lidas do `composer.lock` mas não cruzadas com base de CVEs.

## 2. Resumo executivo

| Sev. | Qtd | Destaques |
|------|-----|-----------|
| Crítica | 3 | Senha inicial de candidato = 6 primeiros dígitos do CPF; endpoint público que devolve currículo completo (incl. CID/saúde) com CPF + nascimento; endpoint público de cadastro que **sobrescreve currículos existentes** e apaga telefones de terceiros |
| Alta | 5 | Exports sem autenticação atrás de URL "secreta" + `ScopeEmpresa` que falha aberto; reset de senha com token de 6 caracteres sem rate limit; impersonação sem auditoria; anexos sem checagem de tenant (IDOR); tokens Sanctum eternos e em texto puro |
| Média | 6 | Enumeração de usuários, `ApiToken` com `!==`/`env()`, reCAPTCHA com TLS desligado, CORS/Reverb `*`, `TrustProxies='*'`, proxy público de CNPJ |
| Baixa/Info | 6 | `exec` com variáveis, `v-html`, IP/host em código, arquivos de dados no git etc. |

**Pontos positivos:** nenhum segredo/chave hardcoded encontrado no código nem no histórico (busca por padrões AWS/Stripe/Google/GitHub/PEM); `.env*` reais estão no `.gitignore` e não rastreados; SQL cru majoritariamente parametrizado ou com cast `(int)`; CSRF ativo sem exceções; login web usa throttle do Laravel + reCAPTCHA fora de `local`; cookies `http_only`, `same_site=lax`; `AGENTS.md` já define boas práticas de tenant/soft-delete.

---

## 3. Achados — Críticos

### C1. Senha padrão previsível para candidatos (derivada do CPF)
- **Local:** `app/Models/Sistema.php:493` (`SenhaCpf`), usada em `app/Http/Controllers/Api/VagaAbertaController.php:367` (e fluxo equivalente em `IntegracaoSpa*`).
- **Problema:** ao cadastrar currículo, o usuário é criado com `login = e-mail` e `password = bcrypt(6 primeiros dígitos do CPF)`. Espaço de busca real ≈ 10⁶ (muito menos considerando CPFs de um estado/ano), e o e-mail/CPF do candidato é obtido pelos endpoints do C2. O bcrypt não ajuda: a entropia da senha é mínima.
- **Impacto:** tomada de conta de candidatos (dados pessoais, documentos, histórico). Se `User::CANDIDATO` tiver qualquer habilidade além do mínimo, risco se amplia.
- **Correção:** gerar senha aleatória forte e enviar link de definição de senha (token de uso único, curto, com throttle); forçar `require_password_reset` no primeiro login (hoje só ocorre pós-login); invalidar/forçar troca das contas existentes ainda com `password_changed_at IS NULL`.

### C2. Consulta pública de currículo completo com CPF + data de nascimento
- **Local:** `routes/api.php` (`POST busca-curriculo`, `busca-cpf`, e `v2/integracao/{apelido}/busca-curriculo|busca-cpf` ) → `VagaAbertaController::buscaCurriculo` (l.100–182) e `buscaCpf` (l.184).
- **Problema:** sem autenticação. Quem conhece (ou adivinha) CPF + nascimento recebe `rg`, `cnh`, filiação, endereço, e-mail, **`cid` e `pcd` (dado de saúde — LGPD art. 5º II, dado sensível)**, telefones, experiências. A data de nascimento é fator fraco (~25 mil combinações/ano de busca) e o limite é só `throttle:60,1` por IP; a resposta distingue "CPF encontrado, nascimento não confere" (oráculo de enumeração). `buscaCpf` também expõe CPFs mascarados + `created_at`.
- **Impacto:** vazamento/scraping de dados pessoais e sensíveis em massa; infração LGPD.
- **Correção:** não devolver o currículo completo em endpoint anônimo; exigir verificação de posse (OTP por e-mail/SMS ao contato já cadastrado); resposta uniforme (mesmo texto para "não existe" e "não confere"); remover `cid`/documentos da resposta; rate limit por CPF + por IP com bloqueio progressivo; reCAPTCHA.

### C3. Cadastro/edição público sem autenticação: sobrescreve dados e apaga registros de terceiros
- **Local:** `VagaAbertaController::store` (l.271–500), `IntegracaoVagaAbertaController` (l.~410), `IntegracaoSpaCurriculoController::cadastraCurriculo`.
- **Problemas:**
  1. Se já existe usuário com o CPF na empresa (`$editando`), o ramo `else` faz `$curriculo->update($dados)` com **todo o `$request->input()`** — sem provar posse do CPF (nenhuma checagem de nascimento/OTP no `store`). Qualquer pessoa que saiba um CPF altera nome, e-mail, endereço, telefones etc. do candidato.
  2. `TelefoneCurriculo::find($index)->delete()` (l.414 e `IntegracaoVagaAbertaController.php:410`) usa `find` global e ids vindos do cliente (`telefonesDelete`): **qualquer telefone de qualquer currículo/empresa pode ser apagado por ID sequencial**; `find()` retornando `null` gera 500.
  3. Mass assignment: `Curriculo::create($dados)` / `update($dados)` com payload bruto; `empresa_id` e `vaga_aberta_id` vêm do cliente. Depende do `$fillable` de `Curriculo` (conferir se inclui `lido`, `user_id`, `empresa_id`, flags de status).
  4. Ramo "novo usuário" retorna 400 *depois* de criar usuário/currículo dentro da transação sem rollback explícito (inconsistência), e `$vaga_aberta` pode ser `null` → 500 (`->Municipio` em null).
- **Correção:** validar via Form Request com `only()`/lista branca de campos; exigir prova de posse para editar; escopar deleções por relação (`$curriculo->Telefones()->find`); validar `vaga_aberta_id` ∈ `empresa_id` **antes** de usar; não aceitar `empresa_id` do body quando houver apelido na rota.

---

## 4. Achados — Altos

### A1. Exports sem autenticação atrás de URL "secreta" + `ScopeEmpresa` fail-open
- **Local:** `routes/web.php:109` — grupo com prefixo `3hmMaxB0QB0z…` (sem `auth`) contendo `recrutamentos/export`, `parecer_rh/export`, `parecer_rota_transporte/export`, `parecer_entrevista_tecnica/export`, `parecer_teste_pratico/export`, `portaria/export` e `verificaCliente`. A string está **versionada no git** (e em logs de acesso/proxy/histórico de navegador).
- **Agravante:** `app/Scopes/ScopeEmpresa.php:21` só aplica `where empresa_id` `if (auth()->user())`. Sem sessão, o scope **não filtra nada** → a query de `RecrutamentoController::filtro/export` percorre currículos (CPF, RG, e-mail, endereço) de **todas as empresas**. O job é despachado com `auth()->id() = null`; confirmar se o arquivo gerado fica acessível ou se o job falha — de qualquer forma há processamento pesado e escrita em `RecrutamentoHistorico` iniciáveis anonimamente (DoS de custo).
- **Correção:** colocar essas rotas em `['auth','habilidades','can:…']`; remover o prefixo secreto (segurança por obscuridade); **fazer `ScopeEmpresa` falhar fechado** (`whereRaw('1=0')` ou exceção quando não há usuário e o contexto não é console/job explícito). Revisar também o ramo `else` do scope, que faz `auth()->user()->empresa_id` sem null-check.

### A2. Recuperação de senha: token curto, sem rate limit, enumeração
- **Local:** `UserController.php:475` (`solicitaRecuperaSenha`), `:511` (`recuperaSenhaPost`); rotas `routes/web.php:16-17` e `:~85` sem `throttle`.
- **Problemas:** token = `strtoupper(Str::random(6))` (≈36⁶ = 2,2×10⁹; efetivamente menos pois `Str::random` mistura caixa e a comparação é direta) válido por **6 horas** e armazenado em texto puro; `POST /envia-recupera-senha` aceita tentativas ilimitadas e **faz login automático** (`Auth::login`) após acerto; a resposta 404 "Usuário não encontrado" permite enumerar logins; vários tokens ativos por usuário; o token também vai em URL GET (`/recupera-senha/{token}`).
- **Impacto:** tomada de conta por força bruta distribuída, inclusive de usuários administrativos.
- **Correção:** token ≥ 32 bytes aleatórios, armazenado como hash, uso único, expiração 15–60 min, invalidar anteriores; `throttle:5,1` por IP **e** por usuário; resposta idêntica exista ou não o login; não autenticar automaticamente (ou exigir MFA); invalidar sessões/tokens Sanctum ao trocar a senha.

### A3. Impersonação (`simularUsuario`) sem trilha e sem restrições
- **Local:** `UserController.php:571` / `routes/web.php:1368` (sem `can:`).
- **Problema:** `grupo_id == 1` → `Auth::loginUsingId($request->user_id)` para **qualquer** `user_id` (qualquer tenant, inclusive outro admin), sem log de auditoria, sem vínculo "impersonado por", sem opção de retornar, sem validar `ativo`.
- **Correção:** permissão explícita por habilidade, registro em `activity_log` (quem → quem, IP, horário), flag de sessão `impersonated_by` exibida na UI, bloquear alvo de grupo superior, expirar rápido.

### A4. IDOR / falta de checagem de tenant em anexos
- **Local:** `StorageS3Controller::anexoShow`/`download` (rotas `g/storage/anexo/{arquivo}` e `anexoDownload/{arquivo}`); o próprio código tem o comentário "*Fazer a validacao (middleware) de download… aqui se necessario*".
- **Problema:** qualquer usuário autenticado (inclusive **candidato**) com o nome do arquivo obtém qualquer anexo do disco, de qualquer empresa. O nome vem de `hashName` (não sequencial), o que reduz, mas não elimina, o risco: nomes vazam em logs, e-mails, URLs, respostas de API e `Arquivo.file`. Não há verificação de que o `Arquivo` pertence à empresa/entidade do usuário nem de habilidade.
- **Correção:** autorizar via entidade dona (policy: `Arquivo → entidade → empresa_id`) e/ou URL assinada temporária (como já feito para `/g/cloud/*`); servir com `Content-Disposition`/`X-Content-Type-Options: nosniff`.

### A5. Tokens de API: sem expiração, guardados em texto puro, sem throttle dedicado
- **Local:** `Api/LoginController.php:20` (`$usuario->update(['api_token' => $token->plainTextToken])`), `config/sanctum.php:41` (`'expiration' => null`).
- **Problemas:** cada login cria **novo** token sem revogar os anteriores; o token em claro fica em `users.api_token` (dump do banco = acesso imediato a todas as contas de API; Sanctum guarda só o hash justamente para evitar isso); habilidades do token são congeladas na emissão; login por API aceita tentativas até 60/min/IP sem lockout; resposta diferencia usuário desativado (só se senha correta → confirma credencial).
- **Correção:** parar de gravar `api_token`; definir `expiration` (ex.: 480 min) e rotacionar; revogar tokens anteriores; `RateLimiter` por login+IP; resposta uniforme.

---

## 5. Achados — Médios, Baixos e Informativos

| # | Sev. | Achado | Local | Recomendação |
|---|------|--------|-------|--------------|
| M1 | Média | `ApiToken` compara com `!==` (não constante) e lê `env()` em runtime; com `config:cache` `env()` retorna `null`/fica frágil; um único token compartilhado dá acesso a vagas, CBOs, **envio de WhatsApp** (`/api/envia-whats`, aceita `anexo` livre → possível SSRF/abuso no provedor) e integração SGI | `Middleware/ApiToken.php:19`, `routes/api.php` | `hash_equals(config('services.api.token'), …)`; tokens por integração, com rotação; validar/whitelist `anexo` e telefone; throttle |
| M2 | Média | reCAPTCHA com `CURLOPT_SSL_VERIFYPEER=false`; no `catch` retorna um `JsonResponse` (truthy → **fail-open**); `env()` direto | `Rules/Recaptcha.php:38-47` | Usar `Http::asForm()->post()` com TLS verificado; falhar fechado; `config()` |
| M3 | Média | CORS `allowed_origins: ['*']` + `supports_credentials: true` em `api/*`; Reverb `allowed_origins: ['*']` | `config/cors.php:22`, `config/reverb.php:85` | Lista explícita de origens |
| M4 | Média | `TrustProxies::$proxies = '*'` — qualquer cliente pode forjar `X-Forwarded-For/Host/Proto` se a origem for acessível sem passar pelo CDN (afeta `ip` em auditoria/throttle e geração de URLs) | `Middleware/TrustProxies.php:15` | Restringir aos IPs do Cloudflare/LB; liberar a porta 8080 (`docker-compose.prod.yml:35`) só para o proxy |
| M5 | Média | `POST publico/cnpjbusca` anônimo repassa a ReceitaWS sem throttle/cache → esgota cota e permite uso como proxy; `curl` sem timeout. `publico/lista-vagas`, `lista-areas*`, `centro-custos*` anônimos retornam dados internos de **todas** as empresas (`Vaga::whereAtivo`, `CentroCusto` com filiais/razão social) | `PublicoController`, `Sistema.php:395` | Auth ou throttle+cache+timeout; filtrar por tenant; remover `upload`/`download` de teste (stubs sem uso) |
| M6 | Média | Sessão `file` + `encrypt=false`, `secure` depende de env; `AuthenticateSession` comentado no Kernel → trocar senha não derruba outras sessões | `config/session.php`, `Http/Kernel.php` | `SESSION_SECURE_COOKIE=true`, driver redis/db, reativar `AuthenticateSession` |
| B1 | Baixa | `Sistema::exportaExcelPython` usa `exec("python3 $caminho $nome_arquivo $redisNome")` sem `escapeshellarg` — hoje os valores são internos, mas qualquer evolução com nome vindo do usuário vira RCE | `Models/Sistema.php:1123` | `escapeshellarg` ou `Process::run([...])` |
| B2 | Baixa | `Horizon::auth` libera apenas `auth()->id() == 1` (ok), mas depende de um ID fixo; ver a exposição de `/horizon` na rede | `HorizonServiceProvider.php` | Gate por habilidade + IP allow-list |
| B3 | Baixa | `v-html` (≈17 pontos) e `{!! !!}` (13) com conteúdo de usuário: tarefas/comentários do Weekly Report, simulados, ocorrências, feedback, mensagens. Não foi confirmado sanitização no servidor | `resources/js/components/weekly-report/TaskModal.vue:94,103,521`, `Ocorrencia.vue:311`, `FeedbackHistorico.vue:54-57`, etc.; `resources/views/pdf/**`, `email/**` | Sanitizar com DOMPurify (front) e HTMLPurifier (back) no ponto de gravação; `{{ }}` onde não precisa HTML |
| B4 | Baixa | `whereRaw('month(nascimento) =' . $campoMes)` — hoje seguro por `(int)` cast; padrão frágil | `AniversariantesController.php:162,203,260` | Bindings `?` |
| B5 | Baixa | Dados/planilhas versionados no git: `output/*.csv` (admissões), `scripts/**/*.csv|xlsx` (inclusive nomes `*_pis_cpf.csv`), `docs/scripts/*.sql`, `verso_carteira_vale.psd`; pasta `download_anexos_cih/` presente em disco (ignorada, mas é dado sensível); IP/host de servidor em `routes/MybpCommand.php:32` | repositório | Verificar se contêm PII/CPF reais; se sim, remover do histórico (`git filter-repo`) e tratar como incidente LGPD |
| B6 | Info | `carteira/{curriculo}` público depende de `Crypt::decrypt` (ok), porém marca "email aberto" e quebra com 500 em token inválido/registro ausente; `ficha-encaminhamento/{exame}/{token}` usa `$request->token` (resolve via parâmetro de rota, funciona) | `TreinamentoController.php:1490`, `ControleExameController.php:483` | Tratar `DecryptException`; throttle |
| B7 | Info | `minimum-stability: dev` em `composer.json` e pacote `juniorfreitas/phpquery-laravel: dev-master` (fonte mutável); `intervention/image 2.7.2` e `predis 1.1.10` em linhas antigas; TinyMCE 5.10.9 em `public/tinymce` (EOL, XSS conhecidos em versões 5.x) | `composer.json`, `public/tinymce` | Fixar versão/commit; atualizar TinyMCE (≥6/7); rodar `composer audit` e `npm audit` no CI |

---

## 6. Itens não verificados (próximos passos de auditoria)

1. **Rodar `composer audit`** (ou `docker compose exec mybpdp composer audit`) e gerar `package-lock.json` para `npm audit`.
2. **Matriz autorização × rota:** das ~1079 rotas em `routes/web.php`, ~483 usam `can:`; outras dependem de `$this->authorize()` no controller (296 chamadas). Gerar lista de rotas `g.*` sem `can:` **e** sem `authorize()` no método e revisar uma a uma (ex.: `usuarios/simularUsuario`, `autocomplete/*`, `busca-projetos`, `get-pcmso`, `downloads`, `storage/*`).
3. **IDOR por tenant:** onde o model não usa `TenantTrait/ScopeEmpresa` (só ~15 models aplicam o scope explicitamente), route-model-binding com `{curriculo}`, `{admissao}`, `{treinamento}` etc. precisa de checagem de `empresa_id`. Auditar os 247 models.
4. **Mass assignment:** conferir `$fillable` de `Curriculo`, `User` (contém `grupo_id`, `empresa_id`, `api_token`, `ativo`) e qualquer `->update($request->all())`.
5. **Uploads em outros controllers** (`Arquivo::gravaArquivo*`, `uploadAnexos` por módulo): `getMimeType()` é conteúdo, mas a extensão salva vem de `extension()`; verificar discos `visibility => public` em S3 (bucket policy) e tamanho máximo.
6. **Exports Excel/CSV:** sanitizar prefixos `= + - @` (CSV/Excel injection) e conferir o padrão CIH.
7. **Infra:** headers (HSTS, CSP, X-Frame-Options), exposição de MySQL/Redis/Reverb/Horizon, `APP_DEBUG=false` e Debugbar/Ignition em produção, backups, rotação de logs sem PII (`Log::debug` com `getTrace()` em `ClientesController.php:1025`).
8. **Teste dinâmico** dos fluxos C1–C3 e A2 em homologação (com autorização) para confirmar exploração e medir o rate limit real.

---

## 7. Plano de remediação sugerido

| Prazo | Ação | Achados |
|-------|------|---------|
| **Imediato (24–72 h)** | Desativar `busca-curriculo` pública ou reduzir a resposta a "existe / não existe" + OTP; exigir auth nas rotas do prefixo secreto; `throttle` em reset de senha; rotacionar o prefixo/token e `X_API_TOKEN`; parar de gravar `users.api_token` e limpar a coluna | C2, A1, A2, A5 |
| **Curto (1–2 semanas)** | Nova política de senha inicial (link de definição, sem CPF) + forçar troca das contas legadas; trava de posse no cadastro público; `ScopeEmpresa` fail-closed; auditoria + restrições na impersonação; policy de anexos | C1, C3, A1, A3, A4 |
| **Médio (1 mês)** | Expiração Sanctum, CORS/Reverb/TrustProxies restritos, reCAPTCHA corrigido, sanitização HTML, atualização TinyMCE, CI com `composer audit`/`npm audit`/Larastan | M1–M6, B3, B7 |
| **Contínuo** | Matriz de autorização automatizada (teste PHPUnit que falha se rota `g.*` não tiver `can:`/policy), testes de isolamento multi-tenant em SQLite `:memory:`, revisão LGPD (CID, documentos, dados de menores) | §6 |

## 7.1 Remediações aplicadas (2026-10-08)

| Achado | Status | Notas |
|--------|--------|-------|
| C1 | Parcial | `SenhaCpf` gera senha aleatória; candidato com `temp` + `require_password_reset`. Contas legadas com senha=CPF ainda precisam de job de força-troca. |
| C2 | Parcial | Resposta uniforme (sem oráculo nascimento); `cid` removido; throttle. OTP completo ainda pendente. |
| C3 | Feito | Delete de telefone escopado; prova de posse (nascimento); whitelist de campos; vaga ∈ empresa. |
| A1 | Feito | Exports anônimos removidos; `ScopeEmpresa` fail-closed; cron vencimento em `/cron/verifica-clientes-vencimento` + `X-API-TOKEN`. |
| A2 | Feito | Token 64 chars com hash SHA-256, TTL 1h, throttle, sem login automático, resposta sem enumeração. |
| A3 | Feito | Impersonação: mesma empresa, bloqueia admin, activity_log, sessão `impersonated_by`. |
| A4 | Feito | `StorageS3Controller` valida tenant/`quem_enviou` + nosniff. |
| A5 | Feito | Não grava `api_token`; revoga tokens anteriores; Sanctum expiration 480 min; throttle login API. |
| M1 | Feito | `hash_equals` + `config('services.api.token')`. |
| M2 | Feito | reCAPTCHA via Http TLS + fail-closed + config. |
| B1 | Feito | `escapeshellarg` no export Python. |
| B4 | Feito | Binding `?` em aniversariantes. |

**Ops pós-deploy:** rotacionar `X_API_TOKEN`; atualizar cron de vencimento de clientes para a nova URL com header; limpar coluna `users.api_token`; forçar troca de senha em candidatos com `password_changed_at` nulo e senha antiga derivada de CPF.

## 8. Observação LGPD

Os fluxos C1–C3 e A1 expõem **dados pessoais e sensíveis** (CPF, RG, CNH, CID/PCD, filiação, endereço). Se houver indício de acesso indevido (verificar logs do endpoint `busca-curriculo`, picos por IP, tentativas em `/envia-recupera-senha`, acessos ao prefixo secreto), avalie obrigação de comunicação à ANPD e aos titulares (art. 48 da LGPD).
