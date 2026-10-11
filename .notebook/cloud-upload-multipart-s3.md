# Cloud upload híbrido (multipart S3 + IndexedDB)

## Summary
Upload do Cloud: &lt;20MB via `POST uploadAnexos`; ≥20MB via multipart (browser→S3 ou partes locais) com retomada na mesma sessão (IndexedDB).

## Flow
1. `Cloud.vue` → `Upload.vue` com `uploadDiretoS3` + `urlsMultipart`
2. Grande: `CloudMultipartUploadController` → `CloudMultipartUploadService`
   - iniciar → assinar-parte / parte → completar | abortar
3. Completar cria `Arquivo` (`disco-cloud`) + `ItensCloud` (mesmas regras de duplicata/permissões do upload clássico)
4. Frontend: `resources/js/utils/cloudMultipartUpload.js` + `cloudUploadSessionStore.js`
5. F5: `listCloudUploadSessionsForCloud` em `Cloud.vue` após listar pasta

## Key files
- `app/Services/Cloud/CloudMultipartUploadService.php`
- `app/Http/Controllers/CloudMultipartUploadController.php`
- `resources/js/components/Upload.vue` (`enviarArquivoMultipart`)
- `resources/js/components/Cloud.vue` (props + retomar)
- Rotas: `g.itenscloud.multipart.*` em `routes/web.php`

## Gotchas
- POST clássico estoura ~100MB (`ValidatePostSize`); Nginx já aceita 800M — não é o gargalo.
- Bucket S3 precisa de CORS `PUT` na origem do MyBP (ETag exposto).
- `modo` local quando `filesystems.disks.disco-cloud.driver !== s3` (testes / local).
- **Nunca gravar File/Blob grande no IndexedDB** — trava o upload em 0%. Guardar só metadados/partes; no F5 o usuário re-seleciona o arquivo.
- Usar `window.axios` no multipart (CSRF do bootstrap).
- Retomada: banner Continuar/Descartar (não auto-start).

## Tags
flow, cloud, upload, s3, multipart, indexeddb
