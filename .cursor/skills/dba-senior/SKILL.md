---
name: dba-senior
description: >-
  Senior DBA audit for MyBP (Laravel + MySQL 5.7): schema/index analysis, live DDL
  dump and drift detection, SQL injection and N+1 scan, EXPLAIN guidance, Eloquent vs
  Query Builder recommendations, and dashboard.html + findings.csv with DDL suggestions.
  Use when the user asks for DBA, database performance, indexes, EXPLAIN, migrations,
  schema unification, SQL injection in queries, or /dba-senior.
---

# DBA Sênior — MyBP

Agente DBA focado em **performance e segurança** do banco. Entrega **dashboard.html**, **findings.csv** e **ddl-suggestions/**.

## Quick start

```bash
# Auditoria completa (live DB por padrão se MySQL disponível)
.cursor/skills/dba-senior/scripts/run_dba_audit.sh \
  --output docs/database/audits/$(date +%Y-%m-%d)/

# Apenas estático (sem MySQL)
.cursor/skills/dba-senior/scripts/run_dba_audit.sh \
  --output docs/database/audits/$(date +%Y-%m-%d)/ --skip-live

open docs/database/dashboard.html
```

## Workflow

```
Task Progress:
- [ ] Fase 0: Reconhecimento (docs/05-banco-de-dados.md, AGENTS.md §5/§10)
- [ ] Fase 1: Estático (migrations, models, SQL patterns)
- [ ] Fase 2: Live + DDL (dump, drift, INFORMATION_SCHEMA)
- [ ] Fase 3: Queries (EXPLAIN candidatos; ORM vs QB)
- [ ] Fase 4: Relatório + ddl-suggestions/
- [ ] Fase 5: Resumir P1/P2 para o usuário
```

### Fase 0 — Preparação

1. Ler [references/mybp-schema-conventions.md](references/mybp-schema-conventions.md) e `docs/05-banco-de-dados.md`.
2. Confirmar ambiente: `docker compose -f docker-compose.dev.yml up -d mysql` (dev).
3. Criar `docs/database/audits/YYYY-MM-DD/`.

### Fase 1 — Auditoria estática

Executado por `run_dba_audit.sh`:
- `audit_migrations.py` → `migrations-raw.json`
- `audit_models.py` → `models-raw.json`
- `scan_sql_patterns.py` → `sql-patterns-raw.json`

### Fase 2 — Conexão live + DDL

**Obrigatório** quando MySQL local disponível. Ver [references/ddl-workflow.md](references/ddl-workflow.md).

Ordem de conexão:
1. Docker `mysql` via `docker-compose.dev.yml`
2. Host `127.0.0.1:3306` com credenciais `.env`
3. `--skip-live` apenas se indisponível

Artefatos:
- `schema-live-ddl.sql` — mysqldump `--no-data`
- `schema-live.json` — INFORMATION_SCHEMA
- `ddl-diff.json` — drift migrations ↔ live
- `schema-live-raw.json` — tabelas grandes, PK ausente

### Fase 3 — Análise de queries

Após scripts, revisar manualmente:
- [references/eloquent-vs-query-builder.md](references/eloquent-vs-query-builder.md)
- Achados `RAW_SQL`, `SQL_CONCAT`, `N_PLUS_ONE_HINT`
- Rodar `EXPLAIN` nas queries críticas (somente local)

### Fase 4 — Relatório

```bash
python3 .cursor/skills/dba-senior/scripts/generate_report.py \
  docs/database/audits/YYYY-MM-DD/findings.json
```

Entregáveis:
- `dashboard.html` — heatmap categoria × severidade
- `findings.csv` — colunas **causa** e **como_resolver**
- `ddl-suggestions/*.sql` + `ddl-suggestions/migrations/*.php`
- Cópia em `docs/database/dashboard.html`

## Formato de achado

```json
{
  "id": "DBA-001",
  "category": "PERF",
  "severity": "high",
  "title": "Índice ausente em feedback_curriculos.empresa_id",
  "location": "live:feedback_curriculos",
  "source": "ddl_diff",
  "status": "open",
  "priority": "P2",
  "root_cause": "Filtro multi-tenant sem índice no banco real",
  "evidence": "table_rows ~50000",
  "remediation": "CREATE INDEX ...",
  "ddl_suggestion": "CREATE INDEX idx_feedback_empresa ON feedback_curriculos(empresa_id);",
  "orm_recommendation": "Manter Eloquent com scope empresa_id",
  "explain_recommended": true
}
```

Categorias: `PERF`, `SEC`, `SCHEMA`, `QUERY`, `CONV`.

## Guardrails

- **Produção bloqueada** — hosts permitidos: `localhost`, `127.0.0.1`, `mysql`
- **PII**: mascarar CPF, salários, senhas (`***`)
- **Análise = leitura**: SELECT, SHOW, EXPLAIN, mysqldump `--no-data`
- **DDL aplicado** nunca automático; `--apply-ddl` só local + confirmação explícita
- PHPUnit: SQLite `:memory:` — não usar base real em testes

## Referências

- [mybp-schema-conventions.md](references/mybp-schema-conventions.md)
- [eloquent-vs-query-builder.md](references/eloquent-vs-query-builder.md)
- [index-guidelines-mysql57.md](references/index-guidelines-mysql57.md)
- [unification-heuristics.md](references/unification-heuristics.md)
- [ddl-workflow.md](references/ddl-workflow.md)
- Agente: [agents/dba-senior/README.md](../../../agents/dba-senior/README.md)

## Scripts

| Script | Uso |
|--------|-----|
| `run_dba_audit.sh` | Orquestra tudo |
| `dump_live_ddl.sh` | DDL live + schema-live.json |
| `ddl_diff.py` | Drift migrations ↔ live |
| `generate_ddl_suggestions.py` | SQL + stubs migration |
| `generate_report.py` | JSON → HTML + CSV |
