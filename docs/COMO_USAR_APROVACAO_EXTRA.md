# Como Usar o Sistema de Aprovações Extras

## 📋 Visão Geral

Sistema que permite configurar aprovações extras personalizadas para processos de RH (demissões, férias, mudanças de cargo, etc.).

**Fluxo de Aprovação**: Gestor → **Aprovação Extra** → RH (final)

> ⚠️ **Importante**: RH sempre é a última aprovação!

## 🚀 Acesso ao Sistema

1. Faça login no MyBP
2. Menu: **ADMINISTRAÇÃO** → **Aprovações Extras**
3. URL direta: `https://seu-dominio/g/administracao/aprovacao-extra-config`

## ⚙️ Configurando uma Aprovação Extra

### Passo 1: Criar Nova Configuração

1. Clique no botão **"Nova Configuração"**
2. Preencha os dados:
    - **Tipo de Processo**: Selecione o processo (Demissão, Férias, Mudança de Cargo)
    - **Nome da Aprovação**: Ex: "SESMT", "Supervisor", "Gerente de Área"
    - **Usuários Autorizados**: Selecione os usuários que podem aprovar
    - **Status**: Ativo/Inativo

### Passo 2: Selecionar Usuários Autorizados

-   Use o campo "Usuários Autorizados" para selecionar pessoas específicas
-   Múltiplos usuários podem ser selecionados
-   **Automático**: Usuários com `privilegio_rh` podem aprovar tudo

### Passo 3: Ativar Configuração

-   Apenas **uma configuração** pode estar ativa por tipo de processo
-   Use o toggle para ativar/desativar

## 📝 Exemplo Prático

### Configuração para Demissões com Aprovação do SESMT

```
Tipo de Processo: Demissão Prevista
Nome da Aprovação: SESMT
Usuários Autorizados: [João Silva, Maria Santos]
Status: Ativo
```

**Fluxo resultante**:

1. Gestor solicita demissão
2. ✅ Gestor aprova
3. 🔄 **SESMT aprova** (João ou Maria)
4. ✅ RH aprova (aprovação final)

## 🔐 Permissões

### Quem pode aprovar?

1. **Usuários selecionados** na configuração
2. **Qualquer usuário com `privilegio_rh`** (sempre pode aprovar tudo)

### Permissão de Acesso à Configuração

-   Necessário ter permissão: `administracao_aprovacao_extra_config`
-   Gerenciar no menu Administração > Papéis e Permissões

## 🛠️ Gerenciamento

### Editar Configuração

-   Clique no ícone ✏️ (editar)
-   **Nota**: O tipo de processo não pode ser alterado após criação

### Ativar/Desativar

-   Use o botão de toggle (✓ ou ✗)
-   Desativar remove a aprovação extra daquele processo

### Deletar

-   Clique no ícone 🗑️ (deletar)
-   **Atenção**: Ação irreversível!

## 📊 Listagem

A tela principal mostra:

-   **Tipo de Processo**: Badge azul com o nome
-   **Nome da Aprovação**: Como aparecerá no sistema
-   **Usuários Autorizados**: Primeiros 3 usuários + contador
-   **Status**: Ativo (verde) ou Inativo (cinza)
-   **Data de Criação**
-   **Ações**: Editar, Ativar/Desativar, Deletar

## ❓ Perguntas Frequentes

### Posso ter múltiplas aprovações extras no mesmo processo?

Não. Apenas uma configuração pode estar ativa por tipo de processo.

### O que acontece se eu desativar uma configuração?

O processo volta ao fluxo normal: Gestor → RH (sem aprovação extra).

### Usuários com privilegio_rh sempre podem aprovar?

Sim! Mesmo que não estejam na lista de "Usuários Autorizados".

### Como sei quem pode aprovar?

Na tela de configuração, os usuários autorizados aparecem em badges. Além deles, qualquer um com `privilegio_rh` também pode.

### Posso mudar o nome da aprovação depois de criada?

Sim! Basta editar a configuração e alterar o campo "Nome da Aprovação".

### O RH sempre é a última aprovação?

Sim! Esta é uma regra fixa do sistema e não pode ser alterada.

## 🔄 Fluxos Possíveis

### Sem Aprovação Extra Configurada

```
Gestor → RH ✅
```

### Com Aprovação Extra Ativa

```
Gestor → Aprovação Extra → RH ✅
```

## 📞 Suporte

Em caso de dúvidas ou problemas:

1. Verifique a documentação técnica em `/docs/`
2. Consulte o arquivo `README_APROVACAO_EXTRA.md`
3. Entre em contato com o suporte técnico

---

**Última atualização**: 30/01/2026
