# TinyMCE self-host (sem Cloud)

> gotcha | tags: tinymce, frontend, editor

## O que usar
- Componente global `tiny-mce-editor` (`TinyMceEditor.vue`)
- Binário LGPL 5.x em `public/tinymce/` (`tinymce.min.js` + plugins/skins/langs)
- Presets: `padrao` | `simples` | `basico` | `prova` | `entrevista` em `utils/tinymceSelfhost.js`
- TinyMCE no Vue: root deve ser um `div` host; TinyMCE cria o textarea por fora. Se o root for `<textarea>`, o Tiny substitui o nó e o Vue quebra com `insertBefore` / `__vnode` ao atualizar (ex.: publicar comentário).

## Não usar
- `@tinymce/tinymce-vue` + `api-key` / `MIX_TYNEKEY` (Cloud)
- Idioma antigo `public/js/tinymce/langs/pt_BR.js` (Mix ainda copia; o editor usa `/tinymce/langs/pt-br.min.js`)

## Gotcha
Wrapper Vue 6 do npm espera TinyMCE 6/7. O self-host do `public` é 5.x — por isso o wrapper próprio, não o pacote Cloud.
Menção no meio do texto: TinyMCE injeta ZWSP após `contenteditable=false`; não usar `selection.setContent('')` antes do insert.

Updated: 2026-09-25
