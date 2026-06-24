#!/usr/bin/env python3
"""Coleta schema live via mysql CLI e gera schema-live.json."""
from __future__ import annotations

import argparse
import json
import os
import subprocess
from collections import defaultdict
from pathlib import Path


def load_env(root: Path) -> dict:
    env = {}
    env_file = root / ".env"
    if env_file.exists():
        for line in env_file.read_text(encoding="utf-8").splitlines():
            line = line.strip()
            if line.startswith("DB_") and "=" in line and not line.startswith("#"):
                k, v = line.split("=", 1)
                env[k] = v.strip().strip('"').strip("'")
    return env


def mysql_query(root: Path, database: str, sql: str, mode: str) -> str:
    env = load_env(root)
    user = env.get("DB_USERNAME", "root")
    password = env.get("DB_PASSWORD", "")
    host = env.get("DB_HOST", "127.0.0.1")
    port = env.get("DB_PORT", "3306")

    if mode == "compose":
        cmd = [
            "docker", "compose", "-f", str(root / "docker-compose.dev.yml"),
            "exec", "-T", "mysql", "mysql",
            f"-u{user}",
        ]
        if password:
            cmd.append(f"-p{password}")
        cmd.extend([database, "-N", "-e", sql])
    elif mode == "docker_run":
        cmd = [
            "docker", "run", "--rm", "-i", "mysql:5.7", "mysql",
            "-hhost.docker.internal", f"-P{port}", f"-u{user}",
        ]
        if password:
            cmd.append(f"-p{password}")
        cmd.extend([database, "-N", "-e", sql])
    else:
        cmd = ["mysql", f"-h{host}", f"-P{port}", f"-u{user}"]
        if password:
            cmd.append(f"-p{password}")
        cmd.extend([database, "-N", "-e", sql])

    proc = subprocess.run(cmd, capture_output=True, text=True, cwd=str(root))
    if proc.returncode != 0:
        raise RuntimeError(proc.stderr or proc.stdout or "mysql failed")
    return proc.stdout


def collect(root: Path, database: str, mode: str) -> dict:
    tables_sql = """
    SELECT table_name, engine, table_rows, data_length, index_length
    FROM information_schema.tables
    WHERE table_schema = DATABASE() AND table_type = 'BASE TABLE'
    ORDER BY table_name
    """
    cols_sql = """
    SELECT table_name, column_name, column_type, is_nullable, column_key, extra
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
    ORDER BY table_name, ordinal_position
    """
    idx_sql = """
    SELECT table_name, index_name, GROUP_CONCAT(column_name ORDER BY seq_in_index) AS cols, non_unique
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
    GROUP BY table_name, index_name, non_unique
    """
    fk_sql = """
    SELECT table_name, column_name, referenced_table_name, referenced_column_name, constraint_name
    FROM information_schema.key_column_usage
    WHERE table_schema = DATABASE() AND referenced_table_name IS NOT NULL
    """

    tables: dict[str, dict] = {}
    for line in mysql_query(root, database, tables_sql, mode).strip().splitlines():
        if not line.strip():
            continue
        parts = line.split("\t")
        if len(parts) < 5:
            continue
        name, engine, rows, data_len, idx_len = parts[:5]
        tables[name] = {
            "engine": engine,
            "table_rows": int(rows or 0),
            "data_length": int(data_len or 0),
            "index_length": int(idx_len or 0),
            "columns": [],
            "indexes": [],
            "foreign_keys": [],
        }

    for line in mysql_query(root, database, cols_sql, mode).strip().splitlines():
        if not line.strip():
            continue
        parts = line.split("\t")
        if len(parts) < 6:
            continue
        tbl, col, ctype, nullable, col_key, extra = parts[:6]
        tables.setdefault(tbl, {"columns": [], "indexes": [], "foreign_keys": []})
        tables[tbl].setdefault("columns", []).append({
            "name": col, "type": ctype, "nullable": nullable == "YES",
            "key": col_key, "extra": extra,
        })

    for line in mysql_query(root, database, idx_sql, mode).strip().splitlines():
        if not line.strip():
            continue
        parts = line.split("\t")
        if len(parts) < 4:
            continue
        tbl, idx_name, cols, non_unique = parts[:4]
        tables.setdefault(tbl, {"columns": [], "indexes": [], "foreign_keys": []})
        tables[tbl].setdefault("indexes", []).append({
            "name": idx_name,
            "columns": cols.split(",") if cols else [],
            "non_unique": non_unique == "1",
        })

    for line in mysql_query(root, database, fk_sql, mode).strip().splitlines():
        if not line.strip():
            continue
        parts = line.split("\t")
        if len(parts) < 5:
            continue
        tbl, col, ref_tbl, ref_col, cname = parts[:5]
        tables.setdefault(tbl, {"columns": [], "indexes": [], "foreign_keys": []})
        tables[tbl].setdefault("foreign_keys", []).append({
            "column": col, "references": f"{ref_tbl}.{ref_col}", "name": cname,
        })

    return {"database": database, "table_count": len(tables), "tables": tables}


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--root", type=Path, required=True)
    parser.add_argument("--database", required=True)
    parser.add_argument("--output", type=Path, required=True)
    parser.add_argument("--mode", type=str, default="host")
    args = parser.parse_args()

    schema = collect(args.root, args.database, args.mode)
    args.output.parent.mkdir(parents=True, exist_ok=True)
    args.output.write_text(json.dumps(schema, indent=2, ensure_ascii=False), encoding="utf-8")
    print(f"[collect_live_schema] {schema['table_count']} tables -> {args.output}")


if __name__ == "__main__":
    main()
