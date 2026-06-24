#!/usr/bin/env python3
"""Gera schema-live-raw.json com stats e achados de tabelas grandes."""
from __future__ import annotations

import argparse
import json
from pathlib import Path

LARGE_TABLE_ROWS = 10000
FILTER_COLUMNS = ["empresa_id", "cliente_id", "feedback_id", "deleted_at"]


def index_columns(table: dict) -> set[str]:
    cols: set[str] = set()
    for idx in table.get("indexes", []):
        for c in idx.get("columns", []):
            cols.add(c)
    return cols


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--live", type=Path, required=True)
    parser.add_argument("--output", type=Path, required=True)
    args = parser.parse_args()

    live = json.loads(args.live.read_text(encoding="utf-8"))
    tables = live.get("tables", {})

    large_without_index: list[dict] = []
    tables_without_pk: list[str] = []
    table_stats: list[dict] = []

    for name, tbl in tables.items():
        rows = tbl.get("table_rows", 0) or 0
        table_stats.append({
            "table": name,
            "rows": rows,
            "data_length": tbl.get("data_length", 0),
            "index_length": tbl.get("index_length", 0),
        })
        has_pk = any(c.get("key") == "PRI" for c in tbl.get("columns", []))
        if not has_pk:
            tables_without_pk.append(name)

        if rows < LARGE_TABLE_ROWS:
            continue
        indexed = index_columns(tbl)
        col_names = {c["name"] for c in tbl.get("columns", [])}
        for col in FILTER_COLUMNS:
            if col in col_names and col not in indexed:
                ddl = f"CREATE INDEX idx_{name}_{col} ON {name}({col});"
                large_without_index.append({
                    "table": name,
                    "column": col,
                    "table_rows": rows,
                    "remediation": ddl,
                    "ddl_suggestion": ddl,
                })

    table_stats.sort(key=lambda x: x["rows"], reverse=True)

    result = {
        "large_tables_without_index": large_without_index,
        "tables_without_pk": tables_without_pk,
        "top_tables_by_rows": table_stats[:30],
    }
    args.output.parent.mkdir(parents=True, exist_ok=True)
    args.output.write_text(json.dumps(result, indent=2, ensure_ascii=False), encoding="utf-8")
    print(f"[build_live_stats] {len(large_without_index)} perf hints -> {args.output}")


if __name__ == "__main__":
    main()
