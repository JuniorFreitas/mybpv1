<?php

namespace App\Console\Commands;

use App\Services\ApiExterna\ApiExternaCredencialService;
use App\Support\ApiExterna\ApiExternaScopes;
use Illuminate\Console\Command;
use RuntimeException;

class ApiExternaCredencialCommand extends Command
{
    protected $signature = 'api-externa:credencial
        {empresa_id : ID da empresa (clientes.id)}
        {--rotate : Rotaciona o client_secret mantendo o client_id}
        {--scopes= : Scopes separados por vírgula (padrão: todos read)}';

    protected $description = 'Cria ou rotaciona credenciais da API Externa (client_id + client_secret) por empresa';

    public function handle(ApiExternaCredencialService $service): int
    {
        $empresaId = (int) $this->argument('empresa_id');
        $rotate = (bool) $this->option('rotate');
        $scopesOpt = $this->option('scopes');
        $scopes = $scopesOpt
            ? array_values(array_filter(array_map('trim', explode(',', (string) $scopesOpt))))
            : ApiExternaScopes::all();

        try {
            $resultado = $service->criarOuRotacionar($empresaId, $rotate, $scopes);
        } catch (RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info('Credencial API Externa gerada. Guarde o client_secret agora — ele não será exibido novamente.');
        $this->line('empresa_id: ' . $resultado['empresa_id']);
        $this->line('user_id: ' . $resultado['user_id']);
        $this->line('client_id: ' . $resultado['client_id']);
        $this->line('client_secret: ' . $resultado['client_secret']);
        $this->line('scopes: ' . implode(' ', $resultado['scopes']));

        return self::SUCCESS;
    }
}
