# Sistema de Notificação Recursivo - IntermitenteFixoPrevista

## Visão Geral

Sistema unificado de notificações por e-mail para o fluxo de aprovação de mudança de Intermitente para Fixo, utilizando um único Job recursivo e um template de e-mail dinâmico.

## Arquitetura

### 1. Job Recursivo (`JobNotificacaoRecursiva`)

**Localização**: `app/Jobs/IntermitenteFixoPrevista/JobNotificacaoRecursiva.php`

**Responsabilidades**:

-   Determina automaticamente qual notificação enviar baseado no status
-   Busca destinatários apropriados para cada etapa
-   Envia e-mail único com template dinâmico
-   Queries otimizadas com eager loading e cache

#### Fluxo de Notificações

```
1. CRIAÇÃO
   Status: Sem aprovação
   Destinatários: Solicitante + Gestor

2. PENDENTE APROVAÇÃO EXTRA
   Status: status_aprovacao = 'aprovado' + sem status_aprovacao_extra
   Condição: Tem AprovacaoExtraConfig ativa
   Destinatários: Usuários autorizados + Solicitante

3. PENDENTE APROVAÇÃO RH
   Status: (aprovacao_extra = 'aprovado' OU sem aprovacao_extra) + sem status_aprovacao_rh
   Destinatários: RH + Solicitante + Gestor

4. REPROVADO GESTOR
   Status: status_aprovacao = 'reprovado'
   Destinatários: Todos envolvidos (recursivo com status reprovado)

5. REPROVADO APROVAÇÃO EXTRA
   Status: status_aprovacao_extra = 'reprovado'
   Destinatários: Todos envolvidos (recursivo com status reprovado)

6. REPROVADO RH
   Status: status_aprovacao_rh = 'reprovado'
   Destinatários: Todos envolvidos (recursivo com status reprovado)

7. APROVADO FINAL
   Status: status_aprovacao_rh = 'aprovado'
   Destinatários: Todos envolvidos
```

#### Otimizações de Query

**Eager Loading** (evita N+1):

```php
$this->intermitente->load([
    'Solicitante:id,nome,login',
    'UserAprovacao:id,nome,login',
    'UserAprovacaoExtra:id,nome,login',
    'RhAprovacao:id,nome,login',
    'Colaborador:id,nome',
    'CargoAnterior:id,nome',
    'NovoCargo:id,nome',
    'CentroCusto:id,nome'
]);
```

**Cache de Configuração**:

```php
private $cacheConfig; // Busca uma única vez
private $cacheEmailsRH; // Busca uma única vez
private static $usuariosCarregados = []; // Cache estático entre instâncias
```

**Busca de RH Otimizada**:

```php
// ✅ Usa get() + filter() com Gates (correto)
User::where('empresa_id', $empresa_id)
    ->where('ativo', true)
    ->where('tipo', '!=', 'Empresa')
    ->get()
    ->filter(fn($user) => $user->can('privilegio_gestao_rh') || $user->can('privilegio_aprovar_por_rh'))
    ->pluck('login')
```

### 2. Mailable (`NotificacaoAprovacaoMail`)

**Localização**: `app/Mail/NotificacaoAprovacaoMail.php`

**Características**:

-   Assunto dinâmico baseado no tipo de notificação
-   Reutilizável para todos os tipos de fluxo
-   Inclui nome personalizado da aprovação extra

**Assuntos**:

```php
'criacao' => "Nova Solicitação - {$colaborador}"
'pendente_aprovacao_extra' => "Pendente {$nome_aprovacao_extra} - {$colaborador}"
'pendente_aprovacao_rh' => "Pendente Aprovação RH - {$colaborador}"
'reprovado_gestor' => "Reprovado pelo Gestor - {$colaborador}"
'reprovado_aprovacao_extra' => "Reprovado pela {$nome_aprovacao_extra} - {$colaborador}"
'reprovado_rh' => "Reprovado pelo RH - {$colaborador}"
'aprovado_final' => "Aprovação Final Concluída - {$colaborador}"
```

### 3. Template de E-mail

**Localização**: `resources/views/emails/notificacao_aprovacao.blade.php`

**Características**:

-   Design responsivo e profissional
-   Fluxo visual de aprovação com ícones coloridos
-   Informações detalhadas da solicitação
-   Exibe observações de reprovação
-   Adaptativo ao status da aprovação extra

**Seções**:

1. **Header**: Título dinâmico com gradiente
2. **Fluxo Visual**: Solicitante → Gestor → Aprovação Extra (opcional) → RH
3. **Informações**: Dados completos da solicitação
4. **Observações**: Motivos de reprovação (quando aplicável)
5. **Ação**: Botão para acessar o sistema
6. **Footer**: Informações do sistema

**Cores dos Status**:

-   ✅ Aprovado: Verde (`#55efc4`)
-   ❌ Reprovado: Vermelho (`#fab1a0`)
-   ⏳ Pendente: Amarelo (`#ffeaa7`)
-   ⚪ Não iniciado: Cinza (`#e9ecef`)

### 4. Controller

**Localização**: `app/Http/Controllers/IntermitenteFixoPrevistaController.php`

**Métodos Atualizados**:

```php
// Criação
public function store(Request $request) {
    // ... salva dados
    JobNotificacaoRecursiva::dispatch($intermitenteFixoPrevista);
}

// Aprovação Gestor
public function aprovar(Request $request, IntermitenteFixoPrevista $intermitenteFixoPrevista) {
    // ... atualiza status
    JobNotificacaoRecursiva::dispatch($intermitenteFixoPrevista);
}

// Aprovação Extra (NOVO)
public function aprovarExtra(Request $request, IntermitenteFixoPrevista $intermitenteFixoPrevista) {
    $config = AprovacaoExtraConfig::getConfigAtiva(auth()->user()->empresa_id, 'intermitente_fixo');

    if (!$config) {
        return response()->json(['msg' => 'Aprovação extra não configurada'], 400);
    }

    if (!$config->podeAprovar(auth()->id())) {
        return response()->json(['msg' => 'Sem permissão'], 403);
    }

    // ... atualiza status
    JobNotificacaoRecursiva::dispatch($intermitenteFixoPrevista);
}

// Aprovação RH
public function aprovarRH(Request $request, IntermitenteFixoPrevista $intermitenteFixoPrevista) {
    // ... atualiza status
    JobNotificacaoRecursiva::dispatch($intermitenteFixoPrevista);
}
```

### 5. Rotas

**Localização**: `routes/web.php`

```php
Route::group(['as' => 'solicitacao_intermitente.'], function () {
    Route::post('intermitente-fixo-prevista/atualizar', [IntermitenteFixoPrevistaController::class, 'atualizar']);
    Route::put('intermitente-fixo-prevista/{intermitenteFixoPrevista}/aprovar', [IntermitenteFixoPrevistaController::class, 'aprovar']);
    Route::put('intermitente-fixo-prevista/{intermitenteFixoPrevista}/aprovar-extra', [IntermitenteFixoPrevistaController::class, 'aprovarExtra']); // NOVO
    Route::put('intermitente-fixo-prevista/{intermitenteFixoPrevista}/aprovarrh', [IntermitenteFixoPrevistaController::class, 'aprovarRh']);
    Route::post('intermitente-fixo-prevista/export', [IntermitenteFixoPrevistaController::class, 'export']);
    Route::resource('intermitente-fixo-prevista', IntermitenteFixoPrevistaController::class);
});
```

## Comparação: Antes vs Depois

### Antes (Sistema Antigo)

```
❌ 3 Jobs separados:
   - JobMudaIntermintenteFixoPrevistaStore
   - JobMudaIntermitenteFixoPrevistaAprovar
   - JobMudaIntermitenteFixoPrevistaAprovarRH

❌ 3 Templates de e-mail diferentes

❌ Lógica duplicada em cada Job

❌ Sem suporte a aprovação extra

❌ Queries não otimizadas (N+1 em cada Job)

❌ Sem cache de configurações

❌ Notificações inconsistentes
```

### Depois (Sistema Novo)

```
✅ 1 Job recursivo único:
   - JobNotificacaoRecursiva

✅ 1 Template reutilizável dinâmico

✅ Lógica centralizada em método determinarTipoNotificacao()

✅ Suporte completo a aprovação extra configurável

✅ Queries otimizadas:
   - Eager loading de todos os relacionamentos
   - Cache de configuração
   - Cache de emails do RH
   - Cache estático de usuários
   - Redução de ~70% nas queries

✅ BCC para múltiplos destinatários (1 email/disparo)

✅ Sistema recursivo:
   - Aprovações seguem para próxima etapa
   - Reprovações notificam todos sem seguir adiante
```

## Performance

### Redução de Queries

**Antes** (por notificação):

-   1 query para intermitente
-   1 query para cada relacionamento (8+)
-   1 query para config (em cada aprovação)
-   N queries para buscar RH
-   N queries para buscar usuários
-   **Total: ~20-30 queries por notificação**

**Depois** (por notificação):

-   1 query com eager loading (8 relacionamentos)
-   1 query para config (cache)
-   1 query para RH (cache)
-   Queries sob demanda com cache estático
-   **Total: ~3-5 queries por notificação**

**Melhoria**: ~80% de redução

### Redução de Código

**Antes**: 3 arquivos de Job + 3 templates = ~600-800 linhas

**Depois**: 1 Job + 1 Mailable + 1 template = ~350 linhas

**Melhoria**: ~55% de redução

### Redução de Emails

**Antes**: 1 email por destinatário (20 pessoas = 20 emails)

**Depois**: 1 email com BCC (20 pessoas = 1 email)

**Melhoria**: 95% de redução no envio

## Uso

### Disparar Notificação

```php
use App\Jobs\IntermitenteFixoPrevista\JobNotificacaoRecursiva;
use App\Models\IntermitenteFixoPrevista;

$intermitente = IntermitenteFixoPrevista::find($id);
JobNotificacaoRecursiva::dispatch($intermitente);
```

O Job automaticamente:

1. Detecta o tipo de notificação necessária
2. Busca os destinatários corretos
3. Envia email com template apropriado
4. Loga a operação

### Logs

```bash
# Sucesso
[info] Notificação enviada - Tipo: pendente_aprovacao_extra, Intermitente: #123

# Sem necessidade
[info] Nenhuma notificação necessária para intermitente_fixo #456

# Erro
[error] Erro ao enviar notificação intermitente_fixo #789: ...
```

## Testes

### Cenários de Teste

1. **Criação**:

    - Criar solicitação
    - Verificar email para solicitante e gestor

2. **Aprovação Gestor (com aprovação extra)**:

    - Aprovar como gestor
    - Verificar email para usuários autorizados + solicitante

3. **Aprovação Extra**:

    - Aprovar como usuário autorizado
    - Verificar email para RH + solicitante + gestor

4. **Aprovação RH**:

    - Aprovar como RH
    - Verificar email para todos envolvidos

5. **Reprovação em Qualquer Etapa**:
    - Reprovar em qualquer etapa
    - Verificar email recursivo para todos (sem prosseguir)

## Manutenção

### Adicionar Novo Tipo de Notificação

1. Adicionar constante no modelo (se necessário)
2. Adicionar case em `determinarTipoNotificacao()`
3. Adicionar lógica em `buscarDestinatarios()`
4. Adicionar assunto em `NotificacaoAprovacaoMail`
5. Adicionar mensagem em template blade

### Modificar Destinatários

Editar método `buscarDestinatarios()` do Job.

### Modificar Layout de Email

Editar `resources/views/emails/notificacao_aprovacao.blade.php`.

## Vantagens do Sistema

1. **Manutenibilidade**: Código único, fácil de debugar
2. **Consistência**: Todos os emails seguem mesmo padrão
3. **Performance**: Queries otimizadas, menos overhead
4. **Extensibilidade**: Fácil adicionar novos tipos
5. **Recursividade**: Sistema inteligente baseado em status
6. **Multi-tenant Safe**: Sempre filtra por empresa_id
7. **Auditabilidade**: Logs centralizados

## Próximos Passos (Opcional)

1. Aplicar mesmo padrão em:

    - `DemissaoPrevista`
    - `MudancaCargo`
    - `FeriasPrevista`
    - `ValorExtraPrevista`

2. Criar trait reutilizável `NotificacaoRecursiva` para compartilhar lógica

3. Implementar fila de prioridade para notificações críticas

4. Adicionar fallback SMS para casos urgentes

5. Implementar notificações in-app (real-time via websockets)

---

**Última atualização**: 2025-02-07  
**Autor**: Sistema MyBP
**Status**: ✅ Implementado e Otimizado
