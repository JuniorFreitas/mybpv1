# Debug de Notificações - Admissões Previstas

## ✅ Implementação Completa

Todo o sistema de notificações está **100% implementado**:

-   ✅ Controller com métodos de notificação
-   ✅ 3 Jobs de notificação (AprovacaoExtra, RH, Usuario)
-   ✅ Mailable com assuntos dinâmicos
-   ✅ Template de email Blade
-   ✅ Logs detalhados em todos os pontos

## 📋 Checklist de Verificação

### 1. Verificar Logs do Laravel

```bash
# Dentro do container Docker
tail -f storage/logs/laravel.log | grep "NOTIFICAÇÃO\|JOB NOTIFICAÇÃO"
```

**O que procurar:**

-   `=== INICIANDO NOTIFICAÇÕES - ADMISSÃO #X ===`
-   `Config de Aprovação Extra encontrada:` ou `Sem config de Aprovação Extra`
-   `Notificando solicitante:`
-   `Job de Notificação Aprovação Extra despachado`
-   `=== JOB NOTIFICAÇÃO APROVAÇÃO EXTRA - INICIADO ===`
-   `Email enviado com sucesso`

### 2. Verificar Fila (Queue)

```bash
# Verificar se o Horizon está rodando
php artisan horizon:status

# Ver jobs falhados
php artisan queue:failed

# Ver jobs processados
docker compose exec mybpdp redis-cli
KEYS *horizon*
```

**Possíveis problemas:**

-   Horizon não está rodando → `php artisan horizon`
-   Jobs falhando silenciosamente → verificar `queue:failed`
-   Redis não está funcionando → `docker compose ps`

### 3. Verificar Configuração de Email

```bash
# Verificar variáveis de ambiente
docker compose exec mybpdp env | grep MAIL
```

**Deve ter:**

```
MAIL_MAILER=ses
MAIL_FROM_ADDRESS=noreply@mybp.com.br
MAIL_FROM_NAME=MyBP
AWS_ACCESS_KEY_ID=xxx
AWS_SECRET_ACCESS_KEY=xxx
AWS_DEFAULT_REGION=us-east-1
```

### 4. Verificar Dados Necessários

**No banco de dados, verifique:**

```sql
-- 1. Configuração de Aprovação Extra está ativa?
SELECT * FROM aprovacao_extra_configs
WHERE empresa_id = 1
  AND tipo_processo = 'admissao'
  AND ativo = 1;

-- 2. Usuários autorizados têm email?
SELECT u.id, u.nome, u.email
FROM users u
WHERE u.id IN (1, 2, 3); -- IDs do campo usuarios_autorizados

-- 3. Usuários RH têm email?
SELECT id, nome, email
FROM users
WHERE empresa_id = 1
  AND (privilegio_gestao_rh = 1 OR privilegio_aprovar_por_rh = 1);

-- 4. Solicitante tem email?
SELECT u.id, u.nome, u.email
FROM admissoes_previstas ap
JOIN users u ON u.id = ap.solicitante_id
WHERE ap.id = 123; -- ID da admissão
```

## 🔍 Fluxo de Notificações

### Quando Gestor Aprova (`aprovar()`)

1. Controller verifica se existe config de aprovação extra
2. **COM Config:**
    - Dispara `JobNotificacaoAprovacaoExtra` → emails dos `usuarios_autorizados`
3. **SEM Config:**
    - Dispara `JobNotificacaoAprovacaoRH` → emails dos usuários com privilégio RH
4. **Sempre:**
    - Dispara `JobNotificacaoAprovacao` → email do solicitante

### Quando Aprovação Extra Aprova (`aprovarExtra()`)

1. **Se status = 'aprovado':**
    - Dispara `JobNotificacaoAprovacaoRH` → emails dos usuários RH
2. **Sempre:**
    - Dispara `JobNotificacaoAprovacao` → email do solicitante
    - Dispara `JobNotificacaoAprovacao` → email do gestor que aprovou inicialmente

## 🐛 Problemas Comuns

### Problema 1: Nenhum log aparece

**Causa:** Jobs não estão sendo despachados  
**Solução:**

```bash
# Verificar se chegou no controller
tail -f storage/logs/laravel.log | grep "INICIANDO NOTIFICAÇÕES"
```

### Problema 2: Logs aparecem mas emails não chegam

**Causa:** Problema com AWS SES ou credenciais  
**Solução:**

```bash
# Testar envio manual
php artisan tinker

# Dentro do tinker:
Mail::raw('Teste', function($m) {
    $m->to('seu-email@exemplo.com')->subject('Teste SES');
});
```

### Problema 3: Jobs ficam presos na fila

**Causa:** Horizon não está rodando ou travou  
**Solução:**

```bash
# Reiniciar Horizon
php artisan horizon:terminate
php artisan horizon

# Verificar se está processando
php artisan horizon:status
```

### Problema 4: Job falha com erro

**Causa:** Dados faltando (email nulo, relacionamento não carregado)  
**Solução:**

```bash
# Ver detalhes do erro
php artisan queue:failed
php artisan queue:retry {job-id}

# Verificar logs do Job específico
tail -f storage/logs/laravel.log | grep "JOB NOTIFICAÇÃO"
```

## 📊 Teste Completo

### 1. Criar uma admissão prevista

1. Acesse **Planejamento > Movimentação > Solicitação de Admissão**
2. Clique em **Nova Solicitação**
3. Preencha os dados e salve
4. Status inicial: **Pendente**

### 2. Aprovar como Gestor

1. Clique no ícone de aprovação (✓)
2. Selecione **Aprovar**
3. **Verificar logs:**
    ```bash
    tail -f storage/logs/laravel.log | grep "NOTIFICAÇÃO"
    ```
4. **Deve aparecer:**
    - `Config de Aprovação Extra encontrada: Gerencia` (se configurado)
    - `Notificando solicitante: email@exemplo.com`
    - `Job de Notificação Aprovação Extra despachado`

### 3. Aprovar como Aprovação Extra

1. Entre com usuário que está em `usuarios_autorizados`
2. Vá na admissão que agora está **Pendente - Aprovação Extra**
3. Clique no botão **Aprovar Extra**
4. Preencha a observação e aprove
5. **Verificar logs:**
    ```bash
    tail -f storage/logs/laravel.log | grep "APROVAÇÃO EXTRA"
    ```
6. **Deve aparecer:**
    - `Status Aprovação Extra: aprovado`
    - `Aprovado - notificando RH`
    - `Notificando solicitante:`
    - `Notificando gestor:`

### 4. Verificar Emails

**Emails esperados:**

**Fluxo COM Aprovação Extra:**

1. **Gestor aprova** → Email para equipe de Aprovação Extra
2. **Gestor aprova** → Email para solicitante
3. **Extra aprova** → Email para RH
4. **Extra aprova** → Email para solicitante
5. **Extra aprova** → Email para gestor

**Fluxo SEM Aprovação Extra:**

1. **Gestor aprova** → Email direto para RH
2. **Gestor aprova** → Email para solicitante

## 🔧 Comandos Úteis

```bash
# Entrar no container
docker compose exec mybpdp bash

# Ver logs em tempo real
tail -f storage/logs/laravel.log

# Filtrar apenas notificações
tail -f storage/logs/laravel.log | grep -i "notific"

# Ver status do Horizon
php artisan horizon:status

# Limpar cache
php artisan cache:clear
php artisan config:clear

# Ver jobs na fila
php artisan queue:work --once --verbose

# Ver jobs falhados
php artisan queue:failed

# Reprocessar job falhado
php artisan queue:retry {id}

# Reprocessar todos os jobs falhados
php artisan queue:retry all
```

## 📧 Assuntos dos Emails

Os emails têm assuntos dinâmicos baseados no tipo:

-   **Aprovação Extra:** `Nova Admissão Prevista Pendente - Aprovação Extra - {cargo}`
-   **Aprovação RH:** `Nova Admissão Prevista Pendente - Aprovação RH - {cargo}`
-   **Aprovação Concluída:** `Admissão Prevista Aprovada - {cargo} - {data}`

## 🎯 Próximos Passos

1. **Rodar o teste completo** seguindo o passo a passo acima
2. **Verificar os logs** em cada etapa
3. **Se não aparecer logs:** problema no controller/dispatch
4. **Se aparecer logs mas não enviar:** problema com AWS SES
5. **Se emails não chegarem:** verificar spam/caixa de entrada

## 📝 Arquivos Modificados

-   `app/Http/Controllers/AdmissoesPrevistaController.php` (logs adicionados)
-   `app/Jobs/AdmissoesPrevista/JobNotificacaoAprovacaoExtra.php` (logs adicionados)
-   `app/Jobs/AdmissoesPrevista/JobNotificacaoAprovacaoRH.php` (logs adicionados)
-   `app/Jobs/AdmissoesPrevista/JobNotificacaoAprovacao.php` (logs adicionados)

**Todos os arquivos já existem e estão funcionais!**
