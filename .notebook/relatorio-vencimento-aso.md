# Relatório vencimento ASO — regras e divergências

Last checked: 2026-09-26 | sample: Montisol `empresa_id=63122` (`vencimento_aso=2` → 60 dias)

## Regra única (tela + Excel + e-mail)

Fonte: `AsoVencimentoRelatorioService`

1. Sem demissão + `Admissao.status` in `ADMITIDO` | `PRONTO PARA ADMISSÃO`
2. `UltimoAso` em `examesesmts` (atual, realizado, aprovado)
3. Config `ClienteConfig.vencimento_aso` define destaque/alerta (`dias_vencer <= N`)
4. E-mail: mesma população; janela `2000-01-01` → hoje+N; só quem `dias_vencer <= N`

## Canais

| Canal | Status |
|--------|--------|
| Tela | Service |
| Excel (`exportExcel` + JobExportaExcel) | Service |
| Excel client (Vue) | Mesmos dados da tela |
| `mybp:vencimentoAso` | Marca `vencido` + dispara e-mail (service) |

## Bugs corrigidos

- `campoTipoExame: ""` não zera mais a lista (Montisol: 0 → **1385**)
- `exportExcel` implementado no controller
- E-mail alinhado a `examesesmts` (não mais `admissao_asos`)
- Situação na tela: “Vencido há X dias” / “Vence em X dias”

## Arquivos

- `app/Services/Relatorios/AsoVencimentoRelatorioService.php`
- `app/Http/Controllers/Relatorios/VencimentoAsosController.php`
- `app/Jobs/Admissao/Processo/VencimentoAsoJob.php`
- `app/Console/Commands/VencimentoAsoCommand.php`
- `app/Models/FeedbackCurriculo.php` (scopes)
- `resources/js/components/relatorios/vencimentoasos/VencimentoAsos.vue`
