# Importação de Admissões

## Summary
Importação em lote via planilha .xlsx (aba "Dados"), assíncrona por fila, com upsert por CPF/empresa.

## Entry points
- UI: `GET/POST /g/admissao/import` — `AdmissaoController::import` / `importUpload` (can:admissao_importacao)
- Vue: `resources/js/components/admissao/import/ImportacaoAdmissoes.vue`
- CLI: `php artisan mybp:importar-admissoes` — `ImportarAdmissoesCommand`
- Job: `ImportacaoAdmissaoJob`

## Flow
1. Upload valida .xlsx (max 20MB) + empresa_id; salva local + S3 (`disco-exportacao`)
2. Dispatch `ImportacaoAdmissaoJob` (fila `queue.queues.importacao`)
3. Lock por empresa (`Cache::lock importacao_admissao_lock_{empresaId}`) — 1 import por vez
4. Por linha (chunks): validar → resolver vaga/área/CC → map → persistir (transação por linha)
5. CPF vazio = skip; CPF existente = update; novo = create
6. Relatório CSV de erros → S3; e-mail `ImportacaoConcluidaMail` + notificação

## Pipeline (services)
- `LeitorPlanilhaAdmissao` → `ValidadorLinhaPlanilhaAdmissao` → `ResolvedorVagaAreaCentroCusto` → `MapperLinhaPlanilhaParaPayload` → `PersistidorAdmissaoImportada`

## Docs
- `docs/importacao/IMPORTACAO_ADMISSOES.md`
- Mensagens: `lang/pt_BR/importacao_admissao.php`

## Gotchas
- Lock falhou = job ignorado (não re-enfileira)
- Legacy: `ImportJob`, scripts `scripts/0_IMPORTACAO*.php` — fluxo antigo separado
- O entrypoint Engeko `scripts/0_IMPORTACAO_ENGEKO_103967.php` configura o script Coimbra por constantes; sua planilha não possui `vaga_mun`, então usa São Luís-MA (`municipio_id=2743`).
- Outras importações no projeto: CBO (`ImportarCboCommand`), treinamentos (`ImportaTreinamento*`)

## Related
- Tags: admissao, importacao, queue
