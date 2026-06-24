# Workflow DDL — extrair, comparar, sugerir, aplicar

## 1. Extrair (leitura)

```bash
bash .cursor/skills/dba-senior/scripts/dump_live_ddl.sh \
  --output docs/database/audits/YYYY-MM-DD/
```

Gera:
- `schema-live-ddl.sql` — mysqldump `--no-data`
- `schema-live.json` — parse INFORMATION_SCHEMA

## 2. Comparar drift

```bash
python3 .cursor/skills/dba-senior/scripts/ddl_diff.py \
  --migrations docs/database/audits/YYYY-MM-DD/migrations-raw.json \
  --live docs/database/audits/YYYY-MM-DD/schema-live.json \
  --output docs/database/audits/YYYY-MM-DD/ddl-diff.json
```

Tipos de drift:
- Tabela só no live (criada manualmente)
- Tabela só nas migrations (migrate pendente)
- Índice ausente no live
- Sem PRIMARY KEY

## 3. Sugerir DDL

Automático após merge em `ddl-suggestions/`:
- `*.sql` — SQL puro para revisão
- `migrations/*.php` — stub Laravel com `Schema::table()`

## 4. Aplicar (somente local + OK explícito)

```bash
# Revisar arquivos em ddl-suggestions/ primeiro
cp docs/database/audits/YYYY-MM-DD/ddl-suggestions/migrations/*.php database/migrations/
docker compose -f docker-compose.dev.yml exec app php artisan migrate
```

Ou via orquestrador (confirmação necessária):

```bash
.cursor/skills/dba-senior/scripts/run_dba_audit.sh --apply-ddl
```

## Guardrails

| Ação | Produção | Local |
|------|----------|-------|
| SELECT / EXPLAIN | Bloqueado | Permitido |
| mysqldump --no-data | Bloqueado | Permitido |
| CREATE INDEX / ALTER | Bloqueado | Após revisão |
| DROP TABLE | Nunca automático | Nunca sem backup |

Hosts permitidos: `localhost`, `127.0.0.1`, `mysql` (Docker).
