# Guia Rápido de Teste - Aprovação Extra Frontend

## 🚀 Como Testar o Frontend

### 1. Configurar Aprovação Extra (SQL)

Execute o SQL de configuração para habilitar aprovação extra:

```sql
-- Para Requisição de Vaga
INSERT INTO aprovacao_extra_configs (tipo_processo, nome_aprovacao, usuarios_autorizados, empresa_id, created_at, updated_at)
VALUES ('requisicao_vaga', 'Gerência', '[1, 2, 3]', 1, NOW(), NOW());

-- Para Valor Extra
INSERT INTO aprovacao_extra_configs (tipo_processo, nome_aprovacao, usuarios_autorizados, empresa_id, created_at, updated_at)
VALUES ('valor_extra', 'Diretoria', '[1, 4, 5]', 1, NOW(), NOW());
```

**⚠️ Importante**: Substitua `[1, 2, 3]` pelos IDs reais dos usuários autorizados.

---

### 2. Testar Valor Extra Prevista

#### Passo 1: Criar Solicitação

1. Acesse: **Planejamento → Movimentação → Valor Extra**
2. Clique em **"Solicitar"**
3. Preencha os campos:
    - Colaborador
    - Gestor
    - Tipo
    - Período em dias
4. Clique em **"Cadastrar"**

#### Passo 2: Aprovação do Gestor

1. Na listagem, localize a solicitação (status "EM ABERTO")
2. Clique em **"Aprovação Gestor"** (ícone ✓)
3. Preencha:
    - Observação
    - Status: **Aprovar**
4. Clique em **"Salvar"**

#### Passo 3: Aprovação Extra (se configurada)

1. Status muda para "APROVADO GESTOR"
2. **Verif ique se aparece botão de Aprovação Extra** ✅
3. Clique no botão (ícone 📋)
4. Modal abre com:
    - Header azul: "Gerência" (ou nome configurado)
    - Campos de observação e status
5. Preencha e aprove
6. Clique em **"Salvar"**

#### Passo 4: Aprovação RH

1. Status muda para "APROVADO [NOME_APROVACAO_EXTRA]"
2. Clique em **"Aprovação RH"**
3. Preencha e finalize

---

### 3. Testar Requisição de Vaga

#### Passo 1: Criar Requisição

1. Acesse: **Planejamento → Requisição de Vagas**
2. Clique em **"Solicitar"**
3. Preencha os campos:
    - Cargo
    - Área
    - Centro de Custo
    - Tipo de Contratação
    - Prioridade
    - Quantidade
4. Clique em **"Cadastrar"**

#### Passo 2: Aprovação do Gestor

1. Na listagem, localize a requisição
2. Clique no botão de aprovação (ícone ✓)
3. Preencha:
    - Observação
    - Status: **Aprovar**
4. Clique em **"Aprovar"**

#### Passo 3: Aprovação Extra (se configurada)

1. **Verifique se aparece botão com ícone 📋** ✅
2. Clique no botão de Aprovação Extra
3. Modal abre com header azul
4. Preencha observação e status
5. Clique em **"Salvar"**

---

## 🔍 Cenários de Teste

### Cenário 1: SEM Aprovação Extra Configurada

```
✅ Não deve exibir botão de Aprovação Extra
✅ Fluxo: Solicitação → Gestor → RH
✅ Após aprovação do gestor, deve ir direto para RH
```

### Cenário 2: COM Aprovação Extra Configurada

```
✅ Deve exibir botão de Aprovação Extra após aprovação do gestor
✅ Fluxo: Solicitação → Gestor → Aprovação Extra → RH
✅ Após aprovação extra, libera para RH
```

### Cenário 3: Reprovação na Aprovação Extra

```
✅ Se reprovar na Aprovação Extra, processo para
✅ Não deve liberar para RH
✅ Status deve indicar "REPROVADO"
```

### Cenário 4: Sem Permissão para Aprovação Extra

```
✅ Usuário SEM permissão: botão não aparece
✅ Usuário COM permissão: botão aparece normalmente
```

---

## 📋 Checklist de Validação Visual

### Interface da Aprovação Extra

-   [ ] Card com header azul aparece
-   [ ] Título mostra nome configurado (ex: "Gerência", "Diretoria")
-   [ ] Ícone `fa-clipboard-check` está visível
-   [ ] Campo de observação (textarea) funciona
-   [ ] Select de status tem opções "Aprovar" e "Reprovar"
-   [ ] Botão "Salvar" aparece apenas em modo aprovação
-   [ ] Alert de "Ação Necessária" aparece quando pendente

### Dropdown de Ações

-   [ ] Botão de Aprovação Extra aparece apenas quando:
    -   `tem_aprovacao_extra = true`
    -   `status_aprovacao = 'aprovado'`
    -   `status_aprovacao_extra = null`
    -   `pode_aprovar_extra = true`

### Fluxo Completo

-   [ ] Após aprovação gestor, botão de Aprovação Extra aparece
-   [ ] Após aprovação extra, botão de Aprovação RH aparece
-   [ ] Status na listagem atualiza corretamente
-   [ ] Mensagens de sucesso aparecem após cada ação

---

## 🐛 Troubleshooting

### Problema: Botão de Aprovação Extra não aparece

**Possíveis causas:**

1. ✅ Aprovação Extra não configurada no banco
2. ✅ `tem_aprovacao_extra` retornando `false`
3. ✅ Usuário não tem permissão (`pode_aprovar_extra = false`)
4. ✅ Solicitação ainda não foi aprovada pelo gestor

**Solução:**

```bash
# 1. Verificar configuração
docker compose exec mybpdp php artisan tinker
>>> \App\Models\AprovacaoExtraConfig::where('tipo_processo', 'valor_extra')->first();

# 2. Verificar assets compilados
npm run dev

# 3. Limpar cache
docker compose exec mybpdp php artisan cache:clear
docker compose exec mybpdp php artisan config:clear
```

### Problema: Erro ao salvar aprovação

**Possíveis causas:**

1. ✅ Endpoint não encontrado (404)
2. ✅ Falta de permissão (403)
3. ✅ Validação falhando (422)

**Solução:**

```bash
# Verificar rotas
docker compose exec mybpdp php artisan route:list | grep aprovarextra

# Verificar logs
docker compose exec mybpdp tail -f storage/logs/laravel.log
```

### Problema: Interface não atualiza

**Possível causa:**
✅ Assets não foram compilados

**Solução:**

```bash
# Compilar novamente
npm run dev

# Ou em produção
npm run prod

# Limpar cache do navegador (Ctrl + Shift + R)
```

---

## 📊 Console do Navegador

### Logs Esperados

Ao carregar a lista:

```javascript
// Console.log automático (se habilitado)
{
    pode_aprovar_extra: true,
    tem_aprovacao_extra: true,
    nome_aprovacao_extra: "Gerência",
    aprovar_por_gestor: true,
    aprovar_por_rh: true
}
```

### Inspecionar Dados no Vue Devtools

1. Instale **Vue Devtools** no navegador
2. Abra a página
3. Vá em **Vue → Components**
4. Verifique propriedades:
    - `temAprovacaoExtra`
    - `aprovaExtra`
    - `nomeAprovacaoExtra`
    - `form.status_aprovacao_extra`

---

## ✅ Teste Completo Bem-Sucedido

### Valor Extra

```
1. ✅ Solicitação criada
2. ✅ Gestor aprovou
3. ✅ Botão de Aprovação Extra apareceu
4. ✅ Aprovação Extra realizada
5. ✅ Botão de Aprovação RH apareceu
6. ✅ RH aprovou
7. ✅ Status final: "APROVADO RH"
```

### Requisição de Vaga

```
1. ✅ Requisição criada
2. ✅ Gestor aprovou
3. ✅ Botão de Aprovação Extra apareceu
4. ✅ Aprovação Extra realizada
5. ✅ Processo concluído com sucesso
```

---

## 📞 Suporte

Se encontrar problemas, verifique:

1. **Logs do Laravel**: `storage/logs/laravel.log`
2. **Console do navegador**: F12 → Console
3. **Network Tab**: Verifique requisições HTTP
4. **Vue Devtools**: Inspecione componentes Vue

---

**Data**: 2026-02-07  
**Status**: ✅ Frontend implementado e pronto para testes
