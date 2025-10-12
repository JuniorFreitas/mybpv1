# Sistema de Primeiro Acesso e Senha Temporária

Este sistema implementa controle obrigatório de alteração de senha para usuários em duas situações específicas:

1. **Primeiro Acesso**: Quando o usuário nunca alterou sua senha (`password_changed_at` é null)
2. **Senha Temporária**: Quando o usuário tem uma senha temporária (`temp = true`)

## Como Funciona

### Fluxo de Autenticação

1. **Login**: Usuário faz login normalmente
2. **Verificação**: Sistema verifica se é primeiro acesso OU tem senha temporária
3. **Redirecionamento**: Se uma das condições for verdadeira, usuário é obrigado a alterar senha
4. **Bloqueio**: Usuário não consegue acessar outras páginas até alterar a senha

### Detecção Automática

O sistema detecta automaticamente:

- ✅ **Primeiro Acesso**: Campo `password_changed_at` está vazio (null)
- ✅ **Senha Temporária**: Campo `temp` está como `true`
- ✅ **Senha Expirada**: Baseado na configuração de dias para reset

### Middlewares Envolvidos

1. **Authenticate**: Permite login com senha temporária
2. **CheckPasswordReset**: Verifica e redireciona se precisa alterar senha

## Como Testar

### 1. Criar Usuário de Teste - Primeiro Acesso

```bash
# Criar usuário que nunca alterou a senha
docker exec app php artisan demo:create-temporary-user \
  --email="primeiro.acesso@teste.com" \
  --name="Primeiro Acesso" \
  --first-access
```

### 2. Criar Usuário de Teste - Senha Temporária

```bash
# Criar usuário com senha temporária
docker exec app php artisan demo:create-temporary-user \
  --email="senha.temporaria@teste.com" \
  --name="Senha Temporária" \
  --temp
```

### 3. Criar Usuário de Teste - Ambos os Casos

```bash
# Criar usuário com senha temporária E primeiro acesso
docker exec app php artisan demo:create-temporary-user \
  --email="ambos.casos@teste.com" \
  --name="Ambos os Casos" \
  --temp \
  --first-access
```

## Fluxo de Teste

### Passo 1: Criar Usuário
```bash
docker exec app php artisan demo:create-temporary-user --email="teste@exemplo.com" --name="Usuário Teste" --temp
```

### Passo 2: Anotar a Senha Temporária
O comando mostrará a senha gerada automaticamente. Anote-a.

### Passo 3: Tentar Fazer Login
1. Acesse a página de login
2. Use o email criado e a senha temporária
3. Sistema deve redirecionar automaticamente para alteração de senha

### Passo 4: Verificar Mensagens
- Mensagem específica aparece: "Senha temporária detectada" ou "Primeiro acesso detectado"
- Interface mostra que a alteração é obrigatória
- Usuário não consegue fechar o modal ou acessar outras páginas

### Passo 5: Alterar Senha
1. Digite nova senha seguindo os critérios de segurança
2. Confirme a senha
3. Sistema deve remover a flag `temp` e definir `password_changed_at`

## Critérios de Senha Segura

- ✅ Mínimo de 8 caracteres
- ✅ Pelo menos 1 letra minúscula (a-z)
- ✅ Pelo menos 1 letra maiúscula (A-Z)  
- ✅ Pelo menos 1 número (0-9)
- ✅ Pelo menos 1 caractere especial (@$!%*?&)

## Recursos Implementados

### Interface do Usuário
- **Indicador visual**: Badge "Obrigatório" no título do modal
- **Mensagens específicas**: Diferentes para primeiro acesso vs senha temporária
- **Validação em tempo real**: Força da senha mostrada dinamicamente
- **Não permite cancelar**: Modal não pode ser fechado se alteração for obrigatória

### Backend
- **Métodos no Model User**:
  - `needsPasswordReset()`: Verifica se precisa alterar
  - `isFirstAccess()`: Verifica se é primeiro acesso
  - `hasTemporaryPassword()`: Verifica se tem senha temporária
  - `getPasswordResetReason()`: Retorna motivo específico

### API
- **LoginController**: Detecta e redireciona automaticamente
- **CheckPasswordReset Middleware**: Bloqueia acesso até alteração
- **AlterarSenhaController**: Remove flag temporária após alteração

## Mensagens do Sistema

### Primeiro Acesso
```
"Primeiro acesso detectado. É obrigatório alterar sua senha."
```

### Senha Temporária
```
"Senha temporária detectada. É obrigatório alterar sua senha."
```

### Senha Expirada
```
"Sua senha expirou há X dias. É necessário alterá-la para continuar."
```

## Segurança

- ✅ **Não bypassa**: Impossível contornar a obrigatoriedade
- ✅ **Validação dupla**: Frontend e backend validam
- ✅ **Remoção automática**: Flag `temp` removida após alteração
- ✅ **Data atualizada**: `password_changed_at` sempre atualizado
- ✅ **Critérios rígidos**: Senha deve atender todos os requisitos

## Casos de Uso Reais

1. **Novo funcionário**: RH cria conta com senha temporária
2. **Reset por admin**: Admin redefine senha de usuário
3. **Primeiro login**: Usuário nunca definiu senha própria
4. **Conta reativada**: Usuário com conta desativada que volta

Este sistema garante que todos os usuários tenham senhas seguras e personalizadas, aumentando significativamente a segurança da aplicação. 