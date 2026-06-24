#!/usr/bin/env python3
"""Varredura de padrões SQL, injection risk e hints de N+1 no app/."""
from __future__ import annotations

import argparse
import json
import re
import subprocess
from pathlib import Path

SCANS = [
    {
        "pattern_id": "RAW_SQL",
        "category": "SEC",
        "severity": "medium",
        "title": "Query SQL raw (DB::raw/whereRaw/selectRaw/DB::select)",
        "root_cause": "SQL raw exige bindings rigorosos; risco de injection se concatenar input",
        "remediation": "Usar query builder com bindings (?); preferir Eloquent parametrizado",
        "pattern": r"(DB::raw|whereRaw|selectRaw|orderByRaw|havingRaw|DB::select)",
        "orm_recommendation": "Avaliar se Eloquent/Query Builder substitui o raw com bindings",
    },
    {
        "pattern_id": "SQL_CONCAT",
        "category": "SEC",
        "severity": "high",
        "title": "Possível concatenação de variável em SQL raw",
        "root_cause": "Interpolação de variável em string SQL sem binding",
        "remediation": "Substituir por placeholders (?); nunca concatenar input do usuário",
        "pattern": r"(whereRaw|selectRaw|DB::select|DB::statement)\s*\([^)]*(\{\$|\.\s*\$)",
        "orm_recommendation": "Query Builder com where() e bindings",
    },
    {
        "pattern_id": "DB_STATEMENT_VAR",
        "category": "SEC",
        "severity": "critical",
        "title": "DB::statement com interpolação de variável",
        "root_cause": "DDL/DML dinâmico com variável interpolada",
        "remediation": "Usar bindings ou whitelist de identificadores; nunca interpolar input",
        "pattern": r"DB::statement\s*\(\s*[\"'].*\{\$",
        "orm_recommendation": "Schema builder ou prepared statements",
    },
    {
        "pattern_id": "WITHOUT_GLOBAL_SCOPES",
        "category": "SEC",
        "severity": "high",
        "title": "withoutGlobalScopes() remove isolamento multi-tenant",
        "root_cause": "Global scope de empresa removido sem filtro manual",
        "remediation": "Garantir where empresa_id; documentar exceção; teste de isolamento",
        "pattern": r"withoutGlobalScopes",
        "orm_recommendation": "Manter scope ou filtro explícito empresa_id",
    },
    {
        "pattern_id": "MISSING_SOFT_DELETE_QB",
        "category": "CONV",
        "severity": "medium",
        "title": "DB::table() sem whereNull deleted_at visível",
        "root_cause": "Query Builder pode ignorar soft delete se não filtrar deleted_at",
        "remediation": "Adicionar whereNull('deleted_at') em joins e DB::table",
        "pattern": r"DB::table\s*\(",
        "orm_recommendation": "Preferir Eloquent com SoftDeletes ou QB com whereNull",
    },
    {
        "pattern_id": "N_PLUS_ONE_HINT",
        "category": "PERF",
        "severity": "medium",
        "title": "Loop com possível N+1 (query dentro de foreach)",
        "root_cause": "Consulta repetida dentro de loop sem eager loading",
        "remediation": "Usar with() antes do loop; ou carregar coleção única",
        "pattern": r"foreach\s*\([^)]+\)\s*\{[^}]{0,200}(::find|::where|->get\(|->first\()",
        "orm_recommendation": "Eloquent with() ou whereIn batch",
        "explain_recommended": True,
    },
    {
        "pattern_id": "ORM_IN_HEAVY_REPORT",
        "category": "QUERY",
        "severity": "low",
        "title": "Controller com múltiplos DB::raw (candidato a ExportQueryBuilder)",
        "root_cause": "Relatório pesado com SQL espalhado no controller",
        "remediation": "Extrair para *ExportQueryBuilder em Services/ com Query Builder",
        "pattern": r"DB::raw",
        "orm_recommendation": "Query Builder dedicado em Service",
        "min_matches": 5,
    },
]


def rg_search(root: Path, pattern: str, glob: str) -> list[dict]:
    cmd = [
        "rg", "--json", "-n", pattern, str(root / "app"),
        "--glob", glob, "--glob", "!vendor/**",
    ]
    try:
        proc = subprocess.run(cmd, capture_output=True, text=True, timeout=120)
    except (subprocess.TimeoutExpired, FileNotFoundError):
        return []

    hits: list[dict] = []
    for line in proc.stdout.splitlines():
        try:
            obj = json.loads(line)
        except json.JSONDecodeError:
            continue
        if obj.get("type") != "match":
            continue
        data = obj.get("data", {})
        path = data.get("path", {}).get("text", "")
        line_no = data.get("line_number", 0)
        text = data.get("lines", {}).get("text", "").strip()
        hits.append({"file": path, "line": line_no, "snippet": text[:300]})
    return hits


def count_raw_per_file(root: Path) -> dict[str, int]:
    counts: dict[str, int] = {}
    for hit in rg_search(root, r"DB::raw", "*.php"):
        f = hit["file"]
        counts[f] = counts.get(f, 0) + 1
    return counts


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--root", type=Path, required=True)
    parser.add_argument("--output", type=Path, required=True)
    args = parser.parse_args()

    findings: list[dict] = []
    raw_counts = count_raw_per_file(args.root)

    for scan in SCANS:
        if scan["pattern_id"] == "ORM_IN_HEAVY_REPORT":
            for file_path, count in raw_counts.items():
                if count >= scan.get("min_matches", 5) and "/Controllers/" in file_path:
                    findings.append({
                        "pattern_id": scan["pattern_id"],
                        "category": scan["category"],
                        "severity": scan["severity"],
                        "title": scan["title"],
                        "location": f"{file_path} ({count}x DB::raw)",
                        "root_cause": scan["root_cause"],
                        "remediation": scan["remediation"],
                        "orm_recommendation": scan.get("orm_recommendation", ""),
                        "evidence": f"{count} ocorrências de DB::raw",
                        "explain_recommended": scan.get("explain_recommended", False),
                    })
            continue

        hits = rg_search(args.root, scan["pattern"], "**/*.php")
        seen: set[tuple[str, int]] = set()
        for hit in hits:
            key = (hit["file"], hit["line"])
            if key in seen:
                continue
            seen.add(key)
            findings.append({
                "pattern_id": scan["pattern_id"],
                "category": scan["category"],
                "severity": scan["severity"],
                "title": scan["title"],
                "location": f"{hit['file']}:{hit['line']}",
                "root_cause": scan["root_cause"],
                "remediation": scan["remediation"],
                "orm_recommendation": scan.get("orm_recommendation", ""),
                "evidence": hit["snippet"],
                "explain_recommended": scan.get("explain_recommended", False),
            })

    result = {"scan_count": len(findings), "findings": findings}
    args.output.parent.mkdir(parents=True, exist_ok=True)
    args.output.write_text(json.dumps(result, indent=2, ensure_ascii=False), encoding="utf-8")
    print(f"[scan_sql_patterns] {len(findings)} hits -> {args.output}")


if __name__ == "__main__":
    main()
