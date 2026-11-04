<?php

namespace App\Support\WeeklyReport;

use App\Models\Arquivo;
use App\Models\Tarefa;
use Illuminate\Support\Collection;

/**
 * Rotas de anexo do Weekly Report são nested (empresa/quadro/lista/tarefa).
 * O accessor genérico de Arquivo aponta para /g/weekly-report/anexo/{file} (rota removida).
 */
final class WeeklyReportAnexoUrls
{
    /**
     * @return array<string, mixed>
     */
    public static function forArquivo(
        Arquivo $arquivo,
        int $empresa,
        int $quadroId,
        int $listaId,
        int $tarefaId
    ): array {
        $base = [
            'empresa' => $empresa,
            'quadro' => $quadroId,
            'lista' => $listaId,
            'tarefa' => $tarefaId,
        ];

        $fileParams = $base + ['arquivo' => $arquivo->file];
        $thumbFile = ($arquivo->imagem && $arquivo->thumb) ? $arquivo->thumb : $arquivo->file;
        $thumbParams = $base + ['arquivo' => $thumbFile];

        $data = $arquivo->toArray();
        $data['url'] = route('g.weekly-report.anexo-tarefa.anexo-show', $fileParams, true);
        $data['urlThumb'] = route('g.weekly-report.anexo-tarefa.anexo-show', $thumbParams, true);
        $data['urlDownload'] = route('g.weekly-report.anexo-tarefa.anexo-download', $fileParams, true);
        $data['urlDelete'] = route('g.weekly-report.anexo-tarefa.anexo-delete', $fileParams, true);

        return $data;
    }

    public static function hydrateTarefaAnexos(
        Tarefa $tarefa,
        int $empresa,
        int $quadroId,
        int $listaId
    ): void {
        if (!$tarefa->relationLoaded('Anexos')) {
            return;
        }

        $mapped = $tarefa->Anexos
            ->map(function ($arquivo) use ($empresa, $quadroId, $listaId, $tarefa) {
                if (!$arquivo instanceof Arquivo) {
                    return $arquivo;
                }

                return self::forArquivo(
                    $arquivo,
                    $empresa,
                    $quadroId,
                    $listaId,
                    (int) $tarefa->id
                );
            })
            ->values();

        $tarefa->setRelation('Anexos', $mapped);
    }

    /**
     * @param  Collection<int, Arquivo>|iterable<Arquivo>  $anexos
     * @return Collection<int, array<string, mixed>>
     */
    public static function mapCollection(
        iterable $anexos,
        int $empresa,
        int $quadroId,
        int $listaId,
        int $tarefaId
    ): Collection {
        return collect($anexos)
            ->map(function ($arquivo) use ($empresa, $quadroId, $listaId, $tarefaId) {
                if (!$arquivo instanceof Arquivo) {
                    return $arquivo;
                }

                return self::forArquivo($arquivo, $empresa, $quadroId, $listaId, $tarefaId);
            })
            ->values();
    }
}
