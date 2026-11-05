<?php

namespace App\Http\Controllers\SaudeOcupacional;

use App\Domain\SaudeOcupacional\Painel\PainelSaudeOcupacionalService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Throwable;

class PainelController extends Controller
{
    public function __construct(private readonly PainelSaudeOcupacionalService $painel)
    {
    }

    public function index()
    {
        return view('g.saude-ocupacional.painel.index');
    }

    public function dados(): JsonResponse
    {
        $empresaId = (int) auth()->user()->empresa_id;

        try {
            return response()->json([
                'success' => true,
                'resumo' => $this->painel->resumo($empresaId),
                'links' => $this->links(),
            ]);
        } catch (Throwable $e) {
            Log::error('Painel Saúde Ocupacional: falha ao calcular', [
                'empresa_id' => $empresaId,
                'erro' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Não foi possível carregar o Painel agora. Tente novamente em instantes.',
            ], 500);
        }
    }

    /**
     * Só devolve links das telas que o usuário pode abrir.
     *
     * @return array<string, string|null>
     */
    private function links(): array
    {
        $link = fn (string $habilidade, string $rota) => Gate::allows($habilidade) && Route::has($rota)
            ? route($rota)
            : null;

        return [
            'vencimento_asos' => $link('relatorio_asos', 'g.relatorios.vencimentoasos.index'),
            'conformidade' => $link('saude_ocupacional', 'g.saude_ocupacional.conformidade.index'),
            'controle_exames' => $link('admissao_controle_exames', 'g.controle_exames.index'),
            'periodicidade' => $link('cadastro_empresa_exame', 'g.aso_periodicidade.index'),
            'medicos' => $link('saude_ocupacional_configuracao', 'g.saude_ocupacional.medicos.index'),
            'pgr' => $link('saude_ocupacional_configuracao', 'g.saude_ocupacional.pgr.index'),
            'pcmso' => $link('saude_ocupacional_configuracao', 'g.saude_ocupacional.pcmso.index'),
            'documentos' => $link('saude_ocupacional', 'g.saude_ocupacional.documentos.index'),
        ];
    }
}
