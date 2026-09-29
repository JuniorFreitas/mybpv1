# Cloud multi-seleção e drag-to-folder

- Seleção múltipla (checkbox) + barra Mover + drag interno para pasta em `resources/js/components/Cloud.vue`
- Batch API: `POST itenscloud/mover-varios` → `ItensCloudController::moverVarios()` / `moverItem()`
- Drag interno usa MIME `application/x-mybp-cloud-itens` (não conflita com upload `Files`)
- Modal/PastaCloud aceita `model.arquivos[]`; anti-ciclo pasta→subpasta no backend
- Desabilitado em `modoBusca`

Tags: cloud, drag, move, ux
