# Schedule Kernel Laravel 12

Gotcha (resolvido 2026-09-16): `app/Console/Kernel.php` não era carregado pelo Laravel 12.

Fix:
- Agenda em `routes/schedule.php`
- Registro em `bootstrap/app.php` → `withSchedule(...)`
- `App\Console\Kernel` ficou como espelho/deprecated

Evidência: `php artisan schedule:list` lista as rotinas (JobAniversariantesDia, férias, ponto…).

Aniversariantes (2026-09-22): `00:05` + retry `08:00` (TZ app) via `mybp:aniversariantes`.

Updated: 2026-09-22
