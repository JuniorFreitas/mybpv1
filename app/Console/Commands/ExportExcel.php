<?php

namespace App\Console\Commands;

use App\Models\Sistema;
use Illuminate\Console\Command;

class ExportExcel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mybp:exportarExcel {usuario} {local} {dados} {arquivo}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exporta dados para excel (padrão CIH / Maatwebsite) a partir de um array';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dados = $this->argument('dados');
        if (is_string($dados)) {
            $decoded = json_decode($dados, true);
            $dados = is_array($decoded) ? $decoded : [];
        }

        Sistema::exportaExcel(
            $this->argument('usuario'),
            (string) $this->argument('local'),
            is_array($dados) ? $dados : [],
            (string) $this->argument('arquivo')
        );

        $this->info('Exportação Excel gerada com sucesso.');

        return self::SUCCESS;
    }
}
