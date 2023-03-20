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
\Excel::import($import, public_path('documento_curriculos_2023.xlsx'));

$empresa_id = 63122;
$user_id = $empresa_id;

$dados = $import->dados;

if ($dados->count() == 0) {
    return response()->json([
        'msg' => 'Nenhum registro encontrado',
        "status" => 'error'
    ], 400);
}

$dados = $dados->toArray();

try {
    $count = 0;
    DB::beginTransaction();
    foreach ($dados as $item) {
        Auth::loginUsingId($user_id);
        $arquivo = \App\Models\Arquivo::create($item);
        $curriculo_id = DB::table('curriculos')->select('id')->where('cpf', $item['cpf'])->first()->id;
        DB::table('documentos_curriculos')->insert([
            'arquivo_id' => $arquivo->id,
            'curriculo_id' => $curriculo_id,
            'tipo' => 'foto3x4'
        ]);
        echo 'Importacao realizada com sucesso do CPF ' . $item['cpf'] . "\n";
        echo 'Importacao realizada com sucesso do curriculo ' . $curriculo_id . "\n";
        echo 'Importacao realizada com sucesso do arquivo ' . $arquivo->id . "\n";
        echo "--------------------------------------------\n";
        DB::commit();
    }

    \Log::info('Importação realizada com sucesso da Empresa ');
    (new ZapNotificacao())->enviar([
        'enviado_id' => $user_id,
        'telefone' => '5598999023762',
        'mensagem' => 'Importação realizada com sucesso das fotos de '. $count .' colaboradores da Empresa ' . $empresa_id
    ]);
    return response()->json(['msg' => 'Importação realizada com sucesso'], 201);
} catch (\Exception $e) {
    DB::rollback();
    \Log::error($e->getMessage() . ' - ' . $e->getLine());

    echo $e->getMessage() . ' - ' . $e->getLine() . "\n";
//    return response()->json(['error' => $e->getMessage() . ' - ' . $e->getLine()], 500);
}
