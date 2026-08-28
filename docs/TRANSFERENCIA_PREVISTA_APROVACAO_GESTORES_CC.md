# Transferência Prevista: Aprovação dos Gestores de Origem e Destino (CC)

## Objetivo

Documentar a implementação da aprovação automática dos gestores de **origem** e **destino** na **Solicitação de Transferência** (`TransferenciaPrevista`), substituindo a seleção manual do gestor aprovador e introduzindo infraestrutura de gestor substituto e gestor superior por centro de custo.

**Data da implementação:** agosto/2026

---

## Problema anterior

O fluxo antigo exigia que o solicitante escolhesse manualmente um **Gestor Aprovação** (`gestor_id`). Esse gestor não necessariamente representava o responsável pelo centro de custo de origem ou de destino.

```
Solicitação → Gestor manual → Aprovação Extra (opcional) → RH → Efetivação
```

---

## Fluxo implementado

```
Solicitação criada
    → Aprovação Gestor CC Origem
        → (reprovado) Processo encerrado
        → (aprovado) Gestor origem ≠ gestor destino?
            → (sim) Aprovação Gestor CC Destino
                → (reprovado) Processo encerrado
                → (aprovado) Aprovação Extra (se configurada)
            → (não) Aprovação Extra (se configurada)
    → RH
    → Efetivação (atualiza admissao.centro_custo_id)
```

### Ordem das etapas

1. **Gestor CC Origem** — sempre
2. **Gestor CC Destino** — somente se o gestor resolvido for diferente do origem
3. **Aprovação Extra** — se configurada em `AprovacaoExtraConfig`
4. **RH**

---

## Regras de negócio

| Regra | Descrição | Implementação |
|-------|-----------|---------------|
| **RN01** | Gestores resolvidos automaticamente via CC | `TransferenciaPrevistaFluxoAprovacaoService::aplicarFluxoGestores()` |
| **RN02/RN03** | Mesmo gestor origem/destino = uma única etapa | `deveExigirAprovacaoGestorDestino()` retorna `false`; `gestor_destino_id = null` |
| **RN04** | Extra/RH bloqueados enquanto gestor destino pendente | Guards em `aprovarExtra()` e `aprovarRH()` |
| **RN05** | Reprovação em qualquer etapa de gestor encerra o processo | `status_aprovacao` ou `status_aprovacao_gestor_destino = reprovado` |
| **RN07** | Solicitante = gestor: cadeia substituto → superior → bloqueio | `CentroCustoGestorResolverService::resolverAprovador()` |
| **RN09** | Alterar CC antes das aprovações recalcula gestores | `update()` detecta mudança de CC e reaplica fluxo se ainda pendente |

---

## Decisões arquiteturais

### Gestor origem — reutilização de colunas existentes

| Campo existente | Novo significado |
|-----------------|------------------|
| `gestor_id` | Aprovador resolvido do CC origem (antes: manual) |
| `status_aprovacao` | Status da aprovação do gestor origem |
| `user_aprovacao_id` | Quem aprovou/reprovou (origem) |
| `data_aprovacao`, `obs_aprovacao` | Dados da etapa origem |

### Gestor destino — novas colunas

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| `exige_aprovacao_gestor_destino` | boolean | Se há etapa separada de destino |
| `fluxo_gestores_automatico` | boolean | `true` = novo fluxo; `false` = legado |
| `gestor_destino_id` | FK users | Aprovador resolvido do CC destino |
| `user_aprovacao_gestor_destino_id` | FK users | Quem aprovou/reprovou destino |
| `status_aprovacao_gestor_destino` | string nullable | `aprovado` / `reprovado` |
| `obs_aprovacao_gestor_destino` | text nullable | Observação da etapa destino |
| `data_aprovacao_gestor_destino` | datetime nullable | Data/hora da aprovação destino |

### Infraestrutura de substituto (RN07)

Nova tabela `centro_custo_gestores`:

| Coluna | Descrição |
|--------|-----------|
| `centro_custo_id` | FK para `centro_custos` |
| `usuario_id` | FK para `users` |
| `tipo` | `GESTOR_PRINCIPAL` ou `GESTOR_SUBSTITUTO` |
| `ativo` | Vigência ativa |
| `inicio_vigencia`, `fim_vigencia` | Período opcional |
| `empresa_id` | Multi-tenant |

- Data migration copia `centro_custos.gestor_id` → `GESTOR_PRINCIPAL`
- `centro_custos.gestor_id` permanece espelhado para compatibilidade com outros módulos
- `users.gestor_superior_id` permite escalonamento quando solicitante = gestor

### Compatibilidade legado

Solicitações **em andamento** criadas antes da feature mantêm `fluxo_gestores_automatico = false` e seguem o fluxo antigo (sem exigir gestor destino). Novas solicitações recebem `fluxo_gestores_automatico = true` automaticamente.

---

## Banco de dados

### Migrations criadas

| Arquivo | Descrição |
|---------|-----------|
| `2026_08_27_100000_create_centro_custo_gestores_table.php` | Cria tabela `centro_custo_gestores` |
| `2026_08_27_100001_migrate_centro_custos_gestor_to_centro_custo_gestores.php` | Migra gestores existentes |
| `2026_08_27_100002_add_gestor_superior_id_to_users_table.php` | Adiciona `gestor_superior_id` em `users` |
| `2026_08_27_100003_add_gestor_destino_fields_to_transferencia_previstas_table.php` | Colunas de gestor destino |

### Comando de deploy

```bash
docker compose exec mybpdp php artisan migrate
```

---

## Backend

### Models

| Arquivo | Alteração |
|---------|-----------|
| `app/Models/CentroCustoGestor.php` | **Novo** — entidade de gestor principal/substituto por CC |
| `app/Models/CentroCusto.php` | Relações `Gestores`, `GestorPrincipal`, `GestorSubstituto` |
| `app/Models/TransferenciaPrevista.php` | Novos campos, casts e relações `GestorOrigem`, `GestorDestino`, `QuemAprovouGestorDestino` |
| `app/Models/User.php` | Campo `gestor_superior_id` e relação `GestorSuperior()` |

### Services

#### `CentroCustoGestorResolverService`

Responsável por resolver o aprovador de um centro de custo.

```php
getGestorPrincipal(int $centroCustoId): ?User
getGestorSubstituto(int $centroCustoId): ?User
resolverAprovador(int $centroCustoId, int $solicitanteId): User
usuarioAtivo(?User $user): bool
```

**Cadeia de resolução (RN07):**

1. Gestor principal do CC
2. Gestor substituto do CC
3. Gestor superior do principal (`users.gestor_superior_id`)
4. Exceção se nenhum candidato ativo e diferente do solicitante (evita autoaprovação)

Fallback: se não houver registro em `centro_custo_gestores`, usa `centro_custos.gestor_id`.

#### `CentroCustoGestorSyncService`

Sincroniza gestor principal e substituto no cadastro de centro de custo, mantendo `centro_custos.gestor_id` atualizado.

#### `TransferenciaPrevistaFluxoAprovacaoService`

Orquestra o fluxo de aprovação da transferência.

```php
validarCentrosCusto(?int $origemId, int $destinoId, int $empresaId): void
resolverGestorOrigem(?int $origemId, int $solicitanteId): User
resolverGestorDestino(int $destinoId, int $solicitanteId): User
deveExigirAprovacaoGestorDestino(User $gestorOrigem, User $gestorDestino): bool
montarDadosFluxoGestores(...): array
aplicarFluxoGestores(TransferenciaPrevista $t, int $solicitanteId, bool $resetarEtapas = true): void
etapaAtual(TransferenciaPrevista $t): string
podeAprovarGestorOrigem(TransferenciaPrevista $t, User $user): bool
podeAprovarGestorDestino(TransferenciaPrevista $t, User $user): bool
gestoresEtapasConcluidas(TransferenciaPrevista $t): bool
isFluxoLegado(TransferenciaPrevista $t): bool
```

**Constantes de etapa:**

- `gestor_origem`
- `gestor_destino`
- `extra`
- `rh`
- `concluido`
- `reprovado`

### Controller — `TransferenciaPrevistaController`

| Método | Comportamento |
|--------|---------------|
| `store()` | Remove `gestor_id` do payload; aplica fluxo automático; CC origem obrigatório |
| `update()` | Recalcula gestores se CC origem/destino mudar antes da aprovação origem |
| `aprovar()` | Aprovação gestor **origem** com `lockForUpdate()` e validação por usuário designado |
| `aprovarGestorDestino()` | **Novo** — aprovação gestor destino com `lockForUpdate()` |
| `aprovarExtra()` | Guard: gestores origem/destino devem estar concluídos |
| `aprovarRH()` | Guard: gestores origem/destino devem estar concluídos |
| `gestorResponsavel()` | **Novo** — retorna gestor principal, substituto e resolvido para um CC |
| `edit()` | Carrega labels de gestores e flags `pode_aprovar_gestor_origem/destino` |

**Logs estruturados:**

- `transferencia.criada`
- `transferencia.aprovacao_gestor_origem.{aprovado|reprovado}`
- `transferencia.aprovacao_gestor_destino.{aprovado|reprovado}`

### Rotas

Prefixo: `/g/planejamento/movimentacao/`

| Método | Rota | Nome |
|--------|------|------|
| GET | `centro-custo/{centrocusto}/gestor-responsavel` | `centro-custo.gestor-responsavel` |
| PUT | `transferencia-prevista/{id}/aprovar` | `aprovar` (gestor origem) |
| PUT | `transferencia-prevista/{id}/aprovar-gestor-destino` | `aprovarGestorDestino` |
| PUT | `transferencia-prevista/{id}/aprovar-extra` | `aprovarExtra` |
| PUT | `transferencia-prevista/{id}/aprovarrh` | `aprovarRH` |
| POST | `transferencia-prevista` | `store` |
| PUT/PATCH | `transferencia-prevista/{id}` | `update` |

### Listagem e exportação

| Arquivo | Alteração |
|---------|-----------|
| `TransferenciaPrevistaFilterApplier.php` | Visibilidade inclui `gestor_destino_id` além de `gestor_id` e solicitante |
| `TransferenciaPrevistaExportFormatter.php` | Coluna "Gestor Aprovação" renomeada para "Gestor Origem"; adicionadas colunas de gestor destino |
| `TransferenciaPrevistaExportQueryBuilder.php` | Eager-load de `GestorOrigem`, `GestorDestino` e aprovadores |

---

## Notificações

### Job — `JobNotificacaoRecursiva`

Novos tipos de notificação:

| Tipo | Quando dispara | Destinatário |
|------|----------------|--------------|
| `criacao_gestor_origem` | Solicitação criada (fluxo automático) | `gestor_id` (origem) |
| `criacao_gestor_destino` | Origem aprovada + destino diferente | `gestor_destino_id` |
| `reprovado_gestor_origem` | Origem reprovou | Solicitante |
| `reprovado_gestor_destino` | Destino reprovou | Solicitante + gestor origem |
| `pendente_aprovacao_extra` | Após ambos gestores | Config aprovação extra |
| `pendente_aprovacao_rh` | Após gestores (e extra, se houver) | RH |

**Comportamento importante:**

- Gestor origem **reprova** → gestor destino **não é notificado**
- Gestor origem **aprova** + destino diferente → notifica gestor destino
- Fluxo legado (`fluxo_gestores_automatico = false`) mantém tipo `criacao` original

### Template de e-mail

Arquivo: `resources/views/emails/movimentacao/transferencia_prevista/notificacao-aprovacao.blade.php`

Timeline visual no e-mail:

```
Solicitante → Gestor Origem → Gestor Destino → Extra → RH
```

Todos os envios continuam via **fila** (`JobNotificacaoRecursiva::dispatch`).

---

## Frontend

### Solicitação de Transferência

Arquivo: `resources/js/components/planejamento/movimentacao/SolicitacaoTransferencia.vue`

| Mudança | Detalhe |
|---------|---------|
| Removido | Componente `<gestoraprovacao>` (seleção manual) |
| Adicionado | Campos read-only de gestor origem e destino |
| Adicionado | Seção "Aprovação Gestor Origem" no modal |
| Adicionado | Seção "Aprovação Gestor Destino" no modal |
| Adicionado | Botões na listagem por etapa |
| Adicionado | Timeline visual no card (Solicitante → Origem → Destino → Extra → RH) |
| Adicionado | `aprovarGestorDestino()` — PUT para nova rota |
| Adicionado | Watchers que consultam `GET centro-custo/{id}/gestor-responsavel` ao selecionar CC |

**Visibilidade dos botões:**

- "Aprovação Gestor Origem" — `gestor_id === usuarioLogado` e etapa pendente
- "Aprovação Gestor Destino" — origem aprovada, `gestor_destino_id === usuarioLogado` e destino pendente

### Cadastro de Centro de Custo

Arquivo: `resources/js/components/cadastros/centrocusto/CentroCusto.vue`

- Campo **Gestor substituto** com autocomplete (`autocomplete/todos-gestores-ativos/`)
- Backend: `CentroCustoController` chama `CentroCustoGestorSyncService` no `store`/`update`
- `edit()` retorna `gestor_substituto_id` e labels para o formulário

### Cadastro de Usuários

Arquivo: `resources/js/components/usuarios/Usuarios.vue`

- Campo **Gestor superior** visível quando o usuário é gestor (`form.gestor`)
- Backend: `UserController::edit()` carrega relação `GestorSuperior`
- Campo `gestor_superior_id` persistido via `fillable` no model `User`

---

## Testes

### Arquivos de teste

| Arquivo | Escopo |
|---------|--------|
| `tests/Unit/Services/TransferenciaPrevista/TransferenciaPrevistaFluxoAprovacaoServiceTest.php` | CT01, CT02, CT08, etapaAtual, fluxo legado |
| `tests/Unit/Services/CentroCusto/CentroCustoGestorResolverServiceTest.php` | Usuário ativo, exceção sem gestor |
| `tests/Unit/Http/Controllers/TransferenciaPrevistaOrigemOpcionalTest.php` | Normalização de CC origem vazio → null |

### Execução

```bash
docker compose exec mybpdp php artisan test \
  tests/Unit/Services/TransferenciaPrevista/TransferenciaPrevistaFluxoAprovacaoServiceTest.php \
  tests/Unit/Services/CentroCusto/CentroCustoGestorResolverServiceTest.php \
  tests/Unit/Http/Controllers/TransferenciaPrevistaOrigemOpcionalTest.php
```

### Cenários de teste manual

#### CT01 — Gestores diferentes

1. CC Origem com gestor Maria, CC Destino com gestor Carlos
2. Criar transferência
3. Verificar: `gestor_id = Maria`, `gestor_destino_id = Carlos`, `exige_aprovacao_gestor_destino = true`
4. Maria aprova → Carlos recebe notificação
5. Carlos aprova → Extra/RH liberados

#### CT02/CT08 — Mesmo gestor

1. CC Origem e Destino com mesmo gestor Maria
2. Verificar: `exige_aprovacao_gestor_destino = false`, sem botão de destino
3. Maria aprova uma vez → fluxo segue para Extra/RH

#### CT04 — Reprovação

1. Gestor origem reprova → destino não notificado, processo encerrado
2. Gestor destino reprova → solicitante e gestor origem notificados

#### RN07 — Solicitante = gestor

1. Configurar substituto e gestor superior no CC/usuário
2. Gestor cria transferência do próprio CC
3. Verificar escalonamento para substituto ou superior
4. Sem candidato válido → erro 422 ao salvar

#### RN09 — Alteração de CC

1. Criar solicitação pendente
2. Editar e mudar CC destino antes da aprovação origem
3. Verificar recálculo de gestores e reset de etapas pendentes

#### Legado

1. Solicitação antiga com `fluxo_gestores_automatico = false`
2. Verificar que não exige etapa de gestor destino

---

## Build frontend

Após alterações nos componentes Vue:

```bash
npm run dev    # desenvolvimento
npm run prod   # produção
```

---

## Arquivos alterados (referência completa)

### Novos

- `app/Models/CentroCustoGestor.php`
- `app/Services/CentroCusto/CentroCustoGestorResolverService.php`
- `app/Services/CentroCusto/CentroCustoGestorSyncService.php`
- `app/Services/TransferenciaPrevista/TransferenciaPrevistaFluxoAprovacaoService.php`
- `database/migrations/2026_08_27_100000_create_centro_custo_gestores_table.php`
- `database/migrations/2026_08_27_100001_migrate_centro_custos_gestor_to_centro_custo_gestores.php`
- `database/migrations/2026_08_27_100002_add_gestor_superior_id_to_users_table.php`
- `database/migrations/2026_08_27_100003_add_gestor_destino_fields_to_transferencia_previstas_table.php`
- `tests/Unit/Services/CentroCusto/CentroCustoGestorResolverServiceTest.php`
- `tests/Unit/Services/TransferenciaPrevista/TransferenciaPrevistaFluxoAprovacaoServiceTest.php`

### Modificados

- `app/Http/Controllers/TransferenciaPrevistaController.php`
- `app/Http/Controllers/CentroCustoController.php`
- `app/Http/Controllers/UserController.php`
- `app/Models/CentroCusto.php`
- `app/Models/TransferenciaPrevista.php`
- `app/Models/User.php`
- `app/Jobs/Movimentacao/TransferenciaPrevista/JobNotificacaoRecursiva.php`
- `app/Services/TransferenciaPrevista/TransferenciaPrevistaExportFormatter.php`
- `app/Services/TransferenciaPrevista/TransferenciaPrevistaExportQueryBuilder.php`
- `app/Services/TransferenciaPrevista/TransferenciaPrevistaFilterApplier.php`
- `resources/js/components/planejamento/movimentacao/SolicitacaoTransferencia.vue`
- `resources/js/components/cadastros/centrocusto/CentroCusto.vue`
- `resources/js/components/usuarios/Usuarios.vue`
- `resources/views/emails/movimentacao/transferencia_prevista/notificacao-aprovacao.blade.php`
- `routes/web.php`
- `tests/Unit/Http/Controllers/TransferenciaPrevistaOrigemOpcionalTest.php`

---

## Observações técnicas

- **CC origem obrigatório** no novo fluxo: necessário para resolver o gestor de origem automaticamente.
- **Concorrência:** `aprovar()` e `aprovarGestorDestino()` usam `lockForUpdate()` para evitar dupla aprovação.
- **Segurança:** aprovação restrita ao usuário designado (`gestor_id` / `gestor_destino_id`), não apenas ao privilégio genérico de gestor.
- **Testes com banco em memória:** testes de integração com `RefreshDatabase` podem falhar por incompatibilidade de migrations antigas com SQLite; os testes unitários atuais não dependem de banco.
- **Horizon/fila:** e-mails dependem do worker de fila ativo.

---

## Referências

- Controller principal: `app/Http/Controllers/TransferenciaPrevistaController.php`
- Service de fluxo: `app/Services/TransferenciaPrevista/TransferenciaPrevistaFluxoAprovacaoService.php`
- Resolver de gestores: `app/Services/CentroCusto/CentroCustoGestorResolverService.php`
- Tela: `resources/js/components/planejamento/movimentacao/SolicitacaoTransferencia.vue`
- Menu: Planejamento → Movimentação → Solicitação de Transferência
