#!/usr/bin/env python3
"""Cruzamento models Eloquent ↔ migrations."""
from __future__ import annotations

import argparse
import json
import re
from pathlib import Path


def parse_model(path: Path) -> dict:
    content = path.read_text(encoding="utf-8", errors="replace")
    rel = str(path)
    if "app/Models" in rel:
        rel = rel.split("app/Models/", 1)[-1]

    const_table = re.search(r"const\s+TABELA\s*=\s*['\"]([^'\"]+)['\"]", content)

    def extract_prop(name: str) -> str | None:
        m = re.search(rf"protected\s+\${name}\s*=\s*['\"]([^'\"]+)['\"]", content)
        if m:
            return m.group(1)
        m = re.search(rf"protected\s+\${name}\s*=\s*self::TABELA", content)
        if m and const_table:
            return const_table.group(1)
        return None

    table = extract_prop("table")
    class_m = re.search(r"class\s+(\w+)", content)
    class_name = class_m.group(1) if class_m else path.stem

    has_fillable = "protected $fillable" in content or "protected $guarded" in content
    has_casts = "protected $casts" in content or "protected function casts()" in content
    uses_soft_delete = "SoftDeletes" in content
    extends_model = "extends Model" in content or "extends Authenticatable" in content

    if not extends_model:
        return {"skip": True}

    # infer table from class name if missing
    if not table:
        inferred = re.sub(r"(?<!^)(?=[A-Z])", "_", class_name).lower() + "s"
        table = inferred

    return {
        "file": f"app/Models/{rel}",
        "class": class_name,
        "table": table,
        "has_fillable": has_fillable,
        "has_casts": has_casts,
        "uses_soft_delete": uses_soft_delete,
        "missing_table": "protected $table" not in content,
        "missing_fillable": not has_fillable,
        "missing_casts": not has_casts,
    }


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--root", type=Path, required=True)
    parser.add_argument("--migrations", type=Path, required=True, help="migrations-raw.json")
    parser.add_argument("--output", type=Path, required=True)
    args = parser.parse_args()

    migrations = json.loads(args.migrations.read_text(encoding="utf-8"))
    migration_tables = set(migrations.get("tables", {}).keys())

    models_dir = args.root / "app" / "Models"
    models: list[dict] = []
    issues: list[dict] = []
    model_tables: dict[str, list[str]] = {}
    seen_classes: set[str] = set()

    for path in sorted(models_dir.rglob("*.php")):
        if path.name == "HasActivitylogOptions.php":
            continue
        info = parse_model(path)
        if info.get("skip"):
            continue
        if info["class"] in seen_classes:
            continue
        seen_classes.add(info["class"])
        models.append(info)
        tbl = info["table"]
        model_tables.setdefault(tbl, []).append(info["class"])

        if info["missing_table"]:
            issues.append({"type": "missing_table_prop", "model": info["class"], "file": info["file"]})
        if info["missing_fillable"]:
            issues.append({"type": "missing_fillable", "model": info["class"], "file": info["file"]})
        if info["missing_casts"]:
            issues.append({"type": "missing_casts", "model": info["class"], "file": info["file"]})
        if tbl not in migration_tables:
            issues.append({"type": "no_migration", "model": info["class"], "table": tbl, "file": info["file"]})

    for tbl, classes in model_tables.items():
        unique_classes = list(dict.fromkeys(classes))
        if len(unique_classes) > 1:
            issues.append({"type": "duplicate_model_table", "table": tbl, "classes": unique_classes})

    orphan_tables = sorted(migration_tables - set(model_tables.keys()))

    result = {
        "model_count": len(models),
        "models": models,
        "issues": issues,
        "orphan_tables": orphan_tables[:100],
        "orphan_table_count": len(orphan_tables),
    }
    args.output.parent.mkdir(parents=True, exist_ok=True)
    args.output.write_text(json.dumps(result, indent=2, ensure_ascii=False), encoding="utf-8")
    print(f"[audit_models] {len(models)} models, {len(issues)} issues -> {args.output}")


if __name__ == "__main__":
    main()
