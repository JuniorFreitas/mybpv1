# Segurança — Pentest OWASP MyBP

Relatórios de pentest gerados pelo agente e skill `pentest-owasp`.

## Estrutura

```
docs/security/
├── dashboard.html          # Última auditoria (cópia regenerada)
├── schema/findings.schema.json
└── audits/YYYY-MM-DD/
    ├── findings.json       # Fonte da verdade
    ├── findings.csv        # Planilha: causa + como resolver
    ├── dashboard.html      # Dashboard interativo
    ├── sast-raw.json       # Saída bruta SAST
    └── dast-raw.json       # Saída bruta DAST (se executado)
```

## Executar pentest

```bash
# Pentest completo (SAST + DAST se ambiente acessível)
.cursor/skills/pentest-owasp/scripts/run_pentest.sh \
  --target http://localhost \
  --output docs/security/audits/$(date +%Y-%m-%d)/

# Apenas SAST
.cursor/skills/pentest-owasp/scripts/run_sast.sh \
  --output docs/security/audits/$(date +%Y-%m-%d)/

# Regenerar HTML/CSV a partir de findings.json existente
.cursor/skills/pentest-owasp/scripts/generate_report.sh \
  docs/security/audits/YYYY-MM-DD/findings.json
```

## Abrir dashboard

```bash
open docs/security/dashboard.html
# ou
open docs/security/audits/YYYY-MM-DD/dashboard.html
```

## Severidades

| Nível | Descrição |
|-------|-----------|
| critical | Exploração imediata, impacto grave (RCE, bypass auth, exposição massiva) |
| high | Exploração provável com impacto significativo |
| medium | Requer condições ou impacto moderado |
| low | Risco residual ou hardening |
| info | Boas práticas, sem exploração direta |

## Prioridades de remediação

| Prioridade | Ação |
|------------|------|
| P1 | Corrigir imediatamente |
| P2 | Corrigir no próximo sprint |
| P3 | Planejar correção |
| P4 | Backlog / aceitar risco documentado |

## Guardrails

- **Nunca** executar pentest em produção sem autorização escrita.
- Evidências em relatórios são sanitizadas (CPF, tokens e senhas mascarados).
- Cruzar achados com [`docs/09-riscos-tecnicos.md`](../09-riscos-tecnicos.md).

## Agente e skill

- Agente: [`agents/pentest-owasp/README.md`](../../agents/pentest-owasp/README.md)
- Skill: [`.cursor/skills/pentest-owasp/SKILL.md`](../../.cursor/skills/pentest-owasp/SKILL.md)
