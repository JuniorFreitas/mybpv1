# Schedule Avaliação de Experiência – Configuração por empresa

O envio automático de e-mails de vencimento de **Avaliação de Experiência** (schedule diário) pode ser **habilitado ou desabilitado por empresa** sem necessidade de deploy.

## Como funciona

- O **comando** `php artisan mybp:avaliacao-experiencia` roda **toda segunda-feira às 00:00** (agendado no `Kernel`). Quando executado sem `--empresa_id`, usa a lista de empresas retornada por **`Sistema::listaEmpresasParaScheduleAvaliacaoExperiencia()`**.
- Essa lista considera a configuração em **`cliente_configs.schedule_avaliacao_experiencia`**:
  - **`true`** ou **sem registro** para o cliente: empresa **incluída** no schedule (recebe o processamento).
  - **`false`**: empresa **excluída** do schedule (não processa nem envia e-mail para essa empresa).

## Habilitar / desabilitar (sem deploy)

### 1. Desabilitar o schedule para uma empresa

Desligar o envio automático de Avaliação de Experiência para um cliente (empresa):

```sql
-- Se já existe linha para o cliente:
UPDATE cliente_configs
SET schedule_avaliacao_experiencia = 0
WHERE cliente_id = :cliente_id;
```

Se o cliente **ainda não tiver** registro em `cliente_configs`, use o Tinker (abaixo) ou crie o registro com `schedule_avaliacao_experiencia = 0`.

Pelo **Tinker** (funciona com ou sem registro prévio):

```php
$clienteId = 12345; // ID da empresa (clientes.id = users.empresa_id)
$config = \App\Models\ClienteConfig::firstOrCreate(
    ['cliente_id' => $clienteId],
    ['schedule_avaliacao_experiencia' => true]
);
$config->schedule_avaliacao_experiencia = false;
$config->save();
```

### 2. Habilitar o schedule para uma empresa

Voltar a processar uma empresa no schedule:

```sql
UPDATE cliente_configs
SET schedule_avaliacao_experiencia = 1
WHERE cliente_id = :cliente_id;
```

Ou remover a restrição (volta a usar o padrão “habilitado”):

```sql
UPDATE cliente_configs
SET schedule_avaliacao_experiencia = 1
WHERE cliente_id = :cliente_id;
```

### 3. Migração

Rodar a migration que adiciona a coluna (uma vez):

```bash
php artisan migrate
```

Arquivo: `database/migrations/2026_02_22_000001_add_schedule_avaliacao_experiencia_to_cliente_configs_table.php`

## Observações

- **Comando manual** `php artisan mybp:avaliacao-experiencia --empresa_id=X` **não** é afetado por essa configuração: ele sempre processa a empresa informada.
- A configuração afeta apenas o **schedule** (Job diário que percorre as empresas).
- **Padrão**: empresas sem registro em `cliente_configs` ou com `schedule_avaliacao_experiencia = 1` continuam sendo processadas.
