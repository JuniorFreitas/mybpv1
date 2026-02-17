# 🧪 Teste Rápido - Sistema de Aprovações Extras

## ✅ Status do Sistema

**Data do teste**: 30/01/2026

### Verificações Automáticas Concluídas:

✅ **Rotas registradas** (10 rotas):

-   GET `/g/administracao/aprovacao-extra-config` - Tela principal
-   GET `/g/administracao/aprovacao-extra-config/listar` - Lista configs
-   GET `/g/administracao/aprovacao-extra-config/tipos-processo` - Lista tipos
-   GET `/g/administracao/aprovacao-extra-config/listar-usuarios` - Lista usuários
-   GET `/g/administracao/aprovacao-extra-config/buscar-por-tipo` - Busca por tipo
-   POST `/g/administracao/aprovacao-extra-config` - Criar config
-   PUT `/g/administracao/aprovacao-extra-config/{id}` - Atualizar config
-   DELETE `/g/administracao/aprovacao-extra-config/{id}` - Deletar config
-   POST `/g/administracao/aprovacao-extra-config/{id}/toggle-ativo` - Ativar/desativar
-   POST `/g/administracao/aprovacao-extra-config/pode-aprovar` - Verificar permissão

✅ **Assets compilados**:

-   Arquivo: `public/js/g/administracao/aprovacao-extra-config/app.js`
-   Tamanho: 2.0 MB
-   Data: 30/01/2026 23:46

✅ **Migrations executadas** (batch 171):

-   `create_aprovacao_extra_configs_table`
-   `add_aprovacao_extra_to_demissao_previstas_table`
-   `add_aprovacao_extra_to_ferias_previstas_table`
-   `add_usuarios_autorizados_to_aprovacao_extra_configs_table`

---

## 🎯 Roteiro de Teste Manual

### 1️⃣ Acesso ao Sistema

**Objetivo**: Verificar se a tela carrega corretamente

**Passos**:

1. Acesse: `http://localhost:8000/g/administracao/aprovacao-extra-config`
2. Faça login (se necessário)

**Resultado Esperado**:

-   ✅ Tela carrega sem erros
-   ✅ Mostra título "Configuração de Aprovações Extras"
-   ✅ Botão "Nova Configuração" visível
-   ✅ Tabela vazia ou com dados (se já houver configs)
-   ✅ Alerta informativo sobre "RH sempre última aprovação"

**Possíveis Problemas**:

-   ❌ **403 Forbidden**: Usuário sem permissão `administracao_aprovacao_extra_config`
-   ❌ **404 Not Found**: Rotas não registradas (rode `php artisan route:clear`)
-   ❌ **500 Error**: Verificar logs do Laravel
-   ❌ **JavaScript não carrega**: Limpar cache do navegador

---

### 2️⃣ Criar Primeira Configuração

**Objetivo**: Testar criação de nova configuração

**Passos**:

1. Clique em **"Nova Configuração"**
2. Preencha:
    - **Tipo de Processo**: Demissão Prevista
    - **Nome da Aprovação**: SESMT
    - **Usuários Autorizados**: Selecione 1 ou 2 usuários
    - **Status**: Marque como "Ativo"
3. Clique em **"Salvar"**

**Resultado Esperado**:

-   ✅ Modal abre corretamente
-   ✅ Campo "Tipo de Processo" mostra opções (Demissão, Férias, Mudança Cargo)
-   ✅ Multiselect de usuários carrega lista
-   ✅ Ao salvar, mostra mensagem de sucesso (SweetAlert)
-   ✅ Modal fecha automaticamente
-   ✅ Tabela atualiza com nova configuração

**Validações**:

-   Campo "Tipo de Processo" é obrigatório
-   Campo "Nome da Aprovação" é obrigatório
-   Máximo 255 caracteres no nome

---

### 3️⃣ Editar Configuração

**Objetivo**: Testar edição de configuração existente

**Passos**:

1. Na linha da configuração criada, clique no botão ✏️ (editar)
2. Altere o **Nome da Aprovação** para: "SESMT - Segurança"
3. Adicione mais um usuário em **Usuários Autorizados**
4. Clique em **"Salvar"**

**Resultado Esperado**:

-   ✅ Modal abre com dados preenchidos
-   ✅ Campo "Tipo de Processo" está desabilitado (não pode alterar)
-   ✅ Usuários previamente selecionados aparecem no multiselect
-   ✅ Alterações são salvas
-   ✅ Tabela reflete mudanças

---

### 4️⃣ Toggle Ativo/Inativo

**Objetivo**: Testar ativação/desativação de configuração

**Passos**:

1. Clique no botão de toggle (ícone ✓ ou ✗)
2. Observe a mudança de status

**Resultado Esperado**:

-   ✅ Badge muda de "Ativo" (verde) para "Inativo" (cinza)
-   ✅ Ícone do botão muda de ✓ para ✗
-   ✅ Mensagem de sucesso aparece
-   ✅ Tabela atualiza automaticamente

**Regra de Negócio**:

-   Apenas **uma configuração ativa** por tipo de processo
-   Se tentar ativar segunda config do mesmo tipo, deve desativar a primeira

---

### 5️⃣ Criar Segunda Configuração (Mesmo Tipo)

**Objetivo**: Testar regra de apenas 1 config ativa por tipo

**Passos**:

1. Crie nova configuração
2. **Tipo de Processo**: Demissão Prevista (mesmo da primeira)
3. **Nome da Aprovação**: "Aprovação Supervisor"
4. Marque como **Ativo**
5. Salve

**Resultado Esperado**:

-   ✅ Configuração é criada
-   ✅ A **primeira configuração** (SESMT) é automaticamente **desativada**
-   ✅ A **nova configuração** fica **ativa**
-   ✅ Apenas uma ativa por tipo de processo

---

### 6️⃣ Testar Diferentes Tipos de Processo

**Objetivo**: Criar configs para diferentes processos

**Passos**:

1. Crie configuração para **Férias Previstas**
    - Nome: "Aprovação Supervisor Férias"
2. Crie configuração para **Mudança de Cargo**
    - Nome: "Aprovação Gerente RH"

**Resultado Esperado**:

-   ✅ Cada tipo pode ter 1 configuração ativa
-   ✅ Total: 3 configs ativas (1 por tipo)
-   ✅ Tabela mostra badges diferentes por tipo

---

### 7️⃣ Multiselect de Usuários

**Objetivo**: Testar seleção múltipla de usuários

**Passos**:

1. Abra modal de edição
2. No campo "Usuários Autorizados":
    - Clique para abrir lista
    - Selecione 5+ usuários
    - Desmarque 2 usuários
    - Deixe 3 selecionados
3. Salve

**Resultado Esperado**:

-   ✅ Lista carrega todos os usuários ativos
-   ✅ Permite selecionar/desselecionar
-   ✅ Mostra contador: "3 usuário(s) selecionado(s)"
-   ✅ Na listagem, mostra primeiros 3 + contador "+X mais"
-   ✅ Usuários são salvos corretamente

---

### 8️⃣ Deletar Configuração

**Objetivo**: Testar exclusão de configuração

**Passos**:

1. Clique no botão 🗑️ (deletar) em alguma config
2. Confirme a exclusão no SweetAlert

**Resultado Esperado**:

-   ✅ Modal de confirmação aparece
-   ✅ Ao confirmar, config é deletada
-   ✅ Linha desaparece da tabela
-   ✅ Mensagem de sucesso

**Cancelar**:

-   Se clicar em "Cancelar", nada acontece

---

### 9️⃣ Validações de Formulário

**Objetivo**: Testar validações de campos

**Testes**:

**A) Campos vazios**:

1. Abra modal de criação
2. Deixe tudo em branco
3. Tente salvar

**Resultado**: ❌ Alerta "Preencha todos os campos obrigatórios"

**B) Apenas tipo selecionado**:

1. Selecione apenas "Tipo de Processo"
2. Deixe "Nome" vazio
3. Tente salvar

**Resultado**: ❌ Alerta de validação

**C) Caracteres especiais no nome**:

1. Tente colocar 300 caracteres no nome
2. Salve

**Resultado**: ✅ Aceita até 255 caracteres

---

### 🔟 Testar Alertas Informativos

**Objetivo**: Verificar mensagens de ajuda

**Verificar**:

-   ✅ Alerta azul no topo: "RH sempre é a última aprovação"
-   ✅ Alerta amarelo no modal: "O fluxo será Gestor → [Nome] → RH"
-   ✅ Dica abaixo de usuários: "Usuários selecionados + privilegio_rh podem aprovar"

---

## 📊 Checklist de Validação

Marque conforme testa:

### Funcionalidades Básicas

-   [ ] Tela carrega sem erros
-   [ ] Botão "Nova Configuração" funciona
-   [ ] Modal abre e fecha corretamente
-   [ ] Formulário salva dados
-   [ ] Listagem atualiza após salvar

### CRUD Completo

-   [ ] **Create**: Criar nova configuração
-   [ ] **Read**: Listar configurações
-   [ ] **Update**: Editar configuração
-   [ ] **Delete**: Deletar configuração

### Regras de Negócio

-   [ ] Apenas 1 config ativa por tipo de processo
-   [ ] RH sempre é última aprovação (info visual)
-   [ ] Tipo de processo não pode ser alterado após criação
-   [ ] Múltiplos usuários podem ser selecionados

### Interface

-   [ ] Multiselect funciona
-   [ ] SweetAlert2 mostra confirmações
-   [ ] Badges de status corretas
-   [ ] Botões de ação funcionam
-   [ ] Tabela responsiva

### Validações

-   [ ] Campos obrigatórios validados
-   [ ] Limite de caracteres respeitado
-   [ ] Mensagens de erro claras

---

## 🐛 Problemas Comuns e Soluções

### Problema: "403 Forbidden"

**Causa**: Usuário sem permissão
**Solução**:

```bash
# Criar permissão no banco
docker compose exec mybpdp php artisan tinker
```

```php
\App\Models\Permission::create([
    'name' => 'administracao_aprovacao_extra_config',
    'display_name' => 'Configuração de Aprovações Extras'
]);
```

### Problema: JavaScript não carrega

**Causa**: Cache do navegador
**Solução**:

1. Abra DevTools (F12)
2. Clique com botão direito no reload
3. Escolha "Empty Cache and Hard Reload"

### Problema: "Route not found"

**Causa**: Rotas não cached
**Solução**:

```bash
docker compose exec mybpdp php artisan route:clear
docker compose exec mybpdp php artisan config:clear
```

### Problema: Modal não abre

**Causa**: Erro de JavaScript
**Solução**:

1. Abra Console do navegador (F12)
2. Verifique erros
3. Confirme se app.js carregou: `http://localhost:8000/js/g/administracao/aprovacao-extra-config/app.js`

---

## 🎓 Teste de Integração (Avançado)

### Testar API Diretamente

**Listar Tipos de Processo**:

```bash
curl http://localhost:8000/g/administracao/aprovacao-extra-config/tipos-processo
```

**Listar Usuários**:

```bash
curl http://localhost:8000/g/administracao/aprovacao-extra-config/listar-usuarios
```

**Listar Configurações**:

```bash
curl http://localhost:8000/g/administracao/aprovacao-extra-config/listar
```

---

## ✅ Resultado do Teste

Após completar todos os testes, preencha:

**Data**: **_/_**/**\_\_**  
**Testado por**: ******\_\_\_******

**Status Geral**:

-   [ ] ✅ Todos os testes passaram
-   [ ] ⚠️ Testes passaram com ressalvas
-   [ ] ❌ Falhas encontradas

**Observações**:

```
_______________________________________________
_______________________________________________
_______________________________________________
```

---

**Sistema pronto para uso em produção?**

-   [ ] Sim
-   [ ] Não - Pendências: ******\_\_\_******
