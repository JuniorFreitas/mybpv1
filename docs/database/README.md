# Banco de Dados — Auditoria DBA MyBP

Relatórios de auditoria DBA (performance + segurança) gerados pelo agente e skill `dba-senior`.

## Estrutura

```
docs/database/
├── dashboard.html          # Última auditoria (cópia regenerada)
├── schema/findings.schema.json
└── audits/YYYY-MM-DD/
    ├── findings.json       # Fonte da verdade
    ├── findings.csv        # Planilha: causa + como resolver
    ├── dashboard.html      # Dashboard interativo
    ├── migrations-raw.json # Schema parseado das migrations
    ├── models-raw.json     # Auditoria de models
    ├── sql-patterns-raw.json
    ├── schema-live-ddl.sql # DDL real (se live executado)
    ├── schema-live.json    # INFORMATION_SCHEMA parseado
    ├── ddl-diff.json       # Drift migrations ↔ live
    ├── schema-live-raw.json
    └── ddl-suggestions/    # SQL + stubs migration Laravel
```

## Executar auditoria

```bash
# Completa (tenta MySQL local/Docker)
.cursor/skills/dba-senior/scripts/run_dba_audit.sh \
  --output docs/database/audits/$(date +%Y-%m-%d)/

# Apenas análise estática
.cursor/skills/dba-senior/scripts/run_dba_audit.sh \
  --output docs/database/audits/$(date +%Y-%m-%d)/ --skip-live

# Regenerar HTML/CSV
python3 .cursor/skills/dba-senior/scripts/generate_report.py \
  docs/database/audits/YYYY-MM-DD/findings.json
```

## Pré-requisito live DB

```bash
docker compose -f docker-compose.dev.yml up -d mysql
docker compose -f docker-compose.dev.yml exec app php artisan migrate
```

## Abrir dashboard

```bash
open docs/database/dashboard.html
```

## Categorias de achados

| Categoria | Foco |
|-----------|------|
| PERF | Índices, N+1, tabelas grandes |
| SEC | SQL injection, withoutGlobalScopes |
| SCHEMA | Drift, unificação, PK ausente |
| QUERY | ORM vs Query Builder |
| CONV | $table, $fillable, $casts |

## Guardrails

- **Nunca** conectar em produção (hosts bloqueados fora de local/Docker).
- DDL sugerido em `ddl-suggestions/` — aplicar só após revisão humana.
- Cruzar achados SQL com pentest OWASP em `docs/security/`.

## Agente e skill

- Agente: [`agents/dba-senior/README.md`](../../agents/dba-senior/README.md)
- Skill: [`.cursor/skills/dba-senior/SKILL.md`](../../.cursor/skills/dba-senior/SKILL.md)
