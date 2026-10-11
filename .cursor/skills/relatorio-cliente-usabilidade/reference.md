# Referência — Relatório Cliente Usabilidade

## Interpretação

| Sinal | Leitura típica |
|---|---|
| Muitas ações + 0–1 login humano | Importação / job / conta Empresa |
| 304 Funcionario com `sistema@mybp.com.br` | Contas geradas na carga, não usabilidade |
| Admissões com `created_at` concentrado em minutos | Carga em lote |
| `data_admissao` histórica ≠ `created_at` recente | Base legada importada |

## Exemplos de pedido do usuário

- "relatório de usabilidade do cliente 103967"
- "acessos de 90 dias + admissão desse cliente_id"
- "uso dos usuários da Engeko"
- `/relatorio-cliente-usabilidade 57861 --dias=30`

## Opções do comando

```
php artisan mybp:relatorio-cliente-usabilidade {cliente_id} {--dias=90} {--output=}
```

- `cliente_id` obrigatório (`clientes.id` / `users.empresa_id`)
- `--dias` entre 1 e 3650
- `--output` diretório absoluto/relativo (default `storage/app/relatorios`)

## Segurança

- HTML **sem CPF**
- Não colar trechos de CSV com documento completo no chat sem necessidade
- Não commitiar CSVs/HTML gerados com dados reais de cliente
