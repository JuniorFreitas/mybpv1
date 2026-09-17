# Schedule Kernel Laravel 12

Gotcha (resolvido 2026-09-16): `app/Console/Kernel.php` não era carregado pelo Laravel 12.

Fix:
- Agenda em `routes/schedule.php`
- Registro em `bootstrap/app.php` → `withSchedule(...)`
- `App\Console\Kernel` ficou como espelho/deprecated

Evidência: `php artisan schedule:list` lista as rotinas (JobAniversariantesDia, férias, ponto…).

Bug extra no job: `JobAniversariantesDia` usava `DB::raw()` em `DB::select()` → TypeError; SQL passou a string pura.

Catch-up aniversariantes do dia: rodar uma vez no ambiente com mail real  
`(new \App\Jobs\Rotinas\JobAniversariantesDia())->handle();`  
(não marcar enviado via Mailtrap/local — bloqueia o envio real)

Updated: 2026-09-16
