# Relatório treinamentos / vencimentos — regras e divergências

Last checked: 2026-09-26 | sample: Montisol `empresa_id=63122`

## Regra única (tela + Excel + e-mail)

Fonte: `TreinamentoVencimentoRelatorioService`

Usada por:
- `Relatorios\TreinamentoController::show` (tela)
- `JobRelatorioTreinamentoVencimento` (Excel)
- `mybp:treinamento-vencimento` (e-mail)

1. Sem demissão (`Admitidos`) + `encaminhado_treinamento=true`
2. Filtro `treinamento_vencimento.data_vencimento` no período
3. Pós-filtro por segmento da admissão (fallback Alumar)
4. Categorias: VENCIDO `<0` | PROXIMO `<=30` | ATENCAO `<=60` | REGULAR
5. Tela/Excel período padrão: hoje → +30 dias
6. E-mail: período `2000-01-01` → hoje+60 (inclui vencidos) e corta em `DIAS_ATENCAO=60`

## Arquivos

- `app/Services/Relatorios/TreinamentoVencimentoRelatorioService.php`
- `app/Http/Controllers/Relatorios/TreinamentoController.php`
- `app/Jobs/JobRelatorioTreinamentoVencimento.php`
- `app/Console/Commands/TreinamentoVencimento.php`
- `app/Services/Treinamento/FeedbackCurriculoFilter.php`
- `resources/js/components/relatorios/treinamento/index.vue`
