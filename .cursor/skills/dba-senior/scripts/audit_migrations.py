#!/usr/bin/env python3
"""Parse estático de migrations Laravel — tabelas, colunas, índices, FKs."""
from __future__ import annotations

import argparse
import json
import re
from collections import defaultdict
from pathlib import Path

TENANT_COLUMNS = {"empresa_id", "cliente_id", "feedback_id", "user_id", "curriculo_id"}
FK_HINT_COLUMNS = TENANT_COLUMNS | {"foreign_id", "parent_id", "vaga_id", "admissoes_id"}


def parse_migration_file(path: Path) -> dict:
    content = path.read_text(encoding="utf-8", errors="replace")
    result = {
        "file": str(path.relative_to(path.parents[2]) if len(path.parents) > 2 else path.name),
        "creates": [],
        "alters": [],
        "indexes": [],
        "foreign_keys": [],
        "soft_deletes": [],
        "raw_statements": [],
    }

    for m in re.finditer(r"Schema::create\(\s*['\"]([^'\"]+)['\"]", content):
        result["creates"].append(m.group(1))

    for m in re.finditer(r"Schema::table\(\s*['\"]([^'\"]+)['\"]", content):
        result["alters"].append(m.group(1))

    for m in re.finditer(r"\$table->softDeletes\(\)", content):
        # tentar achar tabela do bloco mais próximo
        before = content[: m.start()]
        tbl = re.findall(r"Schema::(?:create|table)\(\s*['\"]([^'\"]+)['\"]", before)
        if tbl:
            result["soft_deletes"].append(tbl[-1])

    # index simples e composto
    for m in re.finditer(
        r"\$table->index\(\s*(?:\[([^\]]+)\]|'([^']+)'|\"([^\"]+)\")\s*(?:,\s*['\"]([^'\"]+)['\"])?\s*\)",
        content,
    ):
        cols_raw = m.group(1) or m.group(2) or m.group(3) or ""
        cols = [c.strip().strip("'\"") for c in re.findall(r"['\"]([^'\"]+)['\"]", cols_raw)] if m.group(1) else [cols_raw.strip("'\"")]
        idx_name = m.group(4)
        before = content[: m.start()]
        tbl = re.findall(r"Schema::(?:create|table)\(\s*['\"]([^'\"]+)['\"]", before)
        table = tbl[-1] if tbl else "unknown"
        result["indexes"].append({"table": table, "columns": cols, "name": idx_name})

    for m in re.finditer(
        r"\$table->foreign\(\s*['\"]([^'\"]+)['\"]",
        content,
    ):
        col = m.group(1)
        before = content[: m.start()]
        tbl = re.findall(r"Schema::(?:create|table)\(\s*['\"]([^'\"]+)['\"]", before)
        table = tbl[-1] if tbl else "unknown"
        result["foreign_keys"].append({"table": table, "column": col})

    for m in re.finditer(r"CREATE\s+INDEX\s+(?:IF\s+NOT\s+EXISTS\s+)?(\w+)\s+ON\s+(\w+)", content, re.I):
        result["raw_statements"].append({"type": "index", "name": m.group(1), "table": m.group(2)})

    # colunas comuns em create blocks
    for m in re.finditer(
        r"Schema::create\(\s*['\"]([^'\"]+)['\"][\s\S]*?function\s*\([^)]*\)\s*\{([\s\S]*?)\n\s*\}\);",
        content,
    ):
        table = m.group(1)
        block = m.group(2)
        cols = []
        for cm in re.finditer(r"\$table->(\w+)\(\s*['\"]([^'\"]+)['\"]", block):
            cols.append({"name": cm.group(2), "type": cm.group(1)})
        if cols:
            result.setdefault("table_columns", {})[table] = cols

    return result


def build_schema(migrations_dir: Path) -> dict:
    tables: dict[str, dict] = {}
    all_indexes: list[dict] = []
    soft_delete_tables: set[str] = set()
    files_parsed = 0

    for path in sorted(migrations_dir.glob("*.php")):
        parsed = parse_migration_file(path)
        files_parsed += 1
        for t in parsed["creates"]:
            tables.setdefault(t, {"created_in": [], "columns": [], "has_soft_delete": False})
            tables[t]["created_in"].append(parsed["file"])
        for t in parsed["alters"]:
            tables.setdefault(t, {"created_in": [], "columns": [], "has_soft_delete": False})
        for t in parsed["soft_deletes"]:
            soft_delete_tables.add(t)
            tables.setdefault(t, {"created_in": [], "columns": [], "has_soft_delete": False})
            tables[t]["has_soft_delete"] = True
        for idx in parsed["indexes"]:
            all_indexes.append(idx)
            tables.setdefault(idx["table"], {"created_in": [], "columns": [], "has_soft_delete": False})
        for fk in parsed["foreign_keys"]:
            tables.setdefault(fk["table"], {"created_in": [], "columns": [], "has_soft_delete": False})
            tables[fk["table"]].setdefault("foreign_keys", []).append(fk)
        for tbl, cols in parsed.get("table_columns", {}).items():
            tables.setdefault(tbl, {"created_in": [], "columns": [], "has_soft_delete": False})
            existing = {c["name"] for c in tables[tbl].get("columns", [])}
            for c in cols:
                if c["name"] not in existing:
                    tables[tbl].setdefault("columns", []).append(c)

    # índices por tabela
    index_by_table: dict[str, list] = defaultdict(list)
    indexed_columns: dict[str, set] = defaultdict(set)
    for idx in all_indexes:
        index_by_table[idx["table"]].append(idx)
        for col in idx["columns"]:
            indexed_columns[idx["table"]].add(col)

    # missing index on tenant/FK columns
    missing_index_hints: list[dict] = []
    for table, meta in tables.items():
        col_names = {c["name"] for c in meta.get("columns", [])}
        for col in FK_HINT_COLUMNS:
            if col in col_names and col not in indexed_columns.get(table, set()):
                missing_index_hints.append({"table": table, "column": col})

    # similar tables heuristic
    similar_groups: list[dict] = []
    table_list = list(tables.keys())
    for i, t1 in enumerate(table_list):
        for t2 in table_list[i + 1 :]:
            prefix1 = t1.split("_")[0]
            prefix2 = t2.split("_")[0]
            if prefix1 != prefix2 or len(prefix1) < 4:
                continue
            cols1 = {c["name"] for c in tables[t1].get("columns", [])}
            cols2 = {c["name"] for c in tables[t2].get("columns", [])}
            if not cols1 or not cols2:
                continue
            overlap = len(cols1 & cols2) / max(len(cols1 | cols2), 1)
            if overlap >= 0.6:
                similar_groups.append({"tables": [t1, t2], "overlap": round(overlap, 2)})

    return {
        "files_parsed": files_parsed,
        "table_count": len(tables),
        "tables": tables,
        "indexes": all_indexes,
        "soft_delete_tables": sorted(soft_delete_tables),
        "missing_index_hints": missing_index_hints,
        "similar_table_groups": similar_groups[:50],
    }


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--root", type=Path, required=True)
    parser.add_argument("--output", type=Path, required=True)
    args = parser.parse_args()

    migrations_dir = args.root / "database" / "migrations"
    schema = build_schema(migrations_dir)
    args.output.parent.mkdir(parents=True, exist_ok=True)
    args.output.write_text(json.dumps(schema, indent=2, ensure_ascii=False), encoding="utf-8")
    print(f"[audit_migrations] {schema['table_count']} tables, {len(schema['indexes'])} indexes -> {args.output}")


if __name__ == "__main__":
    main()
