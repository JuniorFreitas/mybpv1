# Heurísticas de unificação de tabelas

Sempre severidade `info` + **revisão humana obrigatória**.

## Critérios automáticos (audit_migrations.py)

- Mesmo prefixo de módulo + overlap de colunas ≥ 60%
- Ex.: `treinamento_vencimento_historicos` vs tabelas `*_historico` do mesmo domínio

## Padrões suspeitos no MyBP

| Padrão | Investigar |
|--------|------------|
| `*_historicos` + `*_auditoria` | Duplicação de log temporal |
| Pivots fora do padrão (`pivot_testemunhals`) | Renomear para `tabela_principal_*` |
| Tabelas com 1-2 colunas além de FK | Candidato a merge ou JSON column |
| Model duplicada (`Models/PontoEletronico` + `PontoEletronico`) | Consolidar código antes de schema |

## Antes de unificar

1. Mapear **models**, **jobs**, **exports CIH**, **relatórios**
2. Estimar volume de dados e downtime
3. Plano: migration de dados → período dual-write → cutover
4. Testes em SQLite `:memory:` + staging local

## Não unificar automaticamente

O agente apenas **sugere** candidatos. Decisão de negócio RH requer aprovação.
