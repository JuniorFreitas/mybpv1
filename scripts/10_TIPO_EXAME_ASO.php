<?php

use App\Models\AdmissaoAso;
use App\Models\AlternativaFormulario;
use App\Models\ExameFuncionario;
use App\Models\ClienteConfig;
use App\Models\RespostaAlternativas;
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
    $examesFuncionarios = ExameFuncionario::withoutGlobalScopes()
//        ->whereNull('exame_tipo_id')
        ->select(['id', 'respostas', 'empresa_id', 'feedback_id', 'exame_tipo_id'])->get();
    $ef = collect($examesFuncionarios);

    $ef->each(function ($item) {
        \App\Models\Examesesmt::withoutGlobalScopes()->where('exame_funcionario_id', $item->id)->update(['feedback_id' => $item->feedback_id]);
        if (is_null($item->exame_tipo_id)) {

            $tipoOrdem = AlternativaFormulario::withoutGlobalScopes()->whereNome('Tipo de ordem')->whereEmpresaId($item->empresa_id)->first();
            $tipoExame = \App\Models\ExameTipo::whereLabel(RespostaAlternativas::whereId($item->respostas['alternativa_id_' . $tipoOrdem['id']]['valor'])->first()->label)->first();
            ExameFuncionario::withoutGlobalScopes()->where('id', $item->id)->update(['exame_tipo_id' => $tipoExame->id]);
        }
        echo $item->id . PHP_EOL;
        DB::commit();
    });
} catch (\Exception $e) {
//    Log::debug("Erro ao atualizar o Tipo de Exame do Funcionário | ".$e->getMessage());
    echo $e->getMessage() . PHP_EOL;
    DB::rollBack();
}
