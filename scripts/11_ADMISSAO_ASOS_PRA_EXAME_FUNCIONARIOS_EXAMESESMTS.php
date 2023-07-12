<?php

use App\Models\AdmissaoAso;
use App\Models\AlternativaFormulario;
use App\Models\ExameFuncionario;
use App\Models\ClienteConfig;
use App\Models\Formulario;
use App\Models\RespostaAlternativas;
use App\Models\Sistema;
use App\Models\TipoRecebeEmail;
use App\Models\User;
use MasterTag\DataHora;

require dirname(__DIR__) . '/vendor/autoload.php';

$app = require_once dirname(__DIR__) . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$kernel->terminate($request, $response);

ini_set('memory_limit', '-1');
ini_set('max_execution_time', '-1');

unset($argv[0]);

try {
    DB::beginTransaction();
    $admissaoAsos = AdmissaoAso::withoutGlobalScopes()
        ->where('ativo', 1)
        ->whereNotNull('admissao_id')
        ->whereNotIn('empresa_id', ['40440'])
        ->select(['id', 'data_aso', 'data_vencimento', 'empresa_id', 'admissao_id'])->get();

    $aa = collect($admissaoAsos);

    $aa->each(function ($item) {
        $token = Sistema::uuid();
        $exame_tipo_id = 1;
        $empresa_id = $item->empresa_id;
        $encaminhamento_data = $item->data_aso;

        $formulario_id = Formulario::withoutGlobalScopes()->whereTitulo('Exames')->whereEmpresaId($empresa_id)->first()->id;

        $admissao = \App\Models\Admissao::withoutGlobalScopes()->where('id', $item->admissao_id)->first();
//        dd($admissao);
        $resultadoIntegrado = clone $admissao;
        $empresaExameId = $resultadoIntegrado->ResultadoIntegrado->empresa_exame_id ?? \App\Models\EmpresaExame::withoutGlobalScopes()->where('empresa_id', $empresa_id)->first()->id;
        $pcmso_id = $resultadoIntegrado->ResultadoIntegrado->pcmso_id ?? \App\Models\Pcmso::withoutGlobalScopes()->where('empresa_id', $empresa_id)->first()->id;

        $temExameFuncionario = ExameFuncionario::withoutGlobalScopes()
            ->whereFeedbackId($admissao->feedback_id)
            ->whereEmpresaExameId($empresaExameId)
            ->where('exame_tipo_id', $exame_tipo_id)
            ->where('pcmso_id', $pcmso_id)
            ->where('encaminhamento_data', '=', (new DataHora($encaminhamento_data))->dataInsert())->first();

        if (is_null($temExameFuncionario)) {
            $exameFuncionario = ExameFuncionario::withoutGlobalScopes()->firstOrCreate([
                'feedback_id' => $admissao->feedback_id,
                'empresa_id' => $empresa_id,
                'empresa_exame_id' => $empresaExameId,
                'formulario_id' => $formulario_id,
                'user_encaminhou_id' => $empresa_id,
                'respostas' => (object)[],
                'token' => $token,
                'pcmso' => true,
                'pcmso_id' => $pcmso_id,
                'exame_tipo_id' => $exame_tipo_id,
                'encaminhamento_data' => $encaminhamento_data
            ]);

            if ($exameFuncionario) {
                \App\Models\Examesesmt::withoutGlobalScopes()
                    ->whereFeedbackId($admissao->feedback_id)
                    ->whereEmpresaId($empresa_id)
                    ->update([
                        'atual' => 0
                    ]);

                $resultado = (object)[
                    "result" => "Apto",
                    "aprovado" => "Sim",
                    "pendencias" => "Não",
                    "observacoes" => null,
                    "trabalho_altura" => "Não se aplica",
                    "pendencias_quais" => null,
                    "espacao_confinado" => "Não se aplica"
                ];

                $exameSesmt = \App\Models\Examesesmt::withoutGlobalScopes()->firstOrCreate([
                    'feedback_id' => $admissao->feedback_id,
                    'empresa_id' => $empresa_id,
                    'exame_funcionario_id' => $exameFuncionario->id,
                    'exame_realizado' => 1,
                    'resultado' => $resultado,
                    'data_realizacao' => $item->data_aso,
                    'data_vencimento' => $item->data_vencimento,
                    'vencido' => 0,
                    'atual' => 1,
                    'user_id' => $empresa_id
                ]);
            }
        }

        \App\Models\ResultadoIntegrado::withoutGlobalScopes()->whereFeedbackId($admissao->feedback_id)->update([
            "encaminhado_exame" => true,
            "encaminhado_exame_data" => (new DataHora($encaminhamento_data))->dataInsert(),
            "pcmso_id" => $pcmso_id,
            "empresa_exame_id" => $empresaExameId,
        ]);

//        Log::debug('Ultimo Inserido: '.$item->id);
        echo 'Ultimo Inserido: ' . $item->id . PHP_EOL;
        DB::commit();
    });
} catch (\Exception $e) {
    Log::debug($e->getMessage() . ' | ' . $e->getLine());
    DB::rollBack();
}
