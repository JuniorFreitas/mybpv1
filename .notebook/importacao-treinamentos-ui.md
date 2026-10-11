# Importação Treinamentos UI

## Summary
Tela web para baixar Excel interativo por tenant e enviar planilha; job chama `mybp:importa-treinamento --path=`.

## Entry points
- UI: `GET/POST /g/treinamento/import` + `GET .../import/modelo` — `ImportacaoTreinamentoController` (`can:treinamento_importacao`)
- Vue: `resources/js/components/treinamentos/import/ImportacaoTreinamentos.vue`
- Job: `ImportacaoTreinamentoJob`
- Modelo: `GeradorModeloImportacaoTreinamento` (abas Dados + Listas + DataValidation)
- Leitor: `LeitorPlanilhaImportacaoTreinamento` → JSON shape do comando

## Flow
1. Download modelo → abas Dados + Listas + Instrucoes; data_treinamento em **dd/mm/aaaa**
2. Upload .xlsx → local + S3 → cria `importacao_treinamentos` (status processando) → job (lock por empresa)
3. Job lê planilha → JSON em `storage/app/importacao_treinamentos/{empresa}/{uuid}.json`
4. `Artisan::call('mybp:importa-treinamento', --path=..., --user_id, --gerar-relatorio, --relatorio-path, --enviar-email opcional)`
5. Comando: se treinamento existe → só atualiza vencimento/data; histórico 1:1 (`salvarHistoricoVencimento`, igual carteira)
6. `DivisorRelatorioImportacaoTreinamento` → CSV sucesso/erro no S3; `Exportacao` + sino + e-mail
7. UI: abas Importação / Importados (`GET historico`, `GET historico/{id}/csv/{sucesso|erro}`)

## Gotchas
- `contrato` não existe em `vencimentos`; gerador usa contrato mais frequente de `admissoes` ou `GERAL`
- Comando aceita `--path` absoluto; `--arquivo` continua no path legado `scripts/import_treinamento_json/`
- Habilidade nova: migration `2026_10_10_160000_add_treinamento_importacao_habilidade` + seeder
- Menu `@can('treinamento_importacao')` — migration cria habilidade e vincula papéis com `treinamento_carteira-etiquetas`
- Sem migrate: item não aparece no menu Treinamento
- Tabela real é `demissaos` (não `demissoes`) no filtro de colaboradores do modelo
- Histórico: migration `2026_10_10_231500_create_importacao_treinamentos_table`
- Build Mix: `resources/js/g/treinamentos/import/app.js` (abas + histórico)

## Docs
- Manual: `docs/importacao/IMPORTACAO_TREINAMENTOS.md`

## Related
- Tags: treinamento, importacao, excel
- Ver também: importacao-treinamento-engeko, importacao-admissoes-flow
