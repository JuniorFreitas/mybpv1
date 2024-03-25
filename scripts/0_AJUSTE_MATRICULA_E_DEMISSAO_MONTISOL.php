<?php

use App\Classes\ZapNotificacao;
use App\Imports\Admissaoimport;
use App\Models\Admissao;
use App\Models\AdmissaoAso;
use App\Models\Curriculo;
use App\Models\Sistema;
use App\Models\TelefoneCurriculo;
use App\Models\User;
use App\Models\VagasAbertas;
use App\Rules\AreaEmpresaRules;
use App\Rules\CpfValidoEmpresaRules;
use App\Rules\VagaAbertaEmpresaRules;
use App\Rules\VerificaCpfEmpresaRules;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Support\Facades\Validator;


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
\Excel::import($import, base_path('scripts/xls/montisol_matriculas.xlsx'));

$empresa_id = 63122;
$user_id = $empresa_id;
Auth::loginUsingId($user_id);
$count = 0;
/*
$dados = $import->dados->map(function ($line) use ($empresa_id, &$count) {
    $count++;
    try {
        Admissao::whereId($line['admissao_id'])->update([
            'matricula' => $line['matricula'],
        ]);
    } catch (\Exception $e) {
        dd($e->getMessage());
    }

    echo "Linha: " . $count . PHP_EOL;
});*/


$importD = new Admissaoimport;
\Excel::import($importD, base_path('scripts/xls/montisol_demissao.xlsx'));

$dadosD = $importD->dados->map(function ($line) use ($empresa_id, &$count) {
    $adm = Admissao::select(['feedback_id', 'id', 'status'])->where('id', $line['admissao_id'])->first();

    if (!is_null($adm) && is_null($adm->Demissao)) {
        $adm->update([
            'status' => Admissao::STATUS_DEMITIDO,
            'matricula' => $line['matricula']
        ]);
        \App\Models\Demissao::create([
            'feedback_id' => $adm->feedback_id,
            'cipa' => true,
            'data_desmobilizacao' => Date::excelToDateTimeObject($line['dt_demissao'])->format('d/m/Y'),
            'motivo_rescisao_id' => 1,
            'outro_motivo' => null,
            'tipo_aviso_id' => 1,
            'solicitado_por' => 'IMPORTACAO SCRIPT',
            'comentario' => 'IMPORTACAO SCRIPT',
            'user_id' => $empresa_id
        ]);
    }


//    dd();
//    $count++;
//    try {
//        Admissao::whereId($line['admissao_id'])->update([
//            'demissao' => $line['demissao'],
//        ]);
//    } catch (\Exception $e) {
//        dd($e->getMessage());
//    }
//
//    echo "Linha: " . $count . PHP_EOL;
});
