#!/usr/bin/env python3
"""Gera dashboard.html e findings.csv a partir de findings.json DBA."""
from __future__ import annotations

import argparse
import csv
import json
import shutil
from pathlib import Path

TEMPLATE_NAME = "dashboard.html"
DATA_MARKER = 'id="dba-data">'


def load_report(path: Path) -> dict:
    return json.loads(path.read_text(encoding="utf-8"))


def write_csv(report: dict, out_path: Path) -> None:
    headers = [
        "id", "categoria", "severidade", "titulo", "localizacao", "fonte",
        "causa", "evidencia", "como_resolver", "prioridade", "status",
        "ddl_sugerido", "orm_recomendacao",
    ]
    with out_path.open("w", encoding="utf-8-sig", newline="") as f:
        writer = csv.writer(f, quoting=csv.QUOTE_ALL)
        writer.writerow(headers)
        for finding in report.get("findings", []):
            writer.writerow([
                finding.get("id", ""),
                finding.get("category", ""),
                finding.get("severity", ""),
                finding.get("title", ""),
                finding.get("location", ""),
                finding.get("source", ""),
                finding.get("root_cause", ""),
                finding.get("evidence", ""),
                finding.get("remediation", ""),
                finding.get("priority", ""),
                finding.get("status", ""),
                finding.get("ddl_suggestion", ""),
                finding.get("orm_recommendation", ""),
            ])


def serialize_report(report: dict) -> str:
    data_json = json.dumps(report, ensure_ascii=False)
    return data_json.replace("</", "<\\/")


def inject_dashboard(template: str, report: dict) -> str:
    data_json = serialize_report(report)
    start = template.find(DATA_MARKER)
    if start == -1:
        raise ValueError(f"Marcador {DATA_MARKER!r} não encontrado no template")
    content_start = start + len(DATA_MARKER)
    end = template.find("</script>", content_start)
    if end == -1:
        raise ValueError("Fechamento </script> do dba-data não encontrado")
    return template[:content_start] + data_json + template[end:]


def write_html(report: dict, template_path: Path, out_path: Path) -> None:
    template = template_path.read_text(encoding="utf-8")
    out_path.write_text(inject_dashboard(template, report), encoding="utf-8")


def main() -> None:
    parser = argparse.ArgumentParser(description="Generate DBA HTML dashboard and CSV")
    parser.add_argument("findings_json", type=Path)
    parser.add_argument("--template", type=Path, default=None)
    parser.add_argument("--copy-latest", action="store_true", default=True)
    args = parser.parse_args()

    script_dir = Path(__file__).resolve().parent
    skill_dir = script_dir.parent
    findings_path = args.findings_json.resolve()
    out_dir = findings_path.parent
    template = args.template or (skill_dir / "templates" / TEMPLATE_NAME)

    if not template.exists():
        raise SystemExit(f"Template not found: {template}")

    report = load_report(findings_path)
    write_csv(report, out_dir / "findings.csv")
    write_html(report, template, out_dir / "dashboard.html")

    if args.copy_latest:
        db_root = out_dir.parent.parent if out_dir.parent.name == "audits" else out_dir.parent
        shutil.copy2(out_dir / "dashboard.html", db_root / "dashboard.html")

    print(f"Generated: {out_dir / 'dashboard.html'}")
    print(f"Generated: {out_dir / 'findings.csv'}")


if __name__ == "__main__":
    main()
