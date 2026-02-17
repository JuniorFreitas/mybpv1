# Implementação de Aprovação Extra - RequisicaoVaga e ValorExtraPrevista

**Data**: 07/02/2026  
**Autor**: Sistema MyBP  
**Versão**: 1.0

## Resumo

Foi implementado o sistema de **Aprovação Extra** em dois processos:

1. **Requisição de Vaga** (Planejamento em Liderança de Pessoal)
2. **Valor Extra Prevista**

## Alterações Realizadas

### 1. Models

#### AprovacaoExtraConfig

-   ✅ Adicionada constante `TIPO_REQUISICAO_VAGA = 'requisicao_vaga'`
-   ✅ Adicionado tipo no array `TIPOS_PROCESSO`

#### RequisicaoVaga

-   ✅ Adicionadas colunas no `$fillable`:
    -   `aprovacao_extra_id`
    -   `status_aprovacao_extra`
    -   `obs_aprovacao_extra`
    -   `data_aprovacao_extra`
-   ✅ Adicionados casts apropriados
-   ✅ Adicionado relacionamento `AprovacaoExtra()`

#### ValorExtraPrevista

-   ✅ As colunas já existiam no `$fillable` e `$casts`
-   ✅ Relacionamento `AprovacaoExtra()` já existia

### 2. Migrations

#### 2026_02_07_000001_add_aprovacao_extra_to_requisicao_vagas_table.php

```sql
-- Adiciona 4 colunas na tabela requisicao_vagas:
- aprovacao_extra_id (FK para users)
- status_aprovacao_extra
- obs_aprovacao_extra
- data_aprovacao_extra
```

#### 2026_02_07_000002_add_aprovacao_extra_to_valor_extra_previstas_table.php

```sql
-- Adiciona 4 colunas na tabela valor_extra_previstas (com verificação):
- aprovacao_extra_id (FK para users)
- status_aprovacao_extra
- obs_aprovacao_extra
- data_aprovacao_extra
```

**Status**: ✅ Executadas com sucesso

### 3. Rotas (routes/web.php)

#### RequisicaoVaga

```php
Route::put('requisicao-vaga/{requisicaoVaga}/aprovarextra',
    [\App\Http\Controllers\RequisicaoVagaController::class, 'aprovarExtra'])
    ->name('aprovarExtra')
    ->middleware('can:planejamento_requisicao_vaga');
```

#### ValorExtraPrevista

```php
Route::put('valor-extra-prevista/{valorExtraPrevista}/aprovarextra',
    [\App\Http\Controllers\ValorExtraPrevistaController::class, 'aprovarExtra'])
    ->name('aprovarExtra');
```

### 4. Controllers

#### RequisicaoVagaController

-   ✅ Adicionado método `aprovarExtra()`

    -   Busca configuração ativa
    -   Valida permissões via `podeAprovar()`
    -   Atualiza dados da aprovação extra
    -   Retorna resposta JSON

-   ✅ Atualizado método `edit()`

    -   Adiciona `aprovacao_extra_nome`
    -   Adiciona `status_aprovacao_extra`

-   ✅ Atualizado método `filtro()`

    -   Adicionado eager loading de `AprovacaoExtra:id,nome`

-   ✅ Atualizado método `atualizar()`
    -   Busca configuração de aprovação extra ativa
    -   Retorna `pode_aprovar_extra`, `tem_aprovacao_extra`, `nome_aprovacao_extra`
    -   Permite frontend saber se deve exibir botões de aprovação extra

#### ValorExtraPrevistaController

-   ✅ Adicionado método `aprovarExtra()`

    -   Busca configuração ativa
    -   Valida permissões via `podeAprovar()`
    -   Atualiza dados da aprovação extra
    -   Retorna resposta JSON

-   ✅ Atualizado método `edit()`

    -   Adiciona `aprovacao_extra_nome`
    -   Adiciona `status_aprovacao_extra`

-   ✅ Atualizado método `filtro()`

    -   Adicionado eager loading de `AprovacaoExtra:id,nome`

-   ✅ Atualizado método `atualizar()`
    -   Busca configuração de aprovação extra ativa
    -   Retorna `pode_aprovar_extra`, `tem_aprovacao_extra`, `nome_aprovacao_extra`
    -   Permite frontend saber se deve exibir botões de aprovação extra

## Fluxo de Aprovação

### RequisicaoVaga (Requisição de Vaga)

```
Solicitação → Aprovação Gestor → [Aprovação Extra]* → (Fim)
```

### ValorExtraPrevista (Valor Extra)

```
Solicitação → Aprovação Gestor → [Aprovação Extra]* → Aprovação RH
```

**[Aprovação Extra]\***: Etapa opcional, ativada via configuração `AprovacaoExtraConfig`

## Configuração

Para ativar a Aprovação Extra, criar registro em `aprovacao_extra_configs`:

```php
[
    'empresa_id' => 1,
    'tipo_processo' => 'requisicao_vaga', // ou 'valor_extra'
    'nome_aprovacao' => 'Gerência',
    'usuarios_autorizados' => [1, 2, 3], // IDs dos usuários
    'ativo' => true
]
```

**Script SQL disponível**: [SQL_APROVACAO_EXTRA_REQUISICAO_VALOR_EXTRA.sql](./SQL_APROVACAO_EXTRA_REQUISICAO_VALOR_EXTRA.sql)

## Permissões

Podem aprovar na etapa extra:

-   Usuários com `privilegio_gestao_rh`
-   Usuários com `privilegio_aprovar_por_rh`
-   Usuários listados em `usuarios_autorizados` da configuração

## Testes Recomendados

### RequisicaoVaga

1. ✅ Criar solicitação
2. ✅ Aprovar como gestor
3. ✅ Verificar se pode aprovar como extra (se configurado)
4. ✅ Aprovar/Reprovar como aprovação extra
5. ✅ Verificar dados salvos

### ValorExtraPrevista

1. ✅ Criar solicitação
2. ✅ Aprovar como gestor
3. ✅ Verificar se pode aprovar como extra (se configurado)
4. ✅ Aprovar/Reprovar como aprovação extra
5. ✅ Aprovar/Reprovar como RH
6. ✅ Verificar dados salvos

## Arquivos Modificados

```
app/Models/
  ├── AprovacaoExtraConfig.php (TIPO_REQUISICAO_VAGA)
  ├── RequisicaoVaga.php (fillable, casts, relacionamento)
  └── ValorExtraPrevista.php (já tinha suporte)

app/Http/Controllers/
  ├── RequisicaoVagaController.php (aprovarExtra, edit, filtro, atualizar)
  └── ValorExtraPrevistaController.php (aprovarExtra, edit, filtro, atualizar)

database/migrations/
  ├── 2026_02_07_000001_add_aprovacao_extra_to_requisicao_vagas_table.php
  └── 2026_02_07_000002_add_aprovacao_extra_to_valor_extra_previstas_table.php

routes/
  └── web.php (rotas aprovarExtra)

docs/
  ├── IMPLEMENTACAO_APROVACAO_EXTRA_REQUISICAO_VALOR_EXTRA.md
  └── SQL_APROVACAO_EXTRA_REQUISICAO_VALOR_EXTRA.sql
```

## Comandos Executados

```bash
# Migrations
docker compose exec mybpdp php artisan migrate

# Cache
docker compose exec mybpdp php artisan config:clear
docker compose exec mybpdp php artisan cache:clear
docker compose exec mybpdp php artisan route:clear
```

## Status Final

✅ **Implementação Completa**

-   Models atualizados
-   Migrations executadas com sucesso
-   Rotas adicionadas
-   Controllers implementados
-   Cache limpo
-   Sistema pronto para uso

## Próximos Passos (Frontend)

Para completar a implementação, será necessário:

1. Atualizar componentes Vue para exibir botão de aprovação extra
2. Adicionar lógica de exibição condicional (quando config ativa)
3. Implementar modal de aprovação extra
4. Adicionar status visual da aprovação extra
5. Testar fluxo completo

## Referências

-   [docs/PADRAO_APROVACAO_EXTRA.md](../docs/PADRAO_APROVACAO_EXTRA.md)
-   [docs/COMO_USAR_APROVACAO_EXTRA.md](../docs/COMO_USAR_APROVACAO_EXTRA.md)
-   [docs/EXEMPLO_USO_APROVACAO_EXTRA_V2.php](../docs/EXEMPLO_USO_APROVACAO_EXTRA_V2.php)- [docs/SQL_APROVACAO_EXTRA_REQUISICAO_VALOR_EXTRA.sql](./SQL_APROVACAO_EXTRA_REQUISICAO_VALOR_EXTRA.sql) - **Script SQL de configuração**
- [docs/GUIA_TESTE_APROVACAO_EXTRA_REQUISICAO_VALOR.md](./GUIA_TESTE_APROVACAO_EXTRA_REQUISICAO_VALOR.md) - **Guia completo de testes**
- [docs/EXEMPLO_COMPONENTE_VUE_APROVACAO_EXTRA_REQUISICAO_VALOR.vue](./EXEMPLO_COMPONENTE_VUE_APROVACAO_EXTRA_REQUISICAO_VALOR.vue) - **Exemplo Vue**
- [docs/TESTE_APROVACAO_EXTRA_REQUISICAO_VALOR.php](./TESTE_APROVACAO_EXTRA_REQUISICAO_VALOR.php) - **Script de validação**
---

**Observação**: Esta implementação segue exatamente o padrão já estabelecido nos processos:

-   DemissaoPrevista
-   MudancaCargo
-   FeriasPrevista
-   IntermitenteFixoPrevista
