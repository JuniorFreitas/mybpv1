# Periodicidade ASO por empresa/tipo

Last checked: 2026-11-04

## Regra

- Config: `empresas_exame_tipos_periodicidades` (`unidade` anos|dias + `valor`)
- Anos: `data_realizacao + N anos − 1 dia`
- Dias: `+ N dias` exatos
- Default: 1 ano − 1 dia
- Demissional: sem vencimento (null); fora do save/job/UI editável
- Alerta `ClienteConfig.vencimento_aso` ≠ periodicidade

## Aplicação ao salvar

- `futuro` — sem job
- `retroativo` — job todos `atual=1` dos tipos alterados
- `a_partir_de` — job com `data_realizacao >= data`

Histórico: `empresas_exame_tipos_periodicidades_historicos` + activity_log (`descricao` obrigatória)

## UI

- Menu Cadastro → Periodicidade ASO (`cadastro_empresa_exame`)
- Modal aplicar: rodapé só Cancelar + Confirmar
- Demissional: “Sem vencimento”

## Jobs (baixo RAM/CPU)

- `JobRecalcularVencimentoAso` → 1º lote por tipo
- `JobRecalcularVencimentoAsoLote` cursor `after_id` + LIMIT ~300 + UPDATE SQL
- Lock `aso:recalc:{empresa_id}`

## Arquivos

- `docs/ASO_PERIODICIDADE_EMPRESA.md`
- Manual: seção 4.9 em `docs/manual-completo/secoes/04-cadastros.html`
- `app/Services/ControleExames/AsoPeriodicidadeService.php`
- Vue: `resources/js/components/cadastros/aso-periodicidade/AsoPeriodicidade.vue`
- Testes: `tests/Unit/Services/ControleExames/AsoPeriodicidadeServiceTest.php`
