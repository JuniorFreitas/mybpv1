# FAT treinamento vs Cloud 404

> gotcha | tags: treinamento, fat, cloud, anexo, signed-url

## Sintoma
Download/visualização da FAT em carteira de treinamentos retornava **404** após auth assinada do Cloud (ago/2026).

## Causa
- Upload FAT usava `Arquivo::DISCO_CLOUD` (`TreinamentoController::uploadAnexos`)
- Accessors de `Arquivo` geravam URL assinada `g.cloud.anexo-*`
- `CloudController::autorizarArquivoCloud()` exige `ItensCloud` — FAT não cria item no Cloud
- Middleware `can:cloud` excluía quem só tem carteira

## Correção
- Disco `disco-treinamento` (`config/filesystems.php`)
- URLs assinadas `g.treinamentos.treinamento.anexo-*` (`Arquivo` accessors)
- Legado: se `disco-cloud` mas existe em `treinamento_vencimento`, `usaRotasAssinadasTreinamento()` força URL de treinamento (não Cloud)
- Rotas com `signed:relative` + `can:treinamento_carteira-etiquetas`
- Auth: temporário via `quem_enviou`; permanente via `treinamento_vencimento` + `feedback_curriculos.empresa_id`
- Fallback storage: `disco-treinamento` → `disco-cloud` (arquivo físico antigo)
- Migration: `2026_08_12_153000_migrate_fat_arquivos_to_disco_treinamento.php`

## Refs
- `TreinamentoController::autorizarArquivoFat()` / `resolverDiscoArquivoFat()`
- `Arquivo::getUrlDownloadAttribute()` (ramo `DISCO_TREINAMENTO`)
- Rotas em `routes/web.php` (grupo `treinamentos.`)
