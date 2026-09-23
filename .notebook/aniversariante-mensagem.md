# Mensagem / envio de aniversariante

## Fluxo
- Template: `AniversarianteMensagemController` + `AniversarianteMensagemResolver`/`Renderer`
- Automático: `routes/schedule.php` → `mybp:aniversariantes` (00:05 + retry 08:00, TZ app)
- Service: `AniversarianteEnvioDiaService` (job só orquestra)
- Manual tela: `AniversariantesController::enviaEmail` → `JobAniversariantes`
- Registro: `parabens_enviados` (sem timestamps): enviado | enviando | erro | não

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
