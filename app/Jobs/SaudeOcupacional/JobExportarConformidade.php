<?php

namespace App\Jobs\SaudeOcupacional;

use App\Domain\SaudeOcupacional\Conformidade\ConformidadeService;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

/**
 * Exportação Excel da Conformidade de ASO (padrão de exportação: cabeçalho fixo, colunas
 * consistentes, "Não informado" no vazio, sem dado sensível). Arquivo vai para Exportações + notificação.
 */
class JobExportarConformidade implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public const LOCAL = 'Conformidade ASO';

    public const CABECALHO = [
        'Colaborador',
        'Cargo',
        'Situação',
        'Último ASO',
        'Realizado em',
        'Vencimento',
        'Dias para vencer',
        'Encaminhado em (sem resultado)',
    ];

    private const NAO_INFORMADO = 'Não informado';

    private const SITUACAO = [
        ConformidadeService::STATUS_VENCIDO => 'Vencido',
        ConformidadeService::STATUS_VENCENDO => 'Vencendo',
        ConformidadeService::STATUS_SEM_ASO => 'Sem ASO',
        ConformidadeService::STATUS_EM_DIA => 'Em dia',
    ];

    public $tries = 1;

    public $timeout = 600;

    /**
     * @param  array{status?: string|null, busca?: string|null, vaga_id?: int|null}  $filtros
     */
    public function __construct(
        public int $empresaId,
        public int $usuarioId,
        public array $filtros,
        public string $nomeArquivo,
        public string $chaveTrava
    ) {
    }

    public function handle(ConformidadeService $conformidade): void
    {
        try {
            $usuario = User::withoutGlobalScopes()->findOrFail($this->usuarioId);
            $linhas = [self::CABECALHO];

            $conformidade->percorrer($this->empresaId, $this->filtros, CarbonImmutable::today(), function (array $lote) use (&$linhas) {
                foreach ($lote as $l) {
                    $linhas[] = $this->linha($l);
                }
            });

            Artisan::call('mybp:exportarExcel', [
                'usuario' => $usuario,
                'local' => self::LOCAL,
                'dados' => $linhas,
                'arquivo' => $this->nomeArquivo,
            ]);
        } finally {
            Cache::forget($this->chaveTrava);
        }
    }

    /**
     * @param  array<string, mixed>  $l
     * @return list<string|int>
     */
    public function linha(array $l): array
    {
        $texto = fn ($valor) => ($valor === null || trim((string) $valor) === '') ? self::NAO_INFORMADO : $this->sanitizar((string) $valor);

        return [
            $texto($l['colaborador'] ?? null),
            $texto($l['cargo'] ?? null),
            self::SITUACAO[$l['status'] ?? ''] ?? self::NAO_INFORMADO,
            $texto($l['ultimo_tipo'] ?? null),
            $texto($l['ultimo_realizado_br'] ?? null),
            $texto($l['vencimento_br'] ?? null),
            $l['dias'] === null ? self::NAO_INFORMADO : (int) $l['dias'],
            $texto($l['encaminhado_em_br'] ?? null),
        ];
    }

    /** Evita injeção de fórmula ao abrir no Excel (=, +, -, @). */
    private function sanitizar(string $valor): string
    {
        return preg_match('/^[=+\-@]/', $valor) ? "'" . $valor : $valor;
    }
}
