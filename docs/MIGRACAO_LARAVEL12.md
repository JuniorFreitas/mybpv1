# Migracao Laravel 12 - Resumo

## Visao geral

-   Upgrade do Laravel 8 para Laravel 12 com ajuste de dependencias e bootstrap.
-   Correcoes de compatibilidade para Activitylog v4, CORS e TrustProxies.
-   Ajustes em broadcasting (Reverb disponivel, fallback para Pusher no frontend).

## Mudancas principais por area

### Dependencias / Laravel 12

-   `composer.json`, `composer.lock`: upgrade para Laravel 12 e libs compativeis.
-   Removidos pacotes legados (`fideloper/proxy`, `fruitcake/laravel-cors`, `league/flysystem-cached-adapter`).
-   Adicionado `laravel/reverb` e atualizacao do activitylog para v4.

### Bootstrap / Middleware / Providers

-   `bootstrap/app.php`: novo fluxo de bootstrap do Laravel 12.
-   `app/Http/Kernel.php`: remocao do middleware de CORS do Fruitcake.
-   `app/Http/Middleware/TrustProxies.php`: troca para middleware nativo do Laravel.
-   `app/Providers/RouteServiceProvider.php`: rotas centralizadas no bootstrap.

### Configuracoes

-   `config/broadcasting.php`: driver `reverb` adicionado e defaults alinhados.
-   `config/reverb.php`: novo arquivo de configuracao.
-   `config/sanctum.php`: compatibilidade Sanctum v4.
-   `.env.example`: variaveis Reverb adicionadas.

### Activitylog v4

-   `app/Models/Concerns/HasActivitylogOptions.php`: novo trait com `getActivitylogOptions()`.
-   `app/Models/*.php`: inclusao do trait junto ao `LogsActivity`.

### Frontend

-   `resources/js/bootstrap.js`: Echo so inicia se houver chave; fallback para Pusher.

### Rotas

-   `routes/web.php`: rota `weekly-report/{empresa}/quadros/{quadro}/listas` renomeada para `weekly-report.listas.index` para evitar conflito com `weekly-report.index`.

## Observacoes de execucao

-   `php artisan --version` ok (12.x).
-   `php artisan route:list` ok.
-   `php artisan test` apontou ausencia de `tests/Unit` no container.

## Proximos passos sugeridos

1. Rodar build de frontend (`npm run dev` ou `npm run prod`).
2. Validar broadcasting com Pusher em todos os ambientes (`BROADCAST_CONNECTION=pusher`).
3. Executar smoke tests dos fluxos criticos e monitorar logs.
