# 📚 Índice da Documentação - Sistema de Aprovação Extra

## 🎯 Visão Geral

Este sistema permite que cada empresa configure aprovações extras personalizadas para diferentes processos de movimentação (demissão, férias, mudança de cargo, etc.), de forma totalmente dinâmica e configurável.

---

## 📂 Arquivos Criados

### 1. **Código Principal**

#### Models

-   📄 [`/app/Models/AprovacaoExtraConfig.php`](../app/Models/AprovacaoExtraConfig.php)

    -   Model principal para gerenciar configurações
    -   Constantes e métodos auxiliares

-   📄 [`/app/Models/DemissaoPrevista.php`](../app/Models/DemissaoPrevista.php) _(Atualizado)_

    -   Campos e relacionamento de aprovação extra adicionados

-   📄 [`/app/Models/FeriasPrevista.php`](../app/Models/FeriasPrevista.php) _(Atualizado)_
    -   Campos e relacionamento de aprovação extra adicionados

#### Controllers

-   📄 [`/app/Http/Controllers/AprovacaoExtraConfigController.php`](../app/Http/Controllers/AprovacaoExtraConfigController.php)
    -   CRUD completo
    -   Métodos auxiliares para consultas

#### Migrations

-   📄 [`/database/migrations/2025_01_30_000001_create_aprovacao_extra_configs_table.php`](../database/migrations/2025_01_30_000001_create_aprovacao_extra_configs_table.php)

    -   Cria tabela de configurações

-   📄 [`/database/migrations/2025_01_30_000002_add_aprovacao_extra_to_demissao_previstas_table.php`](../database/migrations/2025_01_30_000002_add_aprovacao_extra_to_demissao_previstas_table.php)

    -   Adiciona campos em demissao_previstas

-   📄 [`/database/migrations/2025_01_30_000003_add_aprovacao_extra_to_ferias_previstas_table.php`](../database/migrations/2025_01_30_000003_add_aprovacao_extra_to_ferias_previstas_table.php)
    -   Adiciona campos em ferias_previstas

---

### 2. **Documentação**

#### Documentação Principal

-   📘 [`README_APROVACAO_EXTRA.md`](README_APROVACAO_EXTRA.md) ⭐
    -   **Leia primeiro!**
    -   Visão geral completa
    -   Como funciona
    -   Exemplos de uso
    -   API endpoints

#### Guias Visuais

-   📊 [`FLUXO_VISUAL_APROVACAO_EXTRA.md`](FLUXO_VISUAL_APROVACAO_EXTRA.md)
    -   Diagramas e fluxogramas
    -   Comparação antes/depois
    -   Estados da solicitação
    -   Mockups de interface

#### Resumo Executivo

-   📋 [`RESUMO_APROVACAO_EXTRA.md`](RESUMO_APROVACAO_EXTRA.md)
    -   Resumo rápido do que foi criado
    -   Próximos passos
    -   Status da implementação
    -   Observações importantes

---

### 3. **Exemplos de Código**

#### Exemplos PHP

-   💻 [`EXEMPLO_USO_APROVACAO_EXTRA.php`](EXEMPLO_USO_APROVACAO_EXTRA.php)
    -   6 exemplos práticos
    -   Código pronto para copiar
    -   Casos de uso reais
    -   Verificações e validações

#### Exemplos Vue.js

-   🎨 [`EXEMPLO_COMPONENTE_VUE_APROVACAO_EXTRA.vue`](EXEMPLO_COMPONENTE_VUE_APROVACAO_EXTRA.vue)
    -   Componente completo
    -   Interface de configuração
    -   CRUD funcional
    -   Pronto para adaptar

#### Rotas

-   🛣️ [`ROTAS_APROVACAO_EXTRA.php`](ROTAS_APROVACAO_EXTRA.php)
    -   Todas as rotas necessárias
    -   Exemplos de organização
    -   Middleware e permissões

---

### 4. **Implementação**

#### Checklist

-   ✅ [`CHECKLIST_IMPLEMENTACAO_APROVACAO_EXTRA.md`](CHECKLIST_IMPLEMENTACAO_APROVACAO_EXTRA.md) ⭐
    -   **Use para implementar!**
    -   13 fases detalhadas
    -   Checkboxes para marcar progresso
    -   Troubleshooting

#### SQL

-   🗃️ [`SQL_APROVACAO_EXTRA_SETUP.sql`](SQL_APROVACAO_EXTRA_SETUP.sql)
    -   Criar habilidades
    -   Configurações exemplo
    -   Consultas úteis
    -   Scripts de manutenção

---

## 🚀 Por Onde Começar?

### Para Desenvolvedores

1. **Entender o Sistema**

    - 📘 Leia: [`README_APROVACAO_EXTRA.md`](README_APROVACAO_EXTRA.md)
    - 📊 Veja: [`FLUXO_VISUAL_APROVACAO_EXTRA.md`](FLUXO_VISUAL_APROVACAO_EXTRA.md)

2. **Implementar**

    - ✅ Siga: [`CHECKLIST_IMPLEMENTACAO_APROVACAO_EXTRA.md`](CHECKLIST_IMPLEMENTACAO_APROVACAO_EXTRA.md)
    - 🗃️ Execute: [`SQL_APROVACAO_EXTRA_SETUP.sql`](SQL_APROVACAO_EXTRA_SETUP.sql)

3. **Integrar**
    - 💻 Copie código de: [`EXEMPLO_USO_APROVACAO_EXTRA.php`](EXEMPLO_USO_APROVACAO_EXTRA.php)
    - 🎨 Adapte Vue de: [`EXEMPLO_COMPONENTE_VUE_APROVACAO_EXTRA.vue`](EXEMPLO_COMPONENTE_VUE_APROVACAO_EXTRA.vue)
    - 🛣️ Adicione rotas de: [`ROTAS_APROVACAO_EXTRA.php`](ROTAS_APROVACAO_EXTRA.php)

### Para Gestores/Product Owners

1. **Visão Geral**

    - 📋 Leia: [`RESUMO_APROVACAO_EXTRA.md`](RESUMO_APROVACAO_EXTRA.md)
    - 📊 Veja diagramas em: [`FLUXO_VISUAL_APROVACAO_EXTRA.md`](FLUXO_VISUAL_APROVACAO_EXTRA.md)

2. **Planejamento**
    - Defina quais empresas vão usar
    - Identifique aprovadores extras necessários
    - Planeje treinamento

### Para Testadores

1. **Preparação**

    - 📘 Entenda em: [`README_APROVACAO_EXTRA.md`](README_APROVACAO_EXTRA.md)
    - ✅ Veja casos de teste em: [`CHECKLIST_IMPLEMENTACAO_APROVACAO_EXTRA.md`](CHECKLIST_IMPLEMENTACAO_APROVACAO_EXTRA.md) (Fase 10)

2. **Execução**
    - Teste cada cenário
    - Verifique permissões
    - Valide relatórios

---

## 🎯 Casos de Uso

### Cenário 1: Hospital - SESMT

```
Configuração:
- Empresa: Hospital XYZ
- Processo: Demissão
- Aprovador Extra: SESMT

Fluxo:
Solicitação → Gestor → RH → SESMT → Concluído
```

### Cenário 2: Construção - Engenheiro de Segurança

```
Configuração:
- Empresa: Construtora ABC
- Processo: Demissão
- Aprovador Extra: Engenheiro de Segurança

Fluxo:
Solicitação → Gestor → RH → Eng. Segurança → Concluído
```

### Cenário 3: Indústria - Múltiplos Processos

```
Configuração:
- Empresa: Indústria DEF
- Demissão: Supervisor
- Férias: Supervisor
- Mudança Cargo: Diretor

Fluxos independentes por tipo de processo
```

---

## 📊 Estrutura de Dados

### Tabela Principal: `aprovacao_extra_configs`

```sql
id | empresa_id | tipo_processo | nome_aprovacao | ativo
1  | 1          | demissao      | SESMT          | true
2  | 1          | ferias        | Coordenador    | true
3  | 2          | demissao      | Supervisor     | true
```

### Tabelas Afetadas: `demissao_previstas`, `ferias_previstas`

```
Novos campos:
- aprovacao_extra_id (FK -> users)
- status_aprovacao_extra ('aprovado'/'reprovado')
- obs_aprovacao_extra
- data_aprovacao_extra
```

---

## 🔧 Comandos Úteis

### Executar Migrations

```bash
php artisan migrate
```

### Rollback (se necessário)

```bash
php artisan migrate:rollback --step=3
```

### Ver Rotas

```bash
php artisan route:list | grep aprovacao-extra
```

### Limpar Cache

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

### Compilar Assets

```bash
npm run dev      # Desenvolvimento
npm run watch    # Watch mode
npm run prod     # Produção
```

---

## 📞 Suporte

### Documentação

-   Consulte os arquivos em `/docs/`
-   Todos os exemplos estão prontos para uso

### Problemas Comuns

-   Veja seção "Troubleshooting" em [`CHECKLIST_IMPLEMENTACAO_APROVACAO_EXTRA.md`](CHECKLIST_IMPLEMENTACAO_APROVACAO_EXTRA.md)

### Dúvidas

-   Consulte exemplos em [`EXEMPLO_USO_APROVACAO_EXTRA.php`](EXEMPLO_USO_APROVACAO_EXTRA.php)

---

## 📈 Roadmap

### ✅ Fase 1 - COMPLETA

-   [x] Models criados
-   [x] Migrations criadas
-   [x] Controller criado
-   [x] Documentação completa

### ⏳ Fase 2 - PENDENTE

-   [ ] Migrations executadas
-   [ ] Rotas adicionadas
-   [ ] Views criadas
-   [ ] Componentes Vue integrados

### ⏳ Fase 3 - PENDENTE

-   [ ] Testes realizados
-   [ ] Deploy em produção
-   [ ] Treinamento de usuários

---

## 🎉 Resultado Final

Um sistema totalmente **flexível**, **dinâmico** e **configurável** que permite:

✅ Cada empresa definir seus próprios aprovadores extras
✅ Nomear as aprovações conforme sua cultura organizacional
✅ Aplicar para diferentes tipos de processos
✅ Manter rastreabilidade completa
✅ Funcionar sem quebrar o sistema atual

---

**Versão:** 1.0  
**Data:** 30/01/2025  
**Branch:** feature/aprovacao-extra  
**Status:** Pronto para implementação 🚀
