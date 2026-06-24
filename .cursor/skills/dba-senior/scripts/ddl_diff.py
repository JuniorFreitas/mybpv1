#!/usr/bin/env python3
"""Compara migrations-raw.json com schema-live.json (drift)."""
from __future__ import annotations

import argparse
import json
from pathlib import Path

TENANT_COLUMNS = {"empresa_id", "cliente_id", "feedback_id", "deleted_at"}


def index_columns_live(table: dict) -> set[str]:
    cols: set[str] = set()
    for idx in table.get("indexes", []):
        if idx.get("name") == "PRIMARY":
            continue
        for c in idx.get("columns", []):
            cols.add(c)
    return cols


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--migrations", type=Path, required=True)
    parser.add_argument("--live", type=Path, required=True)
    parser.add_argument("--output", type=Path, required=True)
    args = parser.parse_args()

    mig = json.loads(args.migrations.read_text(encoding="utf-8"))
    live = json.loads(args.live.read_text(encoding="utf-8"))

    mig_tables = set(mig.get("tables", {}).keys())
    live_tables = set(live.get("tables", {}).keys())

    drift: list[dict] = []

    for tbl in sorted(live_tables - mig_tables):
        drift.append({
            "type": "live_only_table",
            "severity": "medium",
            "title": f"Tabela no live sem migration: {tbl}",
            "location": f"live:{tbl}",
            "root_cause": "Tabela existe no banco mas não foi detectada nas migrations",
            "evidence": f"Tabela {tbl} presente apenas no live",
            "remediation": "Criar migration de documentação ou remover tabela órfã após validação",
        })

    for tbl in sorted(mig_tables - live_tables):
        drift.append({
            "type": "migration_only_table",
            "severity": "low",
            "title": f"Migration sem tabela no live: {tbl}",
            "location": f"migrations:{tbl}",
            "root_cause": "Migration pendente ou tabela removida manualmente no live",
            "evidence": f"Tabela {tbl} nas migrations, ausente no live",
            "remediation": "Executar php artisan migrate no ambiente local",
        })

    for tbl in sorted(mig_tables & live_tables):
        live_tbl = live["tables"][tbl]
        indexed = index_columns_live(live_tbl)
        for col in TENANT_COLUMNS:
            col_names = {c["name"] for c in live_tbl.get("columns", [])}
            if col in col_names and col not in indexed:
                ddl = f"CREATE INDEX idx_{tbl}_{col} ON {tbl}({col});"
                drift.append({
                    "type": "missing_index_live",
                    "severity": "high",
                    "title": f"Índice ausente no live: {tbl}.{col}",
                    "location": f"live:{tbl}",
                    "root_cause": f"Coluna {col} sem índice no banco real",
                    "evidence": f"Tabela {tbl}, rows~{live_tbl.get('table_rows', 0)}",
                    "remediation": f"1. EXPLAIN queries que filtram {col}\n2. {ddl}",
                    "ddl_suggestion": ddl,
                })

        # PK check
        has_pk = any(c.get("key") == "PRI" for c in live_tbl.get("columns", []))
        if not has_pk:
            drift.append({
                "type": "no_primary_key",
                "severity": "high",
                "title": f"Sem PRIMARY KEY no live: {tbl}",
                "location": f"live:{tbl}",
                "root_cause": "Tabela sem chave primária no banco real",
                "evidence": tbl,
                "remediation": "Adicionar PK via migration Laravel",
            })

    result = {"drift_count": len(drift), "drift": drift}
    args.output.parent.mkdir(parents=True, exist_ok=True)
    args.output.write_text(json.dumps(result, indent=2, ensure_ascii=False), encoding="utf-8")
    print(f"[ddl_diff] {len(drift)} drift items -> {args.output}")


if __name__ == "__main__":
    main()
