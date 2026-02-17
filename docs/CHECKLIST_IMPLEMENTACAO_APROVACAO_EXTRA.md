# ✅ Checklist de Implementação - Sistema de Aprovação Extra

## 📋 Fase 1: Banco de Dados (CRÍTICO)

-   [ ] **1.1** Executar migrations

    ```bash
    cd /Users/juniorfreitas/Desktop/WEB/2025/mybp
    php artisan migrate
    ```

    -   [ ] Verificar se tabela `aprovacao_extra_configs` foi criada
    -   [ ] Verificar se campos foram adicionados em `demissao_previstas`
    -   [ ] Verificar se campos foram adicionados em `ferias_previstas`

-   [ ] **1.2** Verificar estrutura no banco
    ```sql
    DESCRIBE aprovacao_extra_configs;
    DESCRIBE demissao_previstas; -- Verificar novos campos
    DESCRIBE ferias_previstas;   -- Verificar novos campos
    ```

## 📝 Fase 2: Permissões e Habilidades

-   [ ] **2.1** Criar habilidades no banco

    ```sql
    INSERT INTO habilidades (nome, descricao) VALUES
    ('administracao_aprovacao_extra_config', 'Gerenciar configurações de aprovação extra'),
    ('planejamento_movimentacao_demissao_aprovar_extra', 'Aprovar demissões (aprovação extra)'),
    ('planejamento_movimentacao_ferias_aprovar_extra', 'Aprovar férias (aprovação extra)');
    ```

-   [ ] **2.2** Atribuir habilidades aos papéis apropriados
    -   [ ] Administrador → `administracao_aprovacao_extra_config`
    -   [ ] SESMT/Supervisor → `planejamento_movimentacao_demissao_aprovar_extra`
    -   [ ] Gestor → `planejamento_movimentacao_ferias_aprovar_extra`

## 🛣️ Fase 3: Rotas

-   [ ] **3.1** Adicionar rotas no `routes/web.php`

    ```php
    // Copiar de: /docs/ROTAS_APROVACAO_EXTRA.php
    ```

    -   [ ] Rotas de configuração (administração)
    -   [ ] Rotas de aprovação (demissão)
    -   [ ] Rotas de aprovação (férias)

-   [ ] **3.2** Testar rotas
    ```bash
    php artisan route:list | grep aprovacao-extra
    ```

## 🎨 Fase 4: Interface (Views)

-   [ ] **4.1** Criar view principal de configuração

    -   **Arquivo:** `resources/views/g/administracao/aprovacao-extra-config/index.blade.php`
    -   **Conteúdo básico:**
        ```blade
        @extends('layouts.app')
        @section('content')
            <div id="app">
                <aprovacao-extra-config></aprovacao-extra-config>
            </div>
        @endsection
        ```

-   [ ] **4.2** Criar componente Vue

    -   **Arquivo:** `resources/js/components/administracao/AprovacaoExtraConfig.vue`
    -   **Base:** Usar `/docs/EXEMPLO_COMPONENTE_VUE_APROVACAO_EXTRA.vue`

-   [ ] **4.3** Registrar componente

    -   **Arquivo:** `resources/js/app.js`
        ```javascript
        Vue.component('aprovacao-extra-config', require('./components/administracao/AprovacaoExtraConfig.vue').default)
        ```

-   [ ] **4.4** Compilar assets
    ```bash
    npm run dev
    # ou
    npm run watch
    ```

## 🔗 Fase 5: Menu

-   [ ] **5.1** Adicionar link no menu de Administração
    -   **Arquivo:** `resources/views/layouts/menu.blade.php`
    ```blade
    @can('administracao_aprovacao_extra_config')
        <li>
            <a href="{{ route('g.aprovacao-extra-config.index') }}"
               parent="administracao"
               key="aprovacao_extra_config">
                Aprovações Extras
            </a>
        </li>
    @endcan
    ```

## 🔧 Fase 6: Controllers Existentes

### 6.1 DemissaoPrevistaController

-   [ ] **6.1.1** Adicionar método `aprovarExtra()`

    -   Copiar de: `/docs/EXEMPLO_USO_APROVACAO_EXTRA.php` (Exemplo 3)

-   [ ] **6.1.2** Adicionar método `pendentesAprovacaoExtra()`

    -   Copiar de: `/docs/EXEMPLO_USO_APROVACAO_EXTRA.php` (Exemplo 5)

-   [ ] **6.1.3** Atualizar método `index()` ou `listar()`

    -   Incluir relacionamento `AprovacaoExtra`
    -   Retornar informação se tem aprovação extra configurada

-   [ ] **6.1.4** Atualizar método `store()`
    -   Informar ao usuário se terá aprovação extra

### 6.2 FeriasPrevistaController

-   [ ] **6.2.1** Adicionar método `aprovarExtra()`
-   [ ] **6.2.2** Adicionar método `pendentesAprovacaoExtra()`
-   [ ] **6.2.3** Atualizar método `index()` ou `listar()`
-   [ ] **6.2.4** Atualizar método `store()`

## 🎭 Fase 7: Componentes Vue (Demissão/Férias)

### 7.1 Componente de Listagem

-   [ ] **7.1.1** Adicionar coluna para aprovação extra
-   [ ] **7.1.2** Mostrar status da aprovação extra
-   [ ] **7.1.3** Adicionar filtro por status de aprovação extra

### 7.2 Componente de Detalhes

-   [ ] **7.2.1** Mostrar informações da aprovação extra

    -   Nome do aprovador
    -   Status (aprovado/reprovado)
    -   Data/hora
    -   Observações

-   [ ] **7.2.2** Adicionar botões de aprovação extra
    -   Mostrar apenas se:
        -   Usuário tem permissão
        -   Gestor e RH já aprovaram
        -   Ainda não foi aprovado pela aprovação extra

### 7.3 Componente de Criação

-   [ ] **7.3.1** Ao carregar, buscar se tem aprovação extra

    ```javascript
    async buscarConfigAprovacaoExtra() {
        const response = await axios.post(
            '/g/administracao/aprovacao-extra-config/buscar-por-tipo',
            { tipo_processo: 'demissao' }
        );
        this.temAprovacaoExtra = response.data.tem_aprovacao_extra;
        this.nomeAprovacaoExtra = response.data.config?.nome_aprovacao;
    }
    ```

-   [ ] **7.3.2** Mostrar aviso sobre aprovação extra
    ```html
    <div v-if="temAprovacaoExtra" class="alert alert-info">ℹ️ Esta solicitação será analisada também por: {{ nomeAprovacaoExtra }}</div>
    ```

## 🔔 Fase 8: Notificações

-   [ ] **8.1** Criar notificação para aprovador extra

    -   Quando: RH aprovar e tiver aprovação extra configurada
    -   Para: Usuário com permissão de aprovação extra

-   [ ] **8.2** Criar notificação de conclusão

    -   Quando: Aprovador extra aprovar/reprovar
    -   Para: RH e Solicitante

-   [ ] **8.3** Configurar envio de e-mail (opcional)

## 📊 Fase 9: Relatórios

-   [ ] **9.1** Atualizar relatório de demissões

    -   Incluir coluna de aprovação extra
    -   Filtro por status de aprovação extra

-   [ ] **9.2** Atualizar relatório de férias

    -   Incluir coluna de aprovação extra
    -   Filtro por status de aprovação extra

-   [ ] **9.3** Criar relatório de tempo de aprovação
    -   Tempo médio por tipo de aprovação
    -   Gargalos no processo

## 🧪 Fase 10: Testes

### 10.1 Testes Funcionais

-   [ ] **10.1.1** Configuração

    -   [ ] Criar configuração de aprovação extra
    -   [ ] Editar configuração
    -   [ ] Ativar/desativar configuração
    -   [ ] Deletar configuração
    -   [ ] Verificar que só uma config ativa por tipo

-   [ ] **10.1.2** Demissão COM aprovação extra

    -   [ ] Criar solicitação
    -   [ ] Gestor aprovar
    -   [ ] RH aprovar
    -   [ ] Verificar que aparece para aprovador extra
    -   [ ] Aprovador extra aprovar
    -   [ ] Verificar conclusão

-   [ ] **10.1.3** Demissão SEM aprovação extra

    -   [ ] Desativar configuração
    -   [ ] Criar solicitação
    -   [ ] Gestor aprovar
    -   [ ] RH aprovar
    -   [ ] Verificar conclusão (sem aprovação extra)

-   [ ] **10.1.4** Férias COM aprovação extra
    -   [ ] Repetir testes similares

### 10.2 Testes de Permissão

-   [ ] **10.2.1** Usuário SEM permissão

    -   [ ] Não consegue acessar configurações
    -   [ ] Não consegue aprovar como extra

-   [ ] **10.2.2** Usuário COM permissão
    -   [ ] Consegue acessar configurações
    -   [ ] Consegue aprovar como extra

### 10.3 Testes de Integração

-   [ ] **10.3.1** Testar com dados reais
-   [ ] **10.3.2** Testar com múltiplas empresas
-   [ ] **10.3.3** Verificar retrocompatibilidade (registros antigos)

## 📝 Fase 11: Documentação Interna

-   [ ] **11.1** Atualizar manual do usuário
-   [ ] **11.2** Criar vídeo tutorial (opcional)
-   [ ] **11.3** Documentar para time de suporte

## 🚀 Fase 12: Deploy

-   [ ] **12.1** Backup do banco de dados

    ```bash
    mysqldump -u usuario -p nome_banco > backup_antes_aprovacao_extra.sql
    ```

-   [ ] **12.2** Executar migrations em produção

    ```bash
    php artisan migrate --env=production
    ```

-   [ ] **12.3** Compilar assets para produção

    ```bash
    npm run production
    ```

-   [ ] **12.4** Limpar cache

    ```bash
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
    php artisan cache:clear
    ```

-   [ ] **12.5** Testar em produção

## 📋 Fase 13: Pós-Deploy

-   [ ] **13.1** Monitorar logs por 24h
-   [ ] **13.2** Coletar feedback dos usuários
-   [ ] **13.3** Ajustar conforme necessário

## 🎯 Configuração Inicial por Empresa

Para cada empresa que vai usar:

-   [ ] **Empresa 1: [Nome]**

    -   [ ] Definir quais processos terão aprovação extra
    -   [ ] Definir nomes das aprovações
    -   [ ] Criar configurações
    -   [ ] Atribuir permissões aos usuários
    -   [ ] Treinar usuários

-   [ ] **Empresa 2: [Nome]**
    -   [ ] (repetir itens acima)

## 📊 Métricas de Sucesso

Após implementação, verificar:

-   [ ] Tempo de aprovação reduziu/aumentou?
-   [ ] Usuários estão usando o sistema?
-   [ ] Há erros ou bugs reportados?
-   [ ] Feedback dos usuários é positivo?

## 🆘 Troubleshooting

### Problema: Migration falha

**Solução:**

```bash
php artisan migrate:rollback
# Corrigir migration
php artisan migrate
```

### Problema: Relacionamento não funciona

**Verificar:**

1. Foreign keys estão corretas?
2. Campos estão no fillable?
3. Casts estão corretos?

### Problema: Componente Vue não aparece

**Verificar:**

1. Componente registrado no app.js?
2. Assets compilados? (`npm run dev`)
3. Cache limpo? (`php artisan view:clear`)

## 📞 Contatos de Suporte

-   Desenvolvedor: [Nome]
-   Email: [email]
-   Slack: [canal]

---

## ✅ Status Geral

-   ✅ Fase 1: Banco de Dados - **COMPLETO**
-   ⏳ Fase 2: Permissões - **PENDENTE**
-   ⏳ Fase 3: Rotas - **PENDENTE**
-   ⏳ Fase 4: Interface - **PENDENTE**
-   ⏳ Fase 5: Menu - **PENDENTE**
-   ⏳ Fase 6: Controllers - **PENDENTE**
-   ⏳ Fase 7: Componentes Vue - **PENDENTE**
-   ⏳ Fase 8: Notificações - **PENDENTE**
-   ⏳ Fase 9: Relatórios - **PENDENTE**
-   ⏳ Fase 10: Testes - **PENDENTE**
-   ⏳ Fase 11: Documentação - **PENDENTE**
-   ⏳ Fase 12: Deploy - **PENDENTE**
-   ⏳ Fase 13: Pós-Deploy - **PENDENTE**

**Última atualização:** 30/01/2025
**Branch:** feature/aprovacao-extra
