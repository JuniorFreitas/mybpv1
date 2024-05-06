<?php

use App\Models\VagasAbertas;
use App\Models\Vaga;
use Illuminate\Support\Facades\DB;

require dirname(__DIR__) . '/vendor/autoload.php';

$app = require_once dirname(__DIR__) . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$kernel->terminate($request, $response);

ini_set('memory_limit', '-1');
ini_set('max_execution_time', '-1');

try {
    $dados_update = [
        'filial' => false,
        'centro_custo_filial_id' => null
    ];

    $empresa_id = 71953;


    $feedbackIds = DB::table('resultado_integrados as ri')
        ->join('mybp.feedback_curriculos as fc', function ($join) {
            $join->on('ri.feedback_id', '=', 'fc.id')
                ->where('fc.empresa_id', 71953);
        })
        ->pluck('ri.feedback_id');


    foreach ($feedbackIds as $feedbackId) {
        $admissao = DB::table('admissoes')
            ->where('feedback_id', $feedbackId)
            ->first();

        if (!$admissao) {
            DB::table('admissoes')
                ->insert([
                    'feedback_id' => $feedbackId,
                    'filial' => false,
                    'status' => \App\Models\Admissao::STATUS_ADMISSAO_ENCAMINHADOEXAME
                ]);
        }
    }

} catch (Exception $exception) {
    echo $exception->getMessage();
}

