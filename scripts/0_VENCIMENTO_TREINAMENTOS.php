<?php

use App\Classes\ZapNotificacao;
use App\Imports\Admissaoimport;
use Illuminate\Support\Facades\Auth;
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

unset($argv[0]);
$import = new Admissaoimport;
\Excel::import($import, public_path('treinamentos_montisol_2023.xlsx'));

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
        $feedback_id = \App\Models\FeedbackCurriculo::select(['id'])->whereHas('curriculo', function ($query) use ($item) {
            $query->where('cpf', $item['cpf']);
        })->first()->id;

        \App\Models\ResultadoIntegrado::whereFeedbackId($feedback_id)->update([
            'encaminhado_treinamento' => true,
            'encaminhado_treinamento_data' => $item['data_treinamento'],
        ]);
        echo 'Importacao realizada com sucesso do FEEDBACK ID ' . $feedback_id . "\n";
        $dados_treinamento = [
            'feedback_id' => $feedback_id,
            'cadastrou' => $user_id,
            'tipo' => $item['tipo']
        ];
        $treinamento = \App\Models\Treinamento::firstOrCreate($dados_treinamento, $dados_treinamento);
        echo 'Importacao realizada com sucesso do TREINAMENTO ID ' . $treinamento->id . "\n";
        echo 'Importacao realizada com sucesso do VENCIMENTO ID ' . $item['vencimento_id'] . "\n";

        $dados_vencimento = [
            'treinamento_id' => $treinamento->id,
            'vencimento_id' => $item['vencimento_id'],
            'data_vencimento' => $item['data_vencimento'],
            'data_treinamento' => $item['data_treinamento'],
            'numero_fat' => $item['numero_fat']
        ];

        $treinamento_vencimento = \App\Models\Pivot\TreinamentoVencimento::firstOrCreate($dados_vencimento,$dados_vencimento);

        echo 'Importacao realizada com sucesso do CPF ' . $item['cpf'] . "\n";
        echo 'Importacao realizada com sucesso do feedback ' . $feedback_id . "\n";
        echo 'COUNT - ' . $count++ . "\n";
        echo "--------------------------------------------\n";
        DB::commit();
    }

    \Log::info('Importação realizada com sucesso da Empresa ');
    (new ZapNotificacao())->enviar([
        'enviado_id' => $user_id,
        'telefone' => '5598999023762',
        'mensagem' => 'Importação realizada com sucesso dos treinamentos '. $count .' dos colaboradores da Empresa ' . $empresa_id
    ]);
    return response()->json(['msg' => 'Importação realizada com sucesso'], 201);
} catch (\Exception $e) {
    DB::rollback();
    \Log::error($e->getMessage() . ' - ' . $e->getLine(). ' - ' . $e->getFile());

    echo $e->getMessage() . ' - ' . $e->getLine() . "\n";
//    return response()->json(['error' => $e->getMessage() . ' - ' . $e->getLine()], 500);
}
