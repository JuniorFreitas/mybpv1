# 🎯 Sistema de Aprovação Extra Dinâmica - Resumo da Implementação

## ✅ O que foi criado

### 1. **Estrutura do Banco de Dados**

#### Nova Tabela: `aprovacao_extra_configs`

-   ✅ Migration: `2025_01_30_000001_create_aprovacao_extra_configs_table.php`
-   Permite configurar aprovações extras personalizadas por empresa e tipo de processo
-   Campos: empresa_id, tipo_processo, nome_aprovacao, usuarios_autorizados, ativo
-   ✅ Migration adicional: `2025_01_30_000004_add_usuarios_autorizados_to_aprovacao_extra_configs_table.php`

#### Campos Adicionados em `demissao_previstas`

-   ✅ Migration: `2025_01_30_000002_add_aprovacao_extra_to_demissao_previstas_table.php`
-   Campos: aprovacao_extra_id, status_aprovacao_extra, obs_aprovacao_extra, data_aprovacao_extra

#### Campos Adicionados em `ferias_previstas`

-   ✅ Migration: `2025_01_30_000003_add_aprovacao_extra_to_ferias_previstas_table.php`
-   Campos: aprovacao_extra_id, status_aprovacao_extra, obs_aprovacao_extra, data_aprovacao_extra

### 2. **Models**

#### ✅ AprovacaoExtraConfig

-   Arquivo: `/app/Models/AprovacaoExtraConfig.php`
-   Gerencia configurações de aprovação extra
-   Constantes para tipos de processo (demissao, ferias, mudanca_cargo, etc.)
-   Método `getConfigAtiva()` para buscar configuração ativa
-   Método `podeAprovar($userId)` para verificar permissão
-   Suporte a usuários específicos + privilegio_rh

#### ✅ DemissaoPrevista (Atualizado)

-   Arquivo: `/app/Models/DemissaoPrevista.php`
-   Adicionados campos fillable e casts
-   Novo relacionamento: `AprovacaoExtra()`

#### ✅ FeriasPrevista (Atualizado)

-   Arquivo: `/app/Models/FeriasPrevista.php`
-   Adicionados campos fillable e casts
-   Novo relacionamento: `AprovacaoExtra()`

### 3. **Controller**

#### ✅ AprovacaoExtraConfigController

-   Arquivo: `/app/Http/Controllers/AprovacaoExtraConfigController.php`
-   CRUD completo para gerenciar configurações
-   Métodos principais:
    -   `index()` - Interface principal
    -   `listar()` - Lista todas as configs da empresa
    -   `buscarPorTipo()` - Busca config ativa por tipo
    -   `store()` - Criar nova configuração
    -   `update()` - Atualizar configuração
    -   `destroy()` - Deletar configuração
    -   `toggleAtivo()` - Ativar/desativar
    -   `tiposProcesso()` - Lista tipos disponíveis
    -   `podeAprovar()` - Verifica se usuário pode aprovar
    -   `listarUsuarios()` - Lista usuários para seleção

### 4. **Documentação e Exemplos**

#### ✅ README Principal

-   Arquivo: `/docs/README_APROVACAO_EXTRA.md`
-   Documentação completa do sistema
-   Exemplos de uso
-   API endpoints
-   Fluxo de trabalho

#### ✅ Exemplos de Implementação

-   Arquivo: `/docs/EXEMPLO_USO_APROVACAO_EXTRA.php`
-   6 exemplos práticos de como usar no controller
-   Código pronto para copiar e adaptar

#### ✅ Componente Vue.js

-   Arquivo: `/docs/EXEMPLO_COMPONENTE_VUE_APROVACAO_EXTRA.vue`
-   Interface completa para gerenciar configurações
-   Pronto para usar ou adaptar

#### ✅ Rotas

-   Arquivo: `/docs/ROTAS_APROVACAO_EXTRA.php`
-   Todas as rotas necessárias
-   Exemplos de organização

## 🎯 Como Funciona

### Cenário Exemplo: Empresa precisa de aprovação SESMT para demissões

1. **Configuração** (Administrador da Empresa)

    ```php
    AprovacaoExtraConfig::create([
        'empresa_id' => 1,
        'tipo_processo' => 'demissao',
        'nome_aprovacao' => 'SESMT',
        'ativo' => true
    ]);
    ```

2. **Solicitação de Demissão** (RH/Gestor)

    - Sistema verifica automaticamente se existe aprovação extra configurada
    - Se sim, adiciona essa etapa no fluxo

3. **Fluxo de Aprovação** ⚠️ **IMPORTANTE: RH SEMPRE É A ÚLTIMA APROVAÇÃO**

    ```
    1. Gestor aprova     ✓
    2. SESMT aprova      ✓ ← Aprovação Extra (configurável)
    3. RH aprova         ✓ ← SEMPRE A ÚLTIMA
    4. Processo concluído ✓
    ```

4. **Configurar Usuários Autorizados**

    ```php
    AprovacaoExtraConfig::create([
        'empresa_id' => 1,
        'tipo_processo' => 'demissao',
        'nome_aprovacao' => 'SESMT',
        'usuarios_autorizados' => [5, 12, 23], // IDs dos usuários SESMT
        'ativo' => true
    ]);

    // Usuários com privilegio_rh também podem aprovar
    ```

5. **Aprovação pelo SESMT**
    ```php
    // Sistema verifica automaticamente se usuário pode aprovar
    $config = AprovacaoExtraConfig::getConfigAtiva($empresaId, 'demissao');
    if ($config->podeAprovar($userId)) {
        $demissao->update([
            'aprovacao_extra_id' => $userId,
            'status_aprovacao_extra' => 'aprovado',
            'obs_aprovacao_extra' => 'Documentação OK',
            'data_aprovacao_extra' => now()
        ]);
    }
    ```

## 🔧 Próximos Passos para Implementação Completa

### 1. Executar Migrations

```bash
cd /Users/juniorfreitas/Desktop/WEB/2025/mybp
php artisan migrate
```

### 2. Adicionar Rotas

Copiar rotas de `/docs/ROTAS_APROVACAO_EXTRA.php` para `routes/web.php`

### 3. Criar Permissões/Habilidades

```sql
INSERT INTO habilidades (nome, descricao) VALUES
('administracao_aprovacao_extra_config', 'Gerenciar configurações de aprovação extra'),
('planejamento_movimentacao_demissao_aprovar_extra', 'Aprovar demissões (aprovação extra)'),
('planejamento_movimentacao_ferias_aprovar_extra', 'Aprovar férias (aprovação extra)');
```

### 4. Criar View Principal

Criar: `resources/views/g/administracao/aprovacao-extra-config/index.blade.php`

### 5. Adicionar no Menu

Adicionar link no menu de Administração para acessar as configurações

### 6. Atualizar Controllers de Demissão e Férias

-   Usar exemplos de `/docs/EXEMPLO_USO_APROVACAO_EXTRA.php`
-   Adicionar métodos: `aprovarExtra()` e `pendentesAprovacaoExtra()`

### 7. Atualizar Componentes Vue Existentes

-   Adicionar lógica para verificar se tem aprovação extra
-   Mostrar campos condicionalmente
-   Adicionar botões de aprovação extra

### 8. Notificações

-   Notificar aprovador extra quando demissão/férias for aprovada por gestor e RH
-   Notificar RH quando aprovação extra for concluída

### 9. Relatórios

-   Atualizar relatórios para incluir informações de aprovação extra
-   Adicionar filtros por status de aprovação extra

## 💡 Vantagens da Solução

✅ **Flexível**: Cada empresa configura conforme sua necessidade
✅ **Dinâmica**: Não precisa alterar código para adicionar aprovadores
✅ **Escalável**: Fácil adicionar novos tipos de processos
✅ **Rastreável**: Histórico completo de aprovações
✅ **Personalizável**: Nome pode ser qualquer coisa (SESMT, Supervisor, etc.)
✅ **Compatível**: Não quebra registros antigos (campos nullable)
✅ **Reutilizável**: Mesma estrutura serve para todos os processos

## 📊 Tipos de Processo Suportados

-   ✅ Demissão
-   ✅ Férias
-   ✅ Mudança de Cargo
-   ✅ Transferência
-   ✅ Intermitente para Fixo
-   ✅ Valor Extra

## 🎨 Exemplo Real de Uso

### Empresa A

-   Demissão → Aprovação extra: **SESMT**
-   Férias → Aprovação extra: **Coordenador**

### Empresa B

-   Demissão → Aprovação extra: **Supervisor**
-   Férias → Nenhuma aprovação extra
-   Mudança de Cargo → Aprovação extra: **Diretor**

### Empresa C

-   Demissão → Aprovação extra: **Jurídico**
-   Férias → Aprovação extra: **Gerente de Área**
-   Transferência → Aprovação extra: **Gestor Destino**

## 📝 Observações Importantes

1. **Apenas UMA configuração ativa por tipo de processo por empresa**

    - Ao ativar uma nova, as outras são desativadas automaticamente

2. **Campos nullable**

    - Se não houver configuração, campos ficam null
    - Não quebra funcionalidade atual

3. **Verificação automática**

    - Sistema verifica automaticamente se existe config ativa
    - Se não existir, funciona como antes

4. **Status de aprovação**
    - `aprovado` ou `reprovado`
    - Constantes definidas nos models

## 🚀 Status da Implementação

-   ✅ Models criados
-   ✅ Migrations criadas
-   ✅ Controller criado
-   ✅ Documentação completa
-   ✅ Exemplos de código
-   ✅ Componente Vue exemplo
-   ⏳ Migrations executadas
-   ⏳ Rotas adicionadas
-   ⏳ Views criadas
-   ⏳ Integração com controllers existentes
-   ⏳ Notificações implementadas
-   ⏳ Testes realizados

## 📞 Suporte

Para dúvidas ou problemas:

1. Consulte `/docs/README_APROVACAO_EXTRA.md`
2. Veja exemplos em `/docs/EXEMPLO_USO_APROVACAO_EXTRA.php`
3. Use componente exemplo em `/docs/EXEMPLO_COMPONENTE_VUE_APROVACAO_EXTRA.vue`

---

**Criado em:** 30/01/2025
**Branch:** feature/aprovacao-extra
