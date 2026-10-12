<?php

namespace App\Console\Commands;

use App\Models\Arquivo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Os anexos de pré-admissão/carta oferta antigos foram gravados como públicos no bucket.
 * Este comando marca cada objeto (arquivo e miniatura) como privado. Pode ser reexecutado.
 */
class AnexosPreAdmissaoPrivarCommand extends Command
{
    protected $signature = 'mybp:anexos-preadmissao-privar
        {--executar : Aplica a mudança (sem esta opção só conta o que seria alterado)}
        {--a-partir-de=0 : Retoma a partir deste id de arquivo}';

    protected $description = 'Torna privados no storage os anexos já existentes de pré-admissão e carta oferta';

    public function handle(): int
    {
        $disco = Arquivo::DISCO_DOCUMENTOS_PRE_ADMISSAO;
        $storage = Storage::disk($disco);
        $executar = (bool) $this->option('executar');
        $alterados = 0;
        $falhas = 0;
        $ultimoId = 0;

        DB::table('arquivos')
            ->where('disco', $disco)
            ->where('id', '>', (int) $this->option('a-partir-de'))
            ->select(['id', 'file', 'thumb'])
            ->chunkById(500, function ($arquivos) use ($storage, $executar, &$alterados, &$falhas, &$ultimoId) {
                foreach ($arquivos as $arquivo) {
                    foreach (array_filter([$arquivo->file, $arquivo->thumb]) as $caminho) {
                        if (!$executar) {
                            $alterados++;
                            continue;
                        }
                        try {
                            $storage->setVisibility($caminho, 'private');
                            $alterados++;
                        } catch (\Throwable $e) {
                            // Objeto ausente no storage ou sem permissão: segue e informa no final.
                            $falhas++;
                        }
                    }
                    $ultimoId = $arquivo->id;
                }
                $this->line("... até o arquivo #{$ultimoId}");
            });

        $this->info(($executar ? 'Objetos tornados privados: ' : 'Objetos que seriam alterados: ') . $alterados);
        if ($falhas > 0) {
            $this->warn("Falhas (objeto ausente ou sem permissão): {$falhas}");
        }
        if (!$executar) {
            $this->comment('Nada foi alterado. Rode com --executar para aplicar.');
        }

        return self::SUCCESS;
    }
}
