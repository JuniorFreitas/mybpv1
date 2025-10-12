# Sistema de Reset Forçado de Senha

Este sistema permite forçar usuários a alterarem suas senhas a cada X dias por motivos de segurança, com validação de senha segura e prevenção de reutilização.

## Funcionalidades Implementadas

### 1. Campos na Tabela Users
- `require_password_reset`: Boolean que habilita/desabilita o reset forçado
- `password_reset_days`: Número de dias para forçar a alteração
- `password_changed_at`: Data da última alteração de senha

### 2. Middleware de Verificação
O middleware `CheckPasswordReset` verifica automaticamente se o usuário precisa alterar a senha em cada requisição.

### 3. Validação de Senha Segura
**Critérios obrigatórios para todas as senhas:**
- ✅ Mínimo de 8 caracteres
- ✅ Pelo menos 1 letra minúscula (a-z)
- ✅ Pelo menos 1 letra maiúscula (A-Z)
- ✅ Pelo menos 1 número (0-9)
- ✅ Pelo menos 1 caractere especial (@$!%*?&)

### 4. Prevenção de Reutilização
- Validação para não permitir usar a senha atual como nova senha
- Verificação tanto no frontend (JavaScript) quanto no backend (PHP)
- Confirmação da senha atual obrigatória para alterações

### 5. Interface Interativa
- **Validação em tempo real** da força da senha
- **Indicador visual** de força com barra de progresso
- **Dicas específicas** sobre critérios não atendidos
- **Botões de mostrar/ocultar senha**
- **Validação de confirmação** em tempo real

### 6. Comando Artisan para Configuração
Use o comando `users:configure-password-reset` para configurar usuários:

```bash
# Habilitar reset forçado para um usuário específico (90 dias)
docker exec app php artisan users:configure-password-reset --user-id=1 --enable --days=90

# Habilitar para todos os usuários de uma empresa (60 dias)
docker exec app php artisan users:configure-password-reset --empresa-id=100 --enable --days=60

# Desabilitar para um usuário específico
docker exec app php artisan users:configure-password-reset --user-id=1 --disable

# Habilitar para todos os usuários do sistema (30 dias)
docker exec app php artisan users:configure-password-reset --enable --days=30
```

### 4. Comportamento do Sistema

#### Quando habilitado:
1. O usuário faz login normalmente
2. O middleware verifica se a senha expirou
3. Se expirou, redireciona para a página de alteração de senha
4. O usuário não consegue acessar outras páginas até alterar a senha
5. Após alterar, a data `password_changed_at` é atualizada

#### Exceções:
- Páginas de alteração de senha (`alterar-senha.*`)
- Logout (`logout`, `sair`)
- Requisições AJAX retornam erro JSON com `require_password_reset: true`

### 5. Métodos Auxiliares no Model User

```php
// Verifica se o usuário precisa alterar a senha
$user->needsPasswordReset(); // retorna true/false

// Atualiza a data da última alteração
$user->updatePasswordChangedAt();
```

### 6. Integração com Controllers

O sistema já está integrado com:
- `LoginController`: Define `password_changed_at` no primeiro login se for null
- `AlterarSenhaController`: Atualiza `password_changed_at` ao alterar senha
- `UserController`: Atualiza `password_changed_at` na recuperação de senha

### 7. Interface do Usuário

A página de alteração de senha mostra um alerta quando a senha está expirada, informando:
- Que a alteração é obrigatória
- Quantos dias é o intervalo configurado
- Data da última alteração (se disponível)

## Exemplos de Uso

### Configurar empresa para reset a cada 90 dias:
```bash
docker exec app php artisan users:configure-password-reset --empresa-id=100 --enable --days=90
```

### Configurar usuário específico para reset a cada 30 dias:
```bash
docker exec app php artisan users:configure-password-reset --user-id=123 --enable --days=30
```

### Desabilitar reset forçado para todos os usuários:
```bash
docker exec app php artisan users:configure-password-reset --disable
```

## Segurança

- O middleware só permite acesso às páginas de alteração de senha e logout
- Requisições AJAX recebem resposta JSON apropriada
- A data de alteração é atualizada automaticamente
- Sistema compatível com recuperação de senha existente 