<?php

namespace App\Console\Commands;

use App\Models\Cliente;
use App\Models\Grupo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Junta 2+ empresas (clientes) já existentes num mesmo grupo — Fase 2 da
 * reestruturação de multi-tenancy (users pertencerem a N empresas do mesmo
 * grupo). Cada empresa nasce como grupo de 1; este comando move as empresas
 * informadas para o grupo da empresa marcada como matriz e desativa os
 * grupos de 1 que ficaram vazios.
 *
 * Uso:
 *   php artisan mybp:grupos-agrupar 104 40568 --matriz=104
 */
class AgruparEmpresasCommand extends Command
{
    protected $signature = 'mybp:grupos-agrupar
                            {empresa_ids* : IDs das empresas (clientes.id) a agrupar}
                            {--matriz= : ID da empresa que vira matriz do grupo (padrão: a primeira da lista)}';

    protected $description = 'Agrupa empresas existentes num mesmo grupo, definindo qual delas é a matriz';

    public function handle(): int
    {
        $empresaIds = array_map('intval', $this->argument('empresa_ids'));

        if (count($empresaIds) < 2) {
            $this->error('Informe pelo menos 2 IDs de empresa para agrupar.');

            return self::FAILURE;
        }

        if (count($empresaIds) !== count(array_unique($empresaIds))) {
            $this->error('IDs repetidos na lista.');

            return self::FAILURE;
        }

        $empresas = Cliente::withoutGlobalScopes()->whereIn('id', $empresaIds)->get()->keyBy('id');

        $faltando = array_diff($empresaIds, $empresas->keys()->all());
        if ($faltando !== []) {
            $this->error('Empresa(s) não encontrada(s): ' . implode(', ', $faltando));

            return self::FAILURE;
        }

        $matrizId = (int) ($this->option('matriz') ?: $empresaIds[0]);
        if (! $empresas->has($matrizId)) {
            $this->error("Empresa matriz #{$matrizId} não está na lista informada.");

            return self::FAILURE;
        }

        $this->table(
            ['ID', 'Empresa', 'Grupo atual', 'Vira'],
            $empresas->map(fn (Cliente $e) => [
                $e->id,
                $e->nome_fantasia ?? $e->nome,
                $e->grupo_id,
                $e->id === $matrizId ? 'MATRIZ' : 'filial do grupo',
            ])->values()->all()
        );

        if (! $this->confirm('Confirma o agrupamento acima?')) {
            $this->info('Cancelado.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($empresas, $matrizId) {
            $grupoDestino = $empresas[$matrizId]->grupo_id;
            $gruposOrigemParaDesativar = $empresas->pluck('grupo_id')
                ->unique()
                ->reject(fn ($grupoId) => $grupoId === $grupoDestino);

            foreach ($empresas as $empresa) {
                $empresa->update([
                    'grupo_id' => $grupoDestino,
                    'matriz' => $empresa->id === $matrizId,
                ]);
            }

            if ($gruposOrigemParaDesativar->isNotEmpty()) {
                Grupo::whereIn('id', $gruposOrigemParaDesativar)->update(['ativo' => false]);
            }
        });

        $this->info('Agrupamento concluído.');

        return self::SUCCESS;
    }
}
