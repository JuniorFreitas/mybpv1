<?php

namespace App\Jobs\AtaReuniao;

use App\Exports\ModeloRowsExport;
use App\Models\Arquivo;
use App\Models\Exportacao;
use App\Models\User;
use App\Services\AtaReuniao\AtaReuniaoRelatorioExportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use MasterTag\CsvExporter;
use PDF;

class ExportAtaReuniaoRelatorioJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public int $userId,
        public string $formato,
        public array $filtros = []
    ) {
    }

    public function handle(AtaReuniaoRelatorioExportService $service): void
    {
        $user = User::withoutGlobalScopes()->find($this->userId);

        if (!$user) {
            return;
        }

        $dados = $service->dados($user, $this->filtros);
        $headers = $service->headers();
        $rows = $service->rows($dados);
        $formato = strtolower($this->formato);

        if ($formato === 'csv') {
            (new CsvExporter($user, 'Relatorio Atas e Pendencias', $headers, $rows))->export();
            return;
        }

        $fileName = 'relatorio_atas_pendencias_' . $user->id . '_' . $user->empresa_id . '_' . now()->format('YmdHis') . '.' . $formato;

        if ($formato === 'xlsx') {
            Excel::store(new ModeloRowsExport($headers, $rows), $fileName, Arquivo::DISCO_EXPORTACAO);
        } elseif ($formato === 'pdf') {
            $pdf = PDF::loadView('pdf.administracao.atareuniao.relatorio', [
                'headers' => $headers,
                'rows' => $rows,
                'filtros' => $this->filtros,
            ]);
            Storage::disk(Arquivo::DISCO_EXPORTACAO)->put($fileName, $pdf->output());
        } else {
            return;
        }

        Exportacao::create([
            'user_id' => $user->id,
            'arquivo' => $fileName,
            'local' => 'Relatorio Atas e Pendencias',
            'removido' => false,
        ]);
    }
}
