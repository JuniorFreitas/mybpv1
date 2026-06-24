#!/usr/bin/env python3
"""Consolida achados DBA em findings.json."""
from __future__ import annotations

import argparse
import json
import re
from datetime import datetime, timezone
from pathlib import Path

PRIORITY_MAP = {
    "critical": "P1",
    "high": "P2",
    "medium": "P3",
    "low": "P4",
    "info": "P4",
}

SEVERITY_ORDER = {"critical": 0, "high": 1, "medium": 2, "low": 3, "info": 4}


def load_json(path: Path) -> dict | list:
    if not path.exists():
        return {}
    return json.loads(path.read_text(encoding="utf-8"))


def sanitize(text: str) -> str:
    text = re.sub(r"\b\d{3}\.\d{3}\.\d{3}-\d{2}\b", "***.***.***-**", text)
    text = re.sub(r"\b\d{11}\b", "***********", text)
    return text[:2000]


def next_id(counter: list[int]) -> str:
    counter[0] += 1
    return f"DBA-{counter[0]:03d}"


def from_migrations(data: dict, counter: list[int]) -> list[dict]:
    findings = []
    for hint in data.get("missing_index_hints", []):
        tbl, col = hint["table"], hint["column"]
        table_meta = data.get("tables", {}).get(tbl, {})
        cols = table_meta.get("columns", [])
        if cols and not any(c.get("name") == col for c in cols):
            continue
        if tbl == "unknown":
            continue
        ddl = f"CREATE INDEX idx_{tbl}_{col} ON {tbl}({col});"
        findings.append({
            "id": next_id(counter),
            "category": "PERF",
            "severity": "medium",
            "title": f"Índice ausente em {tbl}.{col}",
            "location": f"database/migrations (tabela {tbl})",
            "source": "migrations",
            "status": "open",
            "priority": "P3",
            "root_cause": f"Coluna {col} usada em filtros/FK sem índice detectado nas migrations",
            "evidence": sanitize(f"Tabela {tbl}, coluna {col}"),
            "remediation": f"1. Validar com EXPLAIN\n2. Criar migration:\n{ddl}",
            "ddl_suggestion": ddl,
            "explain_recommended": True,
        })

    for group in data.get("similar_table_groups", [])[:15]:
        t1, t2 = group["tables"]
        findings.append({
            "id": next_id(counter),
            "category": "SCHEMA",
            "severity": "info",
            "title": f"Candidato a unificação: {t1} ↔ {t2}",
            "location": "database/migrations",
            "source": "migrations",
            "status": "open",
            "priority": "P4",
            "root_cause": f"Overlap de colunas {group['overlap']*100:.0f}% — possível redundância estrutural",
            "evidence": sanitize(f"Tabelas: {t1}, {t2}"),
            "remediation": (
                "1. Revisar manualmente domínio RH\n"
                "2. Mapear models, jobs e exports CIH afetados\n"
                "3. Só unificar com plano de migração de dados"
            ),
        })
    return findings


def from_models(data: dict, counter: list[int]) -> list[dict]:
    findings = []
    type_map = {
        "missing_table_prop": ("CONV", "medium", "Model sem protected $table explícito"),
        "missing_fillable": ("CONV", "low", "Model sem $fillable/$guarded"),
        "missing_casts": ("CONV", "low", "Model sem $casts"),
        "no_migration": ("SCHEMA", "medium", "Model sem migration correspondente"),
        "duplicate_model_table": ("SCHEMA", "high", "Múltiplas models apontando para mesma tabela"),
    }
    for issue in data.get("issues", []):
        itype = issue["type"]
        cat, sev, title_prefix = type_map.get(itype, ("CONV", "low", itype))
        loc = issue.get("file", issue.get("table", ""))
        findings.append({
            "id": next_id(counter),
            "category": cat,
            "severity": sev,
            "title": f"{title_prefix}: {issue.get('model', issue.get('table', ''))}",
            "location": loc,
            "source": "models",
            "status": "open",
            "priority": PRIORITY_MAP[sev],
            "root_cause": f"Issue tipo {itype} detectado na auditoria de models",
            "evidence": sanitize(json.dumps(issue, ensure_ascii=False)),
            "remediation": "Corrigir model conforme AGENTS.md §10 ($table, $fillable, $casts)",
        })
    return findings


def from_sql_scan(data: dict, counter: list[int]) -> list[dict]:
    findings = []
    for hit in data.get("findings", []):
        sev = hit.get("severity", "medium")
        findings.append({
            "id": next_id(counter),
            "category": hit.get("category", "SEC"),
            "severity": sev,
            "title": hit.get("title", hit.get("pattern_id", "SQL")),
            "location": hit.get("location", ""),
            "source": "sql_scan",
            "status": "open",
            "priority": PRIORITY_MAP.get(sev, "P3"),
            "root_cause": hit.get("root_cause", ""),
            "evidence": sanitize(hit.get("evidence", "")),
            "remediation": hit.get("remediation", ""),
            "orm_recommendation": hit.get("orm_recommendation", ""),
            "explain_recommended": hit.get("explain_recommended", False),
        })
    return findings


def from_ddl_diff(data: dict, counter: list[int]) -> list[dict]:
    findings = []
    for item in data.get("drift", []):
        sev = item.get("severity", "medium")
        findings.append({
            "id": next_id(counter),
            "category": "SCHEMA",
            "severity": sev,
            "title": item.get("title", "Drift schema"),
            "location": item.get("location", "live vs migrations"),
            "source": "ddl_diff",
            "status": "open",
            "priority": PRIORITY_MAP.get(sev, "P3"),
            "root_cause": item.get("root_cause", ""),
            "evidence": sanitize(item.get("evidence", "")),
            "remediation": item.get("remediation", ""),
            "ddl_suggestion": item.get("ddl_suggestion", ""),
        })
    return findings


def from_live_stats(data: dict, counter: list[int]) -> list[dict]:
    findings = []
    for item in data.get("large_tables_without_index", []):
        findings.append({
            "id": next_id(counter),
            "category": "PERF",
            "severity": "high",
            "title": f"Tabela grande sem índice em {item['column']}: {item['table']}",
            "location": f"live:{item['table']}",
            "source": "live",
            "status": "open",
            "priority": "P2",
            "root_cause": f"~{item.get('table_rows', '?')} linhas; filtro em {item['column']} sem índice no live",
            "evidence": sanitize(json.dumps(item, ensure_ascii=False)),
            "remediation": item.get("remediation", f"CREATE INDEX ON {item['table']}({item['column']})"),
            "ddl_suggestion": item.get("ddl_suggestion", ""),
            "explain_recommended": True,
        })
    for item in data.get("tables_without_pk", []):
        findings.append({
            "id": next_id(counter),
            "category": "SCHEMA",
            "severity": "high",
            "title": f"Tabela sem PRIMARY KEY: {item}",
            "location": f"live:{item}",
            "source": "live",
            "status": "open",
            "priority": "P2",
            "root_cause": "Tabela sem chave primária no banco live",
            "evidence": sanitize(item),
            "remediation": "Adicionar PK via migration; revisar integridade dos dados",
        })
    return findings


def summarize(findings: list[dict]) -> dict:
    summary = {"critical": 0, "high": 0, "medium": 0, "low": 0, "info": 0, "by_category": {}}
    for f in findings:
        sev = f.get("severity", "info")
        if sev in summary:
            summary[sev] += 1
        cat = f.get("category", "CONV")
        summary["by_category"][cat] = summary["by_category"].get(cat, 0) + 1
    return summary


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--output", type=Path, required=True)
    parser.add_argument("--root", type=Path, required=True)
    parser.add_argument("--migrations", type=Path, default=None)
    parser.add_argument("--models", type=Path, default=None)
    parser.add_argument("--sql", type=Path, default=None)
    parser.add_argument("--ddl-diff", type=Path, default=None)
    parser.add_argument("--live-stats", type=Path, default=None)
    parser.add_argument("--live", action="store_true")
    parser.add_argument("--live-skipped-reason", type=str, default=None)
    args = parser.parse_args()

    out_dir = args.output
    counter = [0]
    all_findings: list[dict] = []

    mig_path = args.migrations or out_dir / "migrations-raw.json"
    mod_path = args.models or out_dir / "models-raw.json"
    sql_path = args.sql or out_dir / "sql-patterns-raw.json"
    diff_path = args.ddl_diff or out_dir / "ddl-diff.json"
    live_path = args.live_stats or out_dir / "schema-live-raw.json"

    all_findings.extend(from_migrations(load_json(mig_path), counter))
    all_findings.extend(from_models(load_json(mod_path), counter))
    all_findings.extend(from_sql_scan(load_json(sql_path), counter))
    if diff_path.exists():
        all_findings.extend(from_ddl_diff(load_json(diff_path), counter))
    if live_path.exists():
        all_findings.extend(from_live_stats(load_json(live_path), counter))

    all_findings.sort(key=lambda f: (SEVERITY_ORDER.get(f.get("severity", "info"), 9), f.get("id", "")))

    audit_id = out_dir.name if re.match(r"\d{4}-\d{2}-\d{2}", out_dir.name) else datetime.now(timezone.utc).strftime("%Y-%m-%d")
    report = {
        "audit_id": audit_id,
        "target": "local",
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "scope": {
            "static": True,
            "live": args.live,
            "live_skipped_reason": None if args.live else args.live_skipped_reason,
            "ddl_dump": args.live and (out_dir / "schema-live-ddl.sql").exists(),
            "explain": False,
        },
        "summary": summarize(all_findings),
        "findings": all_findings,
    }

    out_path = out_dir / "findings.json"
    out_path.write_text(json.dumps(report, indent=2, ensure_ascii=False), encoding="utf-8")
    print(f"[merge_dba_findings] {len(all_findings)} findings -> {out_path}")


if __name__ == "__main__":
    main()
