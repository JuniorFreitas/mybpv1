# Mensagem de aniversariante

Entry: tela Aniversariantes (`Aniversariantes.vue` + `AniversarianteMensagem.vue`)
Menu: só Aniversariantes (sem item separado)
API: `AniversarianteMensagemController` dados/salvar/preview
Envio: `AniversariantesMail` → `AniversarianteMensagemResolver` + `AniversarianteMensagemRenderer`
Fallback: `AniversarianteMensagemResolver::MENSAGEM_PADRAO` se não houver HTML da empresa
Job automático: `JobAniversariantesDia` via `routes/schedule.php` + `withSchedule` (daily 00:00)
Envio manual: `AniversariantesController::enviaEmail` → `JobAniversariantes` (fila) → funciona
Registro: tabela `parabens_enviados` (status enviado/enviando; sem created_at)
SQL do job: string em `DB::select` (não usar `DB::raw`)
Permissão: `administracao_aniversariantes`
Placeholders: `{{nome}}`, `{{empresa}}`
Editor: `tiny-mce-editor` self-host (`public/tinymce`), preset `padrao` — sem Cloud
Assunto: inalterado (`{empresa} - FELIZ ANIVERSÁRIO`)

Updated: 2026-08-31
