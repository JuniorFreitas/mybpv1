# Sistema de Notificações Recursivas - Admissões Previstas

## 📋 Visão Geral

O sistema de notificações foi **completamente refatorado** para usar uma abordagem **recursiva e inteligente**, com apenas **UM Job e UM template de email** que gerencia automaticamente todos os fluxos de aprovação.

## ✅ Arquitetura Simplificada

### Antes (Sistema Antigo)

-   ❌ 3 Jobs separados (JobNotificacaoAprovacaoExtra, JobNotificacaoAprovacaoRH, JobNotificacaoAprovacao)
-   ❌ Métodos helper no controller (notificarAprovacaoExtra, notificarRH, notificarUsuario)
-   ❌ Lógica de notificação espalhada pelo controller
-   ❌ Difícil manutenção e debug

### Agora (Sistema Recursivo)

-   ✅ **1 Job único**: `JobNotificacaoRecursiva`
-   ✅ **1 Mailable**: `NotificacaoAprovacaoMail` (com 9 tipos de assunto)
-   ✅ **1 Template**: `notificacao_aprovacao.blade.php` (dinâmico)
-   ✅ **Lógica centralizada**: Job determina automaticamente destinatários e tipo
-   ✅ **Fácil manutenção**: toda lógica em um lugar

## 🔄 Fluxo Recursivo

### Funcionamento

1. **Controller** chama apenas: `JobNotificacaoRecursiva::dispatch($admissao)`
2. **Job analisa** o status atual da admissão
3. **Job determina** automaticamente:
    - Tipo de notificação
    - Destinatários apropriados
    - Assunto do email
    - Mensagem contextual
4. **Job envia** emails para todos os envolvidos

### Tipos de Notificação (9 Cenários)

| Tipo                        | Quando Ocorre                                        | Destinatários                            |
| --------------------------- | ---------------------------------------------------- | ---------------------------------------- |
| `criacao`                   | Admissão criada                                      | Solicitante                              |
| `pendente_aprovacao_extra`  | Gestor aprovou + tem config extra                    | Equipe Extra + Solicitante               |
| `pendente_aprovacao_rh`     | Aprovação extra aprovada OU gestor aprovou sem extra | RH + Solicitante + Gestor                |
| `reprovado_gestor`          | Gestor reprovou                                      | Solicitante + Gestor + RH                |
| `reprovado_aprovacao_extra` | Aprovação extra reprovou                             | Solicitante + Gestor + Equipe Extra + RH |
| `reprovado_rh`              | RH reprovou                                          | Solicitante + Gestor + Equipe Extra + RH |
| `cancelado`                 | Solicitação cancelada                                | Todos os envolvidos                      |
| `aprovado_final`            | RH aprovou                                           | Todos os envolvidos                      |

## 📁 Arquivos do Sistema

### 1. Job Recursivo

**Localização**: `app/Jobs/AdmissoesPrevista/JobNotificacaoRecursiva.php`

**Métodos principais:**

-   `handle()` - Ponto de entrada
-   `determinarTipoNotificacao()` - Analisa status e retorna tipo
-   `buscarDestinatarios()` - Busca emails baseado no tipo
-   `enviarEmail()` - Envia com BCC

**Lógica de determinação:**

```php
// Analisa status atual
if (status_aprovacao === 'reprovado') → 'reprovado_gestor'
if (status_aprovacao === 'cancelado') → 'cancelado'
if (status_aprovacao_extra === 'reprovado') → 'reprovado_aprovacao_extra'

// Determina próxima etapa se aprovado
if (status_aprovacao === 'aprovado') {
    if (tem config extra E não tem status_aprovacao_extra) → 'pendente_aprovacao_extra'
    if (status_aprovacao_extra === 'aprovado') → 'pendente_aprovacao_rh'
    sem config extra → 'pendente_aprovacao_rh'
}

if (status_aprovacao_rh === 'aprovado') → 'aprovado_final'
if (status_aprovacao_rh === 'reprovado') → 'reprovado_rh'
```

### 2. Mailable

**Localização**: `app/Mail/AdmissoesPrevista/NotificacaoAprovacaoMail.php`

**Assuntos dinâmicos:**

```php
'criacao' => "Nova Admissão Prevista Criada - {cargo}"
'pendente_aprovacao_extra' => "Admissão Prevista Pendente - Aprovação Extra - {cargo}"
'pendente_aprovacao_rh' => "Admissão Prevista Pendente - Aprovação RH - {cargo}"
'reprovado_gestor' => "Admissão Prevista REPROVADA pelo Gestor - {cargo}"
'reprovado_aprovacao_extra' => "Admissão Prevista REPROVADA pela Aprovação Extra - {cargo}"
'reprovado_rh' => "Admissão Prevista REPROVADA pelo RH - {cargo}"
'cancelado' => "Admissão Prevista CANCELADA - {cargo}"
'aprovado_final' => "Admissão Prevista APROVADA - {cargo} - {data}"
```

### 3. Template de Email

**Localização**: `resources/views/email/admisoesprevista/notificacao_aprovacao.blade.php`

**Recursos:**

-   Mensagens contextuais baseadas no tipo
-   Exibe motivos de reprovação (se houver)
-   Botão "Visualizar no Sistema" (exceto para status finais)
-   Cores dinâmicas (verde para aprovado, vermelho para reprovado)
-   Informações completas da admissão

### 4. Controller

**Localização**: `app/Http/Controllers/AdmissoesPrevistaController.php`

**Mudanças:**

-   ❌ Removidos: `notificarAprovacaoExtra()`, `notificarRH()`, `notificarUsuario()`
-   ✅ Adicionado em `aprovar()`: `JobNotificacaoRecursiva::dispatch($admissao)`
-   ✅ Adicionado em `aprovarExtra()`: `JobNotificacaoRecursiva::dispatch($admissao)`
-   ✅ Adicionado em `aprovarRH()`: `JobNotificacaoRecursiva::dispatch($admissao)`

## 🎯 Exemplo de Uso

### Gestor Aprova (COM Aprovação Extra)

```php
// Controller
$admissao->update(['status_aprovacao' => 'aprovado']);
JobNotificacaoRecursiva::dispatch($admissao);

// Job detecta:
// - status_aprovacao = 'aprovado'
// - Existe config extra ativa
// - status_aprovacao_extra = null
// Resultado: tipo = 'pendente_aprovacao_extra'

// Destinatários:
// - Equipe de aprovação extra (usuarios_autorizados)
// - Solicitante

// Email:
// Assunto: "Admissão Prevista Pendente - Aprovação Extra - Desenvolvedor PHP"
// Mensagem: "Uma Admissão Prevista está aguardando sua Aprovação Extra."
```

### Aprovação Extra Reprova

```php
// Controller
$admissao->update(['status_aprovacao_extra' => 'reprovado', 'obs_aprovacao_extra' => 'Orçamento indisponível']);
JobNotificacaoRecursiva::dispatch($admissao);

// Job detecta:
// - status_aprovacao_extra = 'reprovado'
// Resultado: tipo = 'reprovado_aprovacao_extra'

// Destinatários:
// - Solicitante
// - Gestor que aprovou inicialmente
// - Pessoa que reprovou (aprovacao_extra_id)
// - RH (para ciência)

// Email:
// Assunto: "Admissão Prevista REPROVADA pela Aprovação Extra - Desenvolvedor PHP"
// Mensagem: "Uma Admissão Prevista foi REPROVADA pela Aprovação Extra."
// Exibe: "Motivo da Reprovação (Aprovação Extra): Orçamento indisponível"
```

### RH Aprova (Final)

```php
// Controller
$admissao->update(['status_aprovacao_rh' => 'aprovado']);
JobNotificacaoRecursiva::dispatch($admissao);

// Job detecta:
// - status_aprovacao_rh = 'aprovado'
// Resultado: tipo = 'aprovado_final'

// Destinatários:
// - Solicitante
// - Gestor
// - Pessoa da aprovação extra (se houver)
// - RH

// Email:
// Assunto: "Admissão Prevista APROVADA - Desenvolvedor PHP - 2026-02-15"
// Mensagem: "Uma Admissão Prevista foi APROVADA com sucesso!"
// SEM botão "Visualizar no Sistema" (processo concluído)
```

## 🔍 Debug e Logs

### Logs Gerados

```log
=== JOB NOTIFICAÇÃO RECURSIVA - INICIADO ===
Admissão ID: 123
Status Aprovação: aprovado
Status Aprovação Extra: null
Status RH: null
Tipo de notificação: pendente_aprovacao_extra
Destinatários encontrados: 3
Email principal: gerente@exemplo.com
BCC: diretor@exemplo.com, financeiro@exemplo.com
Email enviado com sucesso
=== JOB NOTIFICAÇÃO RECURSIVA - CONCLUÍDO ===
```

### Como Monitorar

```bash
# Ver logs em tempo real
tail -f storage/logs/laravel.log | grep "NOTIFICAÇÃO RECURSIVA"

# Ver apenas tipos de notificação
tail -f storage/logs/laravel.log | grep "Tipo de notificação"

# Ver destinatários
tail -f storage/logs/laravel.log | grep "Destinatários encontrados"
```

## 🚀 Vantagens do Sistema Recursivo

### 1. Manutenção Simplificada

-   Toda lógica em **1 arquivo** (JobNotificacaoRecursiva.php)
-   Fácil adicionar novos tipos de notificação
-   Fácil adicionar novos destinatários

### 2. Escalabilidade

-   Adicionar novo status? → 1 linha no `match()` do tipo
-   Adicionar novo destinatário? → 1 case no `switch()` dos destinatários
-   Não precisa modificar controller

### 3. Consistência

-   **Todos os fluxos** usam o mesmo Job
-   **Todos os emails** têm o mesmo layout
-   **Todos os logs** seguem o mesmo padrão

### 4. Testabilidade

-   Testar notificações? → Testar apenas 1 Job
-   Mockar emails? → Mockar apenas 1 Mailable
-   Debug? → Apenas 1 lugar para procurar

### 5. Recursividade Real

-   **Não envia para próxima etapa** se reprovado/cancelado
-   **Envia recursivamente** para todos os envolvidos em caso de reprovação
-   **Determina automaticamente** qual a próxima etapa

## 📚 Comparação: Antes vs Agora

### Adicionar Novo Destinatário

**Antes:**

1. Modificar `notificarAprovacaoExtra()` no controller
2. Modificar `notificarRH()` no controller
3. Modificar `notificarUsuario()` no controller
4. Verificar 3 Jobs diferentes
5. Testar 3 fluxos separados

**Agora:**

1. Adicionar 1 linha no `buscarDestinatarios()` do Job
2. Pronto! ✅

### Adicionar Novo Status

**Antes:**

1. Criar novo método `notificarNovoStatus()`
2. Criar novo `JobNotificacaoNovoStatus`
3. Adicionar chamada no controller
4. Adicionar assunto no Mailable
5. Modificar template

**Agora:**

1. Adicionar 1 linha no `determinarTipoNotificacao()`
2. Adicionar 1 case no `buscarDestinatarios()`
3. Adicionar 1 linha no assunto do Mailable
4. Pronto! ✅

## ⚠️ Importante

### Sempre usar JobNotificacaoRecursiva

```php
// ✅ CORRETO
JobNotificacaoRecursiva::dispatch($admissao);

// ❌ ERRADO (Jobs antigos - NÃO usar mais)
JobNotificacaoAprovacaoExtra::dispatch($admissao, $emails);
JobNotificacaoAprovacaoRH::dispatch($admissao);
JobNotificacaoAprovacao::dispatch($admissao, $email);
```

### Recarregar admissão antes de notificar

```php
// ✅ CORRETO
$admissao->update(['status_aprovacao' => 'aprovado']);
JobNotificacaoRecursiva::dispatch($admissao); // Usa dados atualizados

// ❌ ERRADO
JobNotificacaoRecursiva::dispatch($admissao); // Usa dados antigos
$admissao->update(['status_aprovacao' => 'aprovado']);
```

## 🎓 Resumo

O novo sistema de notificações recursivas:

-   ✅ **Usa 1 Job para tudo**
-   ✅ **Determina automaticamente** destinatários e tipo
-   ✅ **Não envia para próxima etapa** em caso de reprovação
-   ✅ **Envia recursivamente** para todos os envolvidos
-   ✅ **Logs detalhados** para debug
-   ✅ **Fácil manutenção** - tudo centralizado
-   ✅ **Escalável** - fácil adicionar novos fluxos

**Um Job para dominar todos! 🎯**
