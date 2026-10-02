# Mensagem / envio de aniversariante

## Fluxo
- Template: `AniversarianteMensagemController` + `AniversarianteMensagemResolver`/`Renderer`
- Automático: `routes/schedule.php` → `mybp:aniversariantes` (00:05 + retry 08:00, TZ app)
- Service: `AniversarianteEnvioDiaService` (job só orquestra)
- Manual tela: `AniversariantesController::enviaEmail` → `JobAniversariantes`. Se `aniversario_whatsapp` estiver ligado, o modal usa dois switches (E-mail e WhatsApp); os dois ligados viram `ambos`. Sem a flag, só e-mail. O job automático (canal nulo) segue e-mail + WhatsApp quando habilitado.
- Lista (admin, relatório, PDF, Excel): `AniversariantesController::somenteAdmitidos()` — status `ADMITIDO` e sem demissão
- WhatsApp da lista: `Curriculo::TelWhatsappPrincipal()` — `curriculo_telefone` com `tipo = whatsapp` e `principal = true`
- WhatsApp no envio: flag `cliente_configs.aniversario_whatsapp` + `envia_whatsapp`. Mensagem do dia 1–30 (dia 31 usa a 30) em `aniversariante_whatsapp_mensagens` por `empresa_id`. `[Nome]` e `#BPTEAM` → Equipe de RH na hora do envio. Delay da fila: 5–15 s (`AniversarianteWhatsappService`), separado do delay geral de 5–10 s.
- Registro: `parabens_enviados` (sem timestamps): enviado | enviando | erro | não

## Gotcha
`FeedbackCurriculo::scopeAdmitidos()` só faz `whereDoesntHave('Demissao')`. Não exige `admissoes.status = ADMITIDO`. A lista de aniversário precisa do status; senão entra quem ainda está em processo.

Worker de fila antigo (Horizon/`queue:work` desde o boot do container) mantém a classe já carregada. Método novo em `WhatsappNotificationGateService` só vale depois de `php artisan queue:restart` e `horizon:terminate`. Sintoma: log `Falha ao enfileirar WhatsApp de aniversário` com `undefined method podeEnviarAniversario`, `whatsapp_status` nulo. E-mail local sai pelo Mailtrap (`MAIL_HOST`), não para a caixa real.

## Gotchas (corrigidos 2026-09-22)
1. Um e-mail inválido/vazio no meio do `foreach` abortava o lote inteiro.
2. `NOT EXISTS` em qualquer status → `enviando` travava a pessoa o ano todo.
3. `month/day(now())` no MySQL podia divergir do TZ `America/Fortaleza` → usar data do app.
4. Catch engolia exceção sem marcar `erro`.

## Ops
```bash
php artisan mybp:aniversariantes                 # dia (TZ app)
php artisan mybp:aniversariantes --retentar-pendentes  # enviando/erro do ano
php artisan mybp:aniversariantes --queue
```
Não marcar `enviado` via Mailtrap/local se for catch-up de produção.

## Refs
- `app/Services/Aniversariante/AniversarianteEnvioDiaService.php`
- `app/Jobs/Rotinas/JobAniversariantesDia.php`
- `app/Console/Commands/DispararAniversariantesCommand.php`
- `routes/schedule.php`
