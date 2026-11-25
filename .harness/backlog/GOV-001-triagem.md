# GOV-001 — Triagem DBA high (2026-11-18)

Fonte: `docs/database/audits/2026-11-18/findings.csv`  
Total high: **343** (342 SEC `withoutGlobalScopes` + 1 SCHEMA).

## Top acionáveis

| Prioridade | Ação | Evidência |
|------------|------|-----------|
| P0 | Manter TD-003 (helper + heurística CI) | Quase todos os high são o mesmo padrão SEC |
| P1 | Revisar `TreinamentoDuplicaddos.php` (várias ocorrências) | DBA-598…601 |
| P1 | Revisar jobs/commands de treinamento vencimento | DBA-621+ |
| P2 | SCHEMA: models duplicadas `empresa_perimetros` (`EmpresaDispositivos` vs `EmpresaPerimetro`) | DBA-372 |
| P2 | Amostragem AssinaturaDigital / Whatsapp / MFA (muitos bypasses legítimos com `empresa_id`) | DBA-610+ |

## Conclusão

Não abrir 343 tarefas. Tratar como **uma iniciativa de multi-tenant** (TD-003) + 1 correção SCHEMA (DBA-372).  
Helper criado: `App\Support\Tenant\WithoutEmpresaScope`.  
Teste heurístico: `tests/Unit/Architecture/WithoutGlobalScopesHeuristicTest.php`.
