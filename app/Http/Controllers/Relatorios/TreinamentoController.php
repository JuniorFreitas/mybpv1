<?php

namespace App\Http\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use App\Jobs\JobRelatorioTreinamentoVencimento;
use App\Services\Relatorios\TreinamentoVencimentoRelatorioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TreinamentoController extends Controller
{
    public function index()
    {
        return view('g.relatorios.treinamento.index');
    }

    public function show(Request $request, TreinamentoVencimentoRelatorioService $service)
    {
        $user = auth()->user();
        $resultado = $service->listarParaTela($user, $request->all());

        return response()->json([
            'cc' => $resultado['cc'],
            'itens' => $resultado['itens'],
            'total_registros' => $resultado['total_registros'],
            'periodo_consultado' => $resultado['periodo_consultado'],
            'usando_feedback_curriculo_filter' => true,
            'regra_unica_excel' => true,
            'data_consulta' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    public function exportExcel(Request $request)
    {
        try {
            $userId = auth()->id();
            $requestData = $request->all();

            // Mesmos flags da regra única (tela + Excel)
            $requestData['campoVencimento'] = 'true';
            $requestData['campoDemitido'] = false;

            if (!isset($requestData['periodo'])) {
                $requestData['periodo'] = date('Y-m-d') . ' até ' . date(
                    'Y-m-d',
                    strtotime('+' . TreinamentoVencimentoRelatorioService::DIAS_PERIODO_PADRAO . ' days')
                );
                $requestData['vencimento'] = $requestData['periodo'];
            }

            $cacheKey = 'export_vencimento_treinamentos_' . $userId . '_' . md5(json_encode($requestData));

            if (Cache::get($cacheKey)) {
                $cacheData = Cache::get($cacheKey);
                $status = $cacheData['status'] ?? 'processing';
                $attempts = $cacheData['attempt'] ?? 1;
                $maxTries = $cacheData['max_tries'] ?? 3;

                switch ($status) {
                    case 'processing':
                        $message = "Exportação em andamento (tentativa {$attempts}/{$maxTries}). Aguarde a conclusão.";
                        break;
                    case 'retrying':
                        $message = "Exportação tentando novamente (tentativa {$attempts}/{$maxTries}). Aguarde.";
                        break;
                    case 'completed':
                        $message = "Exportação já foi concluída. Verifique suas notificações.";
                        break;
                    case 'failed':
                        $message = "Última exportação falhou após {$maxTries} tentativas. Você pode tentar novamente.";
                        break;
                    default:
                        $message = "Já existe uma exportação em andamento. Aguarde a conclusão.";
                        break;
                }

                return response()->json([
                    'msg' => $message,
                    'status' => $status,
                    'initiated_at' => $cacheData['initiated_at'] ?? null,
                    'attempts' => $attempts,
                    'max_tries' => $maxTries,
                    'last_error' => $cacheData['last_error'] ?? null,
                ], 200);
            }

            $nameArquivo = "vencimento_treinamentos_" . date('YmdHis') . "_" . rand(1000, 9999) . ".xlsx";
            $expiresAt = now()->addMinutes(15);

            Cache::put($cacheKey, [
                'filename' => $nameArquivo,
                'initiated_at' => now(),
                'expires_at' => $expiresAt,
                'user_id' => $userId,
                'status' => 'queued',
                'attempt' => 0,
                'max_tries' => 3,
                'progress' => 0,
                'tipo_relatorio' => 'vencimento_treinamentos',
            ], $expiresAt);

            JobRelatorioTreinamentoVencimento::dispatch(
                $userId,
                $requestData,
                $nameArquivo,
                $cacheKey
            );

            return response()->json([
                'msg' => 'Estamos gerando seu arquivo excel de vencimento de treinamentos. Assim que finalizado você será notificado.',
                'export_id' => $cacheKey,
                'estimated_time' => '5-15 minutos',
                'usando_job_vencimento_especifico' => true,
                'mesmo_formato_frontend' => true,
                'regra_unica_excel' => true,
                'otimizado_chunks' => true,
            ]);
        } catch (\Exception $e) {
            \Log::error("Erro no controller de export vencimento treinamentos: " . $e->getMessage() . " " . $e->getFile() . " on line " . $e->getLine());

            if (isset($cacheKey)) {
                Cache::forget($cacheKey);
            }

            return response()->json(['error' => 'Erro interno na exportação'], 500);
        }
    }
}
