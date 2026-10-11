---
name: relatorio-cliente-usabilidade
description: >-
  Gera relatórios MyBP por cliente_id (empresa_id): usabilidade (acessos,
  activity_log, admissão) e recrutamento (currículos, vagas, admitidos/demitidos
  por Matriz/Filial) nos últimos N dias, com CSVs + HTML. Use when the user asks
  for relatório de usabilidade, recrutamento, currículos, vagas abertas,
  admitidos/demitidos, acessos do cliente, ou /relatorio-cliente-usabilidade.
---

# Relatório Cliente · Usabilidade + Recrutamento

Dois comandos padrão MyBP por `cliente_id` (= `empresa_id` / `clientes.id`), janela default **90** dias.

## Quick start

```bash
# Usabilidade (acessos + uso + admissão)
.cursor/skills/relatorio-cliente-usabilidade/scripts/run.sh 63122
docker compose exec -T mybpdp php artisan mybp:relatorio-cliente-usabilidade 63122 --dias=90

# Recrutamento (currículos, vagas, status, demitidos · Matriz/Filial)
.cursor/skills/relatorio-cliente-usabilidade/scripts/run-recrutamento.sh 63122
docker compose exec -T mybpdp php artisan mybp:relatorio-cliente-recrutamento 63122 --dias=90
```

### Usabilidade — saída

| Arquivo | Conteúdo |
|---|---|
| `*_usuarios_uso_*.csv` | Users ativos + login/ações |
| `*_admissoes_*.csv` | Admissões criadas no período |
| `*_activity_resumo_*.csv` | activity_log agregado |
| `*_admissoes_cc_filial_*.csv` | Quebra CC × filial |
| `*_demitidos_*.csv` | DEMITIDO no período |
| `*_relatorio_*.html` | HTML + gráficos |

### Recrutamento — saída

| Arquivo | Conteúdo |
|---|---|
| `*_recrutamento_*.html` | HTML + gráficos |
| `*_status_matriz_filial.csv` | Status · Matriz/Filial |
| `*_vagas.csv` | Vagas abertas criadas no período |

## Workflow do agente

```
Task Progress:
- [ ] Confirmar cliente_id e tipo (usabilidade vs recrutamento)
- [ ] Rodar o script correspondente
- [ ] Citar HTML e 4–6 métricas-chave
```

1. **Não inventar números** — sempre gerar via comando.
2. **Não logar CPF/senha**; CPF só nos CSVs.
3. Filial no HTML só se existir (além de Matriz).

## Regras de dados

### Usabilidade
- Users: `ativo=1` + `deleted_at` null
- Acesso: `ultimo_acesso` · Uso: `activity_log` causer User
- Admissão: `feedback_curriculos.empresa_id` · soft delete · CC/filial
- DEMITIDO período: datas de desmob ou `updated_at` se vazias

### Recrutamento
- Currículos: activity_log `curriculo` created
- Candidaturas: Feedback created
- Vagas criadas: `vagas_abertas.created_at`
- Preenchidas: ADMITIDO + `data_admissao` no período
- Status snapshot: Matriz (`filial!=1`) / Filial (`filial=1`)

## Código

- Usabilidade: `RelatorioClienteUsabilidadeCommand` + `ClienteUsabilidadeRelatorioService` + `cliente_usabilidade.blade.php`
- Recrutamento: `RelatorioClienteRecrutamentoCommand` + `ClienteRecrutamentoRelatorioService` + `cliente_recrutamento.blade.php`

## Referência

[reference.md](reference.md)
