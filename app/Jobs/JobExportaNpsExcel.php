<?php

namespace App\Jobs;

use App\Events\Notificacoes\NotificacaoEvent;
use App\Models\Arquivo;
use App\Models\Exportacao;
use App\Models\NpsPergunta;
use App\Models\NpsResposta;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class JobExportaNpsExcel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 600;
    public $queue;

    protected $userId;
    protected $nomeArquivo;
    protected $filtros;
    protected $lockKey;
    protected $lockTimeout = 1200;

    const CHUNK_SIZE = 500;

    public function __construct($userId, $nomeArquivo, $filtros)
    {
        $this->userId = $userId;
        $this->nomeArquivo = $nomeArquivo;
        $this->filtros = $filtros;
        $this->lockKey = 'nps_export_lock_' . md5($nomeArquivo . '_' . $userId . '_' . json_encode($filtros));
    }

    public function handle(): void
    {
        if (!$this->acquireLock()) {
            \Log::info("Job NPS Excel já em processamento. Lock: {$this->lockKey}");
            return;
        }

        try {
            \Log::info('Iniciando exportação NPS Excel');
            \Auth::loginUsingId($this->userId);

            $user = User::find($this->userId);
            if (!$user) {
                throw new \Exception("Usuário {$this->userId} não encontrado.");
            }

            $query = $this->buildQuery($user);
            $perguntas = NpsPergunta::orderBy('ordem')->get(['id', 'texto', 'ordem']);

            $headers = ['Usuário', 'Empresa', 'Ciclo', 'Data'];
            foreach ($perguntas as $p) {
                $headers[] = 'P' . $p->ordem . ' - ' . mb_substr($p->texto, 0, 50) . (mb_strlen($p->texto) > 50 ? '...' : '');
            }

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Respostas NPS');
            $sheet->fromArray($headers, null, 'A1');

            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '366092'],
                ],
            ];
            $lastCol = $this->colLetter(count($headers));
            $sheet->getStyle('A1:' . $lastCol . '1')->applyFromArray($headerStyle);

            $currentRow = 2;
            $totalProcessed = 0;

            $query->chunk(self::CHUNK_SIZE, function ($respostas) use ($sheet, $perguntas, &$currentRow, &$totalProcessed) {
                $rows = [];
                $empresasNomes = $this->empresasNomesForIds($respostas->pluck('empresa_id')->unique()->filter()->all());

                foreach ($respostas as $r) {
                    $row = [
                        $r->User ? ($r->User->nome ?? $r->User->login) : '—',
                        $empresasNomes[$r->empresa_id] ?? '—',
                        $r->npsCiclo ? $r->npsCiclo->nome : '—',
                        $r->created_at ? $r->created_at->format('d/m/Y H:i') : '—',
                    ];
                    $itensPorPergunta = $r->itens->keyBy('nps_pergunta_id');
                    foreach ($perguntas as $p) {
                        $item = $itensPorPergunta->get($p->id);
                        $row[] = $item ? (int) $item->nota : '';
                    }
                    $rows[] = $row;
                    $totalProcessed++;
                }

                if (!empty($rows)) {
                    $sheet->fromArray($rows, null, 'A' . $currentRow);
                    $currentRow += count($rows);
                }
                unset($rows);

                if (function_exists('gc_collect_cycles') && ($totalProcessed % 1000 === 0)) {
                    gc_collect_cycles();
                }
            });

            foreach (range('A', $lastCol) as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $tempPath = storage_path('app/temp/' . $this->nomeArquivo);
            if (!is_dir(dirname($tempPath))) {
                mkdir(dirname($tempPath), 0755, true);
            }
            $writer = new Xlsx($spreadsheet);
            $writer->save($tempPath);
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

            $content = file_get_contents($tempPath);
            Storage::disk(Arquivo::DISCO_EXPORTACAO)->put($this->nomeArquivo, $content);
            unlink($tempPath);

            Exportacao::create([
                'user_id' => $this->userId,
                'arquivo' => $this->nomeArquivo,
                'local' => 'Relatório NPS',
                'removido' => false,
            ]);

            Event::dispatch(new NotificacaoEvent([
                'user_id' => $this->userId,
                'local' => 'Relatório NPS',
            ], NotificacaoEvent::EXPORTACAO_EXCEL, NotificacaoEvent::TIPO_PADRAO));

            \Log::info("Exportação NPS Excel concluída. Total: {$totalProcessed} linhas.");
        } catch (\Throwable $e) {
            \Log::error('Erro exportação NPS Excel: ' . $e->getMessage());
            throw $e;
        } finally {
            $this->releaseLock();
        }
    }

    private function colLetter(int $n): string
    {
        $letter = '';
        while ($n > 0) {
            $n--;
            $letter = chr(65 + ($n % 26)) . $letter;
            $n = (int) floor($n / 26);
        }
        return $letter ?: 'A';
    }

    private function empresasNomesForIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }
        return User::whereIn('id', $ids)->get(['id', 'nome'])->mapWithKeys(function ($u) {
            return [$u->id => $u->nome ?: 'Empresa #' . $u->id];
        })->all();
    }

    private function buildQuery(User $user): \Illuminate\Database\Eloquent\Builder
    {
        $dataInicio = null;
        $dataFim = null;
        if (!empty($this->filtros['data_inicio'])) {
            try {
                $dataInicio = Carbon::createFromFormat('d/m/Y', $this->filtros['data_inicio'])->startOfDay();
            } catch (\Exception $e) {
            }
        }
        if (!empty($this->filtros['data_fim'])) {
            try {
                $dataFim = Carbon::createFromFormat('d/m/Y', $this->filtros['data_fim'])->endOfDay();
            } catch (\Exception $e) {
            }
        }
        $empresaId = isset($this->filtros['empresa_id']) && $this->filtros['empresa_id'] !== '' && $this->filtros['empresa_id'] !== null
            ? (int) $this->filtros['empresa_id'] : null;
        $cicloId = isset($this->filtros['ciclo_id']) && $this->filtros['ciclo_id'] !== '' && $this->filtros['ciclo_id'] !== null
            ? (int) $this->filtros['ciclo_id'] : null;

        $query = NpsResposta::with(['User:id,nome,login', 'npsCiclo:id,nome', 'itens.npsPergunta:id,texto,ordem'])
            ->orderByDesc('created_at');

        if ($cicloId !== null) {
            $query->where('nps_ciclo_id', $cicloId);
        }
        if ($dataInicio) {
            $query->where('created_at', '>=', $dataInicio);
        }
        if ($dataFim) {
            $query->where('created_at', '<=', $dataFim);
        }
        if ($empresaId !== null) {
            $query->where('empresa_id', $empresaId);
        }

        return $query;
    }

    private function acquireLock(): bool
    {
        try {
            return Cache::store('redis')->add($this->lockKey, gethostname() . '_' . getmypid() . '_' . time(), $this->lockTimeout);
        } catch (\Exception $e) {
            \Log::warning('Lock Redis NPS export: ' . $e->getMessage());
            return true;
        }
    }

    private function releaseLock(): void
    {
        try {
            Cache::store('redis')->forget($this->lockKey);
        } catch (\Exception $e) {
            // ignore
        }
    }

    public function failed(\Throwable $exception): void
    {
        $this->releaseLock();
    }
}
