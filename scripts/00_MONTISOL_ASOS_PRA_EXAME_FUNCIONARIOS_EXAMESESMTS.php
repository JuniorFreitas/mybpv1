<?php

use App\Imports\Admissaoimport;
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
use PhpOffice\PhpSpreadsheet\Shared\Date;

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
$import = new Admissaoimport;
\Excel::import($import, base_path('scripts/xls/montisol_aso.xlsx'));

$empresa_id = 63122;
$user_id = $empresa_id;
Auth::loginUsingId($user_id);
$count = 1;

try {
    DB::beginTransaction();

    $dados = $import->dados->map(function ($line) use ($empresa_id, &$count) {
        return [
            'cpf' => $line['cpf'],
            'data_aso' => Date::excelToDateTimeObject($line['data_aso'])->format('Y-m-d')
        ];
    })->filter(function ($item) {
        return !is_null($item['cpf']);
    })->unique('cpf')->toArray();


    $empresaExameId = DB::table('empresa_exames as ee')->select(['ee.id'])->where('ee.empresa_id', $empresa_id)->first()->id;

    $pcmso_id = DB::table('pcmsos as p')->select(['p.id'])->where('p.empresa_id', $empresa_id)->first()->id;
    $formulario_id = DB::table('formularios as f')->select(['f.id'])->where('f.titulo', 'Exames')->where('f.empresa_id', $empresa_id)->first()->id;

    $dados = collect($dados)->chunk(10)->each(function ($records) use ($empresa_id, &$count, $empresaExameId, $pcmso_id, $formulario_id) {
        foreach ($records as $record) {
            if ($record['cpf'] != '040.691.483-45') {
                $feedback = DB::table('feedback_curriculos as fc')
                    ->select(['fc.id', 'fc.curriculo_id', 'c.cpf', 'adm.data_admissao'])
                    ->join('curriculos as c', 'c.id', '=', 'fc.curriculo_id')
                    ->join('admissoes as adm', 'adm.feedback_id', '=', 'fc.id')
                    ->where('c.cpf', $record['cpf'])
                    ->where('fc.empresa_id', $empresa_id)
                    ->whereNull('fc.deleted_at')
                    ->first();

                if (is_null($feedback)) {
                    continue;
                } else {

                    $temExameFuncionario = DB::table('exame_funcionarios as ef')
                        ->select(['ef.id', 'e.exame_realizado', 'e.data_realizacao', 'e.data_vencimento', 'e.id as examesesmts_id'])
                        ->join('examesesmts as e', 'e.exame_funcionario_id', '=', 'ef.id')
                        ->where('ef.feedback_id', $feedback->id)
                        ->where('ef.empresa_id', $empresa_id);

                    if ($temExameFuncionario->count() > 0) {
                        $token = Sistema::uuid();
                        $exame_tipo_id = 1;
                        $encaminhamento_data = $record['data_aso'];
                        $tExame = $temExameFuncionario->first();

                        if ($tExame->data_realizacao == $encaminhamento_data) {
                            continue;
//                            print_r("Data de realização do exame " . $tExame->data_realizacao . " é igual a data de encaminhamento " . $encaminhamento_data . " : " . $feedback->id . PHP_EOL);
                        } else {

                            DB::table('examesesmts as e')
                                ->where('e.id', $tExame->examesesmts_id)
                                ->update([
                                    'data_realizacao' => $encaminhamento_data,
                                    'data_vencimento' => \Carbon\Carbon::parse($encaminhamento_data)->addYear()->format('Y-m-d'),
                                ]);

                            print_r($tExame->examesesmts_id . ", ");

//                            print_r("Atualizado data de realização do exame " . $tExame->data_realizacao . " para data de encaminhamento " . $encaminhamento_data . " : " . $feedback->id . PHP_EOL);
//                            print_r("Data de realização do exame " . $tExame->data_realizacao . " é diferente a data de encaminhamento " . $encaminhamento_data . " : " . $feedback->id . PHP_EOL);
                        }


//                        print_r("Ja tem exame para o funcionario: " . $feedback->id . PHP_EOL);
                    } else {
//                        print_r("Não tem exame para o funcionario: " . $feedback->cpf . ' ADMITIDO EM: ' . $feedback->data_admissao . PHP_EOL);
                    }


//                    if ($temExameFuncionario->count() > 0) {
//

//                        //                print_r("Já existe exame para o funcionário: " . $feedback->id . " Quantidade de " . $temExameFuncionario->count() . " TIPO DO EXAME = " . $temExameFuncionario->first()->exame_tipo_id . PHP_EOL);
//                    } else {
//                        print_r("Não existe exame para o funcionário: " . $feedback->id . PHP_EOL);
//                    }

                }
            }
            /* die();

             $empresaExameId = \App\Models\EmpresaExame::select(['id'])->where('empresa_id', $empresa_id)->first()->id;
             $pcmso_id = \App\Models\Pcmso::select(['id'])->where('empresa_id', $empresa_id)->first()->id;
             $exame_tipo_id = 1;


             $temExameFuncionario = ExameFuncionario::withoutGlobalScopes()
                 ->whereFeedbackId($feedback->id)
                 ->whereEmpresaExameId($empresaExameId);

             if ($temExameFuncionario->count() > 0) {

                 print_r($temExameFuncionario->first()->toArray());
                 echo PHP_EOL;
 //                print_r("Já existe exame para o funcionário: " . $feedback->id . " Quantidade de " . $temExameFuncionario->count() . " TIPO DO EXAME = " . $temExameFuncionario->first()->exame_tipo_id . PHP_EOL);
             } else {
                 print_r("Não existe exame para o funcionário: " . $feedback->id . PHP_EOL);
             }*/

//                ->where('exame_tipo_id', $exame_tipo_id)
//                ->where('pcmso_id', $pcmso_id)
//                ->where('encaminhamento_data', '=', (new DataHora($encaminhamento_data))->dataInsert())->first();

//            $token = Sistema::uuid();
//            $exame_tipo_id = 1;
//            $encaminhamento_data = $record['data_aso'];
//
//            $formulario_id = Formulario::withoutGlobalScopes()->whereTitulo('Exames')->whereEmpresaId($empresa_id)->first()->id;
        }
    });

//    dd($dados[0]);
    DB::commit();


    die();
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
