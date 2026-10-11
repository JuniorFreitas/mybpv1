# Importação Treinamento Engeko

## Summary
Planilha XLSX → JSON → `mybp:importa-treinamento` para empresa 103967.

## Flow
1. Fonte: `scripts/xls/import_treinamento_engeko_preenchido_2026.xlsx` (Planilha1)
2. Formato coluna: `colaborador=matricula|cpf|nome|cargo`, `treinamento=vencimento_id|contrato|label`, `data_treinamento` (serial Excel)
3. JSON: `scripts/import_treinamento_json/import_treinamento_engeko_2026.json`
4. Comando: `ImportaTreinamento` (`mybp:importa-treinamento`) — `tipo=Fixo`, vencimento calculado por `prazo_fixo`

## Gotchas
- Comando só lê JSON em `scripts/import_treinamento_json/{arquivo}.json` (não XLSX direto)
- `tipo` no JSON é Fixo/Parada (não o nome do treinamento); o treino é o `vencimento_id`
- Pivot table: `treinamento_vencimento` (singular)
- Planilha2 do XLSX é lookup, não importar

## Run (2026-10-09)
- empresa_id 103967, 2408 registros, 0 falhas, 296 CPFs
- Relatório: `storage/app/relatorios/relatorio_importacao_treinamento_103967_2026-10-09_00-13-37.csv`
