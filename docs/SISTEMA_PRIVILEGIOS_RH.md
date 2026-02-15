# Sistema de Privilégios RH - MyBP

## ⚠️ ATENÇÃO: NÃO SÃO COLUNAS NO BANCO!

Os privilégios `privilegio_gestao_rh` e `privilegio_aprovar_por_rh` **NÃO são colunas** na tabela `users`.

São verificados através do **sistema de Gates do Laravel**.

---

## Arquitetura

### Relacionamentos

```
User (users table)
├── grupo_id → Papel (papeis table)
    └── habilidades (many-to-many via papeis_habilidades)
        └── Habilidade (habilidades table)
            └── nome: "privilegio_gestao_rh", "privilegio_aprovar_por_rh", etc.
```

### Model User

```php
// Relacionamento com Papel
public function Papel() {
    return $this->hasOne(Papel::class, 'id', 'grupo_id');
}

// Lista habilidades do usuário
public function listaDeHabilidades() {
    if (!$this->papel) return [];

    return $this->papel->habilidades->pluck('nome')->toArray();
}

// Helper para RH
public function temPrivilegioGestaoRh(): bool {
    return (bool)$this->can('privilegio_gestao_rh');
}
```

### Model Papel

```php
// Relacionamento com Habilidades
public function habilidades() {
    return $this->belongsToMany(Habilidade::class, 'papeis_habilidades');
}
```

---

## Como Verificar Privilégios

### ✅ CORRETO - Em Controllers

```php
// Verificar usuário autenticado
if (auth()->user()->can('privilegio_gestao_rh')) {
    // Tem privilégio
}

// Authorize (lança exceção se não tiver)
$this->authorize('privilegio_aprovar_por_rh');

// Verificar outro usuário
if ($user->can('privilegio_gestao_rh')) {
    // Tem privilégio
}

// Verificar múltiplos
if (auth()->user()->can('privilegio_gestao_rh') ||
    auth()->user()->can('privilegio_aprovar_por_rh')) {
    // Tem pelo menos um
}
```

### ✅ CORRETO - Buscar Usuários RH

```php
// SEMPRE usar get() + filter() com can()
$usuariosRH = User::where('empresa_id', $empresaId)
    ->where('ativo', true)
    ->where('tipo', '!=', 'Empresa')
    ->get() // ⚠️ Importante: get() primeiro!
    ->filter(function ($user) {
        return $user->can('privilegio_gestao_rh')
            || $user->can('privilegio_aprovar_por_rh');
    });

// Pegar emails
$emails = $usuariosRH->pluck('login')->toArray();
```

### ❌ ERRADO - Query Direta

```php
// ❌ NÃO FUNCIONA - Colunas não existem!
User::where('privilegio_gestao_rh', true)->get();

// ❌ NÃO FUNCIONA
User::where(function ($query) {
    $query->where('privilegio_gestao_rh', true)
        ->orWhere('privilegio_aprovar_por_rh', true);
})->get();
```

**Erro gerado:**

```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'privilegio_gestao_rh'
```

---

## Exemplos Reais do Sistema

### DemissaoPrevistaController

```php
private function notificarRH(DemissaoPrevista $demissao) {
    $usuariosRH = User::where('empresa_id', auth()->user()->empresa_id)
        ->where('ativo', true)
        ->where('tipo', '!=', 'Empresa')
        ->get()
        ->filter(function ($user) {
            return $user->can('privilegio_gestao_rh')
                || $user->can('privilegio_aprovar_por_rh');
        });

    // Enviar notificações...
}
```

### JobNotificacaoRecursiva (CORRIGIDO)

```php
private function buscarEmailsRH(): array {
    return User::where('empresa_id', $this->admissao->empresa_id)
        ->where('ativo', true)
        ->where('tipo', '!=', 'Empresa')
        ->get() // ⚠️ get() antes do filter()!
        ->filter(function ($user) {
            return $user->can('privilegio_gestao_rh')
                || $user->can('privilegio_aprovar_por_rh');
        })
        ->pluck('login')
        ->filter() // Remove nulls
        ->toArray();
}
```

### AvaliacaoController

```php
private function temPrivilegioGestaoRh() {
    return (bool)in_array(
        'privilegio_gestao_rh',
        auth()->user()->listaDeHabilidades()
    );
}
```

---

## Privilégios Disponíveis

### Gestão de RH

-   `privilegio_gestao_rh` - Acesso completo ao módulo RH
-   `privilegio_aprovar_por_rh` - Pode aprovar processos como RH

### Outros Comuns

-   `privilegio_gestor_area` - Gestor de departamento
-   `privilegio_gestor_centro_custo` - Gestor de centro de custo
-   `privilegio_visualizar_relatorio_ponto` - Visualizar relatórios
-   `privilegio_editar_ponto` - Editar registros de ponto

---

## Checklist de Implementação

Ao criar Jobs/Controllers que precisam notificar RH:

-   [ ] ✅ Usar `User::...->get()->filter($user->can(...))`
-   [ ] ❌ NUNCA usar `where('privilegio_gestao_rh', true)`
-   [ ] ✅ Filtrar por `empresa_id` (multi-tenant)
-   [ ] ✅ Filtrar por `ativo = true`
-   [ ] ✅ Excluir `tipo = 'Empresa'`
-   [ ] ✅ Verificar ambos: `privilegio_gestao_rh` OR `privilegio_aprovar_por_rh`
-   [ ] ✅ Usar `pluck('login')` para emails (coluna 'login', não 'email')

---

## Performance

### ⚠️ Atenção ao N+1

O método `can()` verifica relacionamentos. Para evitar N+1:

```php
// Eager load papel e habilidades
$users = User::with(['Papel.habilidades'])
    ->where('empresa_id', $empresaId)
    ->where('ativo', true)
    ->get()
    ->filter(fn($u) => $u->can('privilegio_gestao_rh'));
```

### Cache em Jobs

```php
class MeuJob {
    private $cacheEmailsRH;

    public function handle() {
        // Buscar UMA VEZ
        $this->cacheEmailsRH = $this->buscarEmailsRH();

        // Usar múltiplas vezes sem requery
        $this->enviarEmail($this->cacheEmailsRH);
    }
}
```

---

## Troubleshooting

### Erro: "Column 'privilegio_gestao_rh' not found"

**Causa**: Tentativa de query direta no where()

**Solução**: Usar `get()->filter($user->can())`

### Usuários RH não recebem emails

**Verifique**:

1. User tem `grupo_id` configurado?
2. Papel existe e tem `habilidades` relacionadas?
3. Habilidade com nome exato (`privilegio_gestao_rh` ou `privilegio_aprovar_por_rh`)?
4. Tabela pivot `papeis_habilidades` tem registros?
5. Campo `login` está preenchido? (não é `email`)

---

## SQL de Verificação

```sql
-- Verificar estrutura de privilégios
SELECT u.id, u.nome, u.login, p.nome as papel,
       GROUP_CONCAT(h.nome) as habilidades
FROM users u
LEFT JOIN papeis p ON p.id = u.grupo_id
LEFT JOIN papeis_habilidades ph ON ph.papel_id = p.id
LEFT JOIN habilidades h ON h.id = ph.habilidade_id
WHERE u.empresa_id = 1
  AND u.ativo = 1
GROUP BY u.id;

-- Usuários com privilégio RH
SELECT u.id, u.nome, u.login
FROM users u
INNER JOIN papeis p ON p.id = u.grupo_id
INNER JOIN papeis_habilidades ph ON ph.papel_id = p.id
INNER JOIN habilidades h ON h.id = ph.habilidade_id
WHERE u.empresa_id = 1
  AND u.ativo = 1
  AND h.nome IN ('privilegio_gestao_rh', 'privilegio_aprovar_por_rh');
```

---

**Última atualização**: 2026-02-07  
**Sistema**: MyBP Laravel 8  
**Padrão**: Laravel Gates + Eloquent Relationships
