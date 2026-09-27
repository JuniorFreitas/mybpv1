<?php

namespace App\Http\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use App\Jobs\JobExportaExcel;
use App\Models\ExameTipo;
use App\Services\Relatorios\AsoVencimentoRelatorioService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use MasterTag\DataHora;

class VencimentoAsosController extends Controller
{
    public function index()
    {
        return view('g.relatorios.vencimentoasos.index');
    }

    /**
     * Mantido para compatibilidade (Job antigo). Preferir o service.
     */
    public static function filtro($empresa_id, $dados, $request = null)
    {
        $user = auth()->user();
        if (!$user || (int) $user->empresa_id !== (int) $empresa_id) {
            $user = \App\Models\User::withoutGlobalScopes()
                ->where('empresa_id', $empresa_id)
                ->where('ativo', true)
                ->whereNotNull('login')
                ->first();
            if ($user) {
                auth()->login($user);
            }
        }

        if (!$user) {
            return [];
        }

        $request = $request ?? Request::create('/', 'POST', is_array($dados) ? $dados : []);
        $resultado = app(AsoVencimentoRelatorioService::class)->listarParaTela($user, $request);

        return $resultado['dados'];
    }

    public function show(Request $request, AsoVencimentoRelatorioService $service)
    {
        $resultado = $service->listarParaTela(auth()->user(), $request);

        return response()->json([
            'dados' => $resultado['dados'],
            'periodo_vencimento_numero' => $resultado['periodo_vencimento_numero'],
            'periodo_vencimento_extenso' => $resultado['periodo_vencimento_extenso'],
            'cc' => $resultado['cc'],
            'regra_unica' => true,
        ]);
    }

    public function exportExcel(Request $request, AsoVencimentoRelatorioService $service)
    {
        $resultado = $service->listarParaTela(auth()->user(), $request);
        $dados = $resultado['dados'];

        $head = [
            'Nome',
            'Cargo',
            'CNPJ da Empresa',
            'Empresa',
            'Centro de Custo',
            'Data da Admissão',
            'Tipo do Exame',
            'Data do ASO',
            'Vencimento ASO',
            'Dias',
            'Status',
        ];

        $rows = [];
        foreach ($dados as $row) {
            $dias = (int) ($row['dias_vencer'] ?? 0);
            $rows[] = [
                $row['colaborador'] ?? '',
                $row['cargo'] ?? '',
                $row['emp_cnpj'] ?? '',
                $row['emp_nome_fantasia'] ?? '',
                $row['emp_centro_custo'] ?? '',
                $row['data_admissao'] ?? '',
                $row['exame_tipo'] ?? '',
                $this->formatarDataExport($row['data_aso'] ?? null),
                $this->formatarDataExport($row['data_vencimento'] ?? null),
                $dias,
                $dias < 0 ? 'VENCIDO' : 'A VENCER',
            ];
        }

        $nameArquivo = 'vencimento_asos_' . Str::slug('ASO') . rand(1000, 9999) . '_' . date('YmdHis') . '.xlsx';
        JobExportaExcel::dispatch(auth()->id(), 'Vencimento ASO', $head, $rows, $nameArquivo);

        return response()->json([
            'msg' => 'Estamos gerando seu arquivo excel, assim que finalizado você será notificado.',
            'regra_unica' => true,
        ]);
    }

    public function tiposExames()
    {
        return ExameTipo::whereAtivo(true)->get();
    }

    private function formatarDataExport($data): string
    {
        if (!$data) {
            return '';
        }
        if (is_string($data) && str_contains($data, '/')) {
            return $data;
        }

        try {
            return (new DataHora($data))->dataCompleta();
        } catch (\Throwable $e) {
            return (string) $data;
        }
    }
}
