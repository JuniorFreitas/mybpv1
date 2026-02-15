# Guia de Teste - Aprovação Extra (Requisição de Vaga e Valor Extra)

## 📋 Pré-requisitos

1. ✅ Migrations executadas
2. ✅ Cache limpo
3. ✅ Configuração criada no banco (usar SQL script)
4. ✅ Usuários com permissões apropriadas

---

## 🧪 Teste 1: Requisição de Vaga com Aprovação Extra

### 1.1 Configurar Aprovação Extra

```sql
-- Verificar se já existe
SELECT * FROM aprovacao_extra_configs
WHERE tipo_processo = 'requisicao_vaga' AND empresa_id = 1;

-- Se não existir, criar
INSERT INTO aprovacao_extra_configs
(empresa_id, tipo_processo, nome_aprovacao, usuarios_autorizados, ativo, created_at, updated_at)
VALUES
(1, 'requisicao_vaga', 'Gerência', '[2,3]', 1, NOW(), NOW());
```

### 1.2 Criar Solicitação

**Endpoint:** `POST /planejamento/requisicao-vaga`

```json
{
    "centro_custo_id": 1,
    "cargo_id": 1,
    "area_id": 1,
    "quantidade": 1,
    "tipo_contratacao": "CLT",
    "prioridade": "alta",
    "imediata": false,
    "previsao_inicio": "15/02/2026",
    "solicitante": "João Silva",
    "observacao": "Teste de requisição com aprovação extra"
}
```

**Resultado Esperado:** Status 201

### 1.3 Listar e Verificar Configuração

**Endpoint:** `POST /planejamento/requisicao-vaga/atualizar`

```json
{
    "pages": 1
}
```

**Resultado Esperado:**

```json
{
    "dados": {
        "itens": [...],
        "pode_aprovar_extra": true/false,
        "tem_aprovacao_extra": true,
        "nome_aprovacao_extra": "Gerência"
    }
}
```

### 1.4 Aprovar como Gestor

**Endpoint:** `PUT /planejamento/requisicao-vaga/{id}/aprovar`

**Login:** Usuário com `privilegio_aprovar_por_gestor`

```json
{
    "id": 1,
    "status_aprovacao": "aprovado",
    "obs_aprovacao": "Aprovado pelo gestor"
}
```

**Resultado Esperado:** Status 201

### 1.5 Verificar no Banco

```sql
SELECT
    id,
    status_aprovacao,
    user_aprovacao_id,
    data_aprovacao,
    status_aprovacao_extra,
    aprovacao_extra_id
FROM requisicao_vagas
WHERE id = 1;
```

**Resultado Esperado:**

-   `status_aprovacao`: "aprovado"
-   `user_aprovacao_id`: ID do gestor
-   `status_aprovacao_extra`: NULL (ainda não aprovado)

### 1.6 Aprovar como Aprovação Extra

**Endpoint:** `PUT /planejamento/requisicao-vaga/{id}/aprovarextra`

**Login:** Usuário autorizado (IDs em `usuarios_autorizados`) ou com `privilegio_gestao_rh`

```json
{
    "id": 1,
    "status_aprovacao_extra": "aprovado",
    "obs_aprovacao_extra": "Aprovado pela gerência"
}
```

**Resultado Esperado:** Status 201

### 1.7 Verificar Aprovação Extra

```sql
SELECT
    id,
    status_aprovacao,
    status_aprovacao_extra,
    aprovacao_extra_id,
    data_aprovacao_extra,
    obs_aprovacao_extra
FROM requisicao_vagas
WHERE id = 1;
```

**Resultado Esperado:**

-   `status_aprovacao_extra`: "aprovado"
-   `aprovacao_extra_id`: ID do usuário que aprovou
-   `data_aprovacao_extra`: Data/hora atual

### 1.8 Tentar Aprovar sem Permissão

**Login:** Usuário SEM permissão (não está em `usuarios_autorizados` e não tem `privilegio_gestao_rh`)

**Resultado Esperado:** Status 403 - "Você não tem permissão para aprovar esta solicitação"

---

## 🧪 Teste 2: Valor Extra com Aprovação Extra

### 2.1 Configurar Aprovação Extra

```sql
INSERT INTO aprovacao_extra_configs
(empresa_id, tipo_processo, nome_aprovacao, usuarios_autorizados, ativo, created_at, updated_at)
VALUES
(1, 'valor_extra', 'Diretoria', '[2,4]', 1, NOW(), NOW());
```

### 2.2 Criar Solicitação

**Endpoint:** `POST /planejamento/movimentacao/valor-extra-prevista`

```json
{
    "colaborador_id": 10,
    "centro_custo_id": 1,
    "centro_custo_filial_id": null,
    "tipo": "adicional_noturno",
    "periodo_dias": 30,
    "obs": "Teste de valor extra com aprovação extra"
}
```

### 2.3 Aprovar como Gestor

**Endpoint:** `PUT /planejamento/movimentacao/valor-extra-prevista/{id}/aprovar`

```json
{
    "id": 1,
    "status_aprovacao": "aprovado",
    "obs_aprovacao": "Aprovado pelo gestor"
}
```

### 2.4 Aprovar como Aprovação Extra

**Endpoint:** `PUT /planejamento/movimentacao/valor-extra-prevista/{id}/aprovarextra`

```json
{
    "id": 1,
    "status_aprovacao_extra": "aprovado",
    "obs_aprovacao_extra": "Aprovado pela diretoria"
}
```

### 2.5 Aprovar como RH

**Endpoint:** `PUT /planejamento/movimentacao/valor-extra-prevista/{id}/aprovarrh`

**Login:** Usuário com `privilegio_aprovar_por_rh`

```json
{
    "id": 1,
    "status_aprovacao_rh": "aprovado",
    "obs_rh": "Aprovado pelo RH"
}
```

### 2.6 Verificar Fluxo Completo

```sql
SELECT
    id,
    status_aprovacao,
    user_aprovacao_id,
    status_aprovacao_extra,
    aprovacao_extra_id,
    status_aprovacao_rh,
    rh_aprovacao_id
FROM valor_extra_previstas
WHERE id = 1;
```

**Resultado Esperado:** Todas as aprovações devem estar preenchidas

---

## 🧪 Teste 3: Reprovação

### 3.1 Reprovar na Aprovação Extra

```json
{
    "id": 2,
    "status_aprovacao_extra": "reprovado",
    "obs_aprovacao_extra": "Orçamento insuficiente"
}
```

**Resultado Esperado:**

-   Status 201
-   `status_aprovacao_extra`: "reprovado"
-   Fluxo deve parar (não prosseguir para RH)

---

## 🧪 Teste 4: Sem Configuração Ativa

### 4.1 Desativar Aprovação Extra

```sql
UPDATE aprovacao_extra_configs
SET ativo = 0
WHERE tipo_processo = 'requisicao_vaga' AND empresa_id = 1;
```

### 4.2 Tentar Aprovar

**Resultado Esperado:**

-   Status 400
-   Mensagem: "Não existe configuração de aprovação extra ativa"

### 4.3 Verificar Listagem

**Resultado Esperado:**

```json
{
    "dados": {
        "tem_aprovacao_extra": false,
        "pode_aprovar_extra": false
    }
}
```

---

## 🧪 Teste 5: Performance e Eager Loading

### 5.1 Verificar Query Log

```sql
-- Habilitar query log no MySQL
SET GLOBAL general_log = 'ON';
SET GLOBAL log_output = 'TABLE';
```

### 5.2 Chamar Listagem

```bash
curl -X POST http://localhost:8000/planejamento/requisicao-vaga/atualizar \
  -H "Content-Type: application/json" \
  -d '{"pages": 1}'
```

### 5.3 Verificar Queries

```sql
SELECT
    event_time,
    SUBSTRING(argument, 1, 200) as query
FROM mysql.general_log
WHERE command_type = 'Query'
    AND argument LIKE '%requisicao_vagas%'
ORDER BY event_time DESC
LIMIT 10;
```

**Resultado Esperado:** Apenas 1 query com JOIN ou eager loading, não N+1 queries

---

## ✅ Checklist de Validação

-   [ ] Configuração criada no banco
-   [ ] Usuários autorizados configurados corretamente
-   [ ] Gestor consegue aprovar normalmente
-   [ ] Usuário autorizado consegue aprovar como extra
-   [ ] Usuário não autorizado é bloqueado (403)
-   [ ] Reprovação funciona corretamente
-   [ ] Dados aparecem na listagem
-   [ ] Eager loading funcionando (sem N+1)
-   [ ] Frontend recebe flags corretas
-   [ ] Com config desativada, não exibe opção

---

## 🐛 Troubleshooting

### Problema: Erro 403 ao tentar aprovar

**Solução:** Verificar se o usuário está em `usuarios_autorizados` ou tem privilégios

```sql
-- Verificar usuário
SELECT id, nome, login FROM users WHERE id = X;

-- Verificar config
SELECT usuarios_autorizados FROM aprovacao_extra_configs
WHERE tipo_processo = 'requisicao_vaga';

-- Verificar habilidades
SELECT * FROM papel_habilidades
WHERE papel_id = (SELECT papel_id FROM users WHERE id = X);
```

### Problema: Config não aparece no frontend

**Solução:** Limpar cache

```bash
docker compose exec mybpdp php artisan config:clear
docker compose exec mybpdp php artisan cache:clear
```

### Problema: Eager loading não funciona

**Solução:** Verificar se relacionamento existe no model

```php
// No model
public function AprovacaoExtra()
{
    return $this->hasOne(User::class, 'id', 'aprovacao_extra_id');
}
```

---

## 📊 Queries Úteis para Debug

```sql
-- Requisições com todas as aprovações
SELECT
    rv.id,
    rv.created_at,
    u1.nome as solicitante,
    u2.nome as aprovador_gestor,
    rv.status_aprovacao,
    u3.nome as aprovador_extra,
    rv.status_aprovacao_extra
FROM requisicao_vagas rv
LEFT JOIN users u1 ON u1.id = rv.user_id
LEFT JOIN users u2 ON u2.id = rv.user_aprovacao_id
LEFT JOIN users u3 ON u3.id = rv.aprovacao_extra_id
ORDER BY rv.created_at DESC;

-- Valores extras com fluxo completo
SELECT
    vep.id,
    u1.nome as solicitante,
    u2.nome as colaborador,
    vep.status_aprovacao,
    vep.status_aprovacao_extra,
    vep.status_aprovacao_rh
FROM valor_extra_previstas vep
LEFT JOIN users u1 ON u1.id = vep.user_id
LEFT JOIN users u2 ON u2.id = vep.colaborador_id
ORDER BY vep.created_at DESC;
```
