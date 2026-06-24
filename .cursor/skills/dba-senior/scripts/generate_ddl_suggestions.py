#!/usr/bin/env python3
"""Gera DDL de remediação e stubs de migration Laravel a partir de findings.json."""
from __future__ import annotations

import argparse
import json
import re
from datetime import datetime
from pathlib import Path


def slugify(text: str) -> str:
    text = re.sub(r"[^a-zA-Z0-9]+", "_", text.lower()).strip("_")
    return text[:60]


def migration_stub(table: str, index_name: str, columns: list[str], ts: str, seq: int) -> str:
    cols_repr = json.dumps(columns) if len(columns) > 1 else f"'{columns[0]}'"
    if len(columns) > 1:
        index_call = f"$table->index({cols_repr}, '{index_name}');"
    else:
        index_call = f"$table->index('{columns[0]}', '{index_name}');"

    class_name = "Dba" + "".join(w.capitalize() for w in slugify(f"{table}_{index_name}").split("_"))

    return f"""<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration
{{
  public function up(): void
  {{
    Schema::table('{table}', function (Blueprint $table) {{
      {index_call}
    }});
  }}

  public function down(): void
  {{
    Schema::table('{table}', function (Blueprint $table) {{
      $table->dropIndex('{index_name}');
    }});
  }}
}};
"""


def parse_create_index(ddl: str) -> tuple[str, str, list[str]] | None:
    m = re.search(
        r"CREATE\s+INDEX\s+(\w+)\s+ON\s+(\w+)\s*\(([^)]+)\)",
        ddl,
        re.I,
    )
    if not m:
        return None
    idx, tbl, cols_raw = m.group(1), m.group(2), m.group(3)
    cols = [c.strip().strip("`") for c in cols_raw.split(",")]
    return tbl, idx, cols


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("findings_json", type=Path)
    parser.add_argument("--output-dir", type=Path, default=None)
    args = parser.parse_args()

    findings_path = args.findings_json.resolve()
    out_dir = args.output_dir or (findings_path.parent / "ddl-suggestions")
    mig_dir = out_dir / "migrations"
    out_dir.mkdir(parents=True, exist_ok=True)
    mig_dir.mkdir(parents=True, exist_ok=True)

    report = json.loads(findings_path.read_text(encoding="utf-8"))
    ts = datetime.now().strftime("%Y_%m_%d")
    seq = 0
    generated = 0

    for finding in report.get("findings", []):
        ddl = finding.get("ddl_suggestion", "")
        if not ddl or "CREATE INDEX" not in ddl.upper():
            continue
        parsed = parse_create_index(ddl)
        if not parsed:
            continue
        table, idx_name, cols = parsed
        seq += 1
        fid = finding.get("id", f"DBA-{seq:03d}").replace("-", "_").lower()
        sql_path = out_dir / f"{fid}_add_index_{slugify(table)}_{slugify(cols[0])}.sql"
        sql_path.write_text(ddl.strip() + "\n", encoding="utf-8")

        mig_name = f"{ts}_{seq:06d}_dba_{slugify(table)}_{slugify(idx_name)}.php"
        mig_path = mig_dir / mig_name
        mig_path.write_text(
            migration_stub(table, idx_name, cols, ts, seq),
            encoding="utf-8",
        )
        generated += 1

    print(f"[generate_ddl_suggestions] {generated} DDL files -> {out_dir}")


if __name__ == "__main__":
    main()
