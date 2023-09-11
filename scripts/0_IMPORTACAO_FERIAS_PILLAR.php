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
use MasterTag\DataHora;
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


$nomeArquivo = public_path('importacao_ferias_pillar.xlsx');

$import = new Admissaoimport;
\Excel::import($import, $nomeArquivo);

$empresa_id = 39765;
$user_id = $empresa_id;
Auth::loginUsingId($user_id);
$count = 0;

$itens = [];
$achados = [];

$dados = $import->dados->map(function ($line) use ($empresa_id, &$itens, &$achados, &$count, &$query) {
    $admissao_id = explode('|', $line['nome'])[0];
    $periodo_aquisitivo = explode('|', $line['periodo_aquisitivo']);
    $periodo_aquisitivo_id = $periodo_aquisitivo[0];
    $periodo_aquisitivo_ultima_data = explode('/', $periodo_aquisitivo[1])[1];
    $data_saida = Date::excelToDateTimeObject($line['data_saida'])->format('Y-m-d');
    $qnt_dias_adquido = $line['qnt_dias_adquirido'];
    $qnt_faltas = $line['qnt_faltas'];
    $afastado = $line['afastado'] == 'sim';

    $data_retorno = \Carbon\Carbon::create($data_saida)->addDays($qnt_dias_adquido)->format('Y-m-d');
    $tem_falta = $qnt_faltas > 0 ? 1 : 0;

    $dataAdmissao = DB::table('admissoes')->select(['data_admissao'])->where('id', $admissao_id)->first()->data_admissao;

    $date = new DataHora($dataAdmissao);
    $ultimoAnoPeriodoAquisitivo = $periodo_aquisitivo_ultima_data . '-' . $date->mes() . '-' . $date->dia();
    $newDate = new DataHora($ultimoAnoPeriodoAquisitivo);
    $newDate->addDia(330);
    $ultima_data = $newDate->dataInsert();

    $itens[] = [
        'empresa_id' => $empresa_id,
        'admissao_id' => $admissao_id,
        'periodo_aquisitivo_id' => $periodo_aquisitivo_id,
        'data_saida' => $data_saida,
        'data_retorno' => $data_retorno,
        'ultima_data' => $ultima_data,
        'qnt_dias' => $qnt_dias_adquido,
        'dias_saldo' => 0,
        'tem_faltas' => $tem_falta,
        'qnt_faltas' => $qnt_faltas,
        'solicitante_id' => $empresa_id,
        'obs_solicitante' => null,
        'data_solicitacao' => now(),
        'gestor_id' => $empresa_id,
        'gestor_aprovacao_id' => $empresa_id,
        'obs_gestor' => null,
        'status_aprovacao_gestor' => \App\Models\Ferias::STATUS_APROVADO,
        'data_aprovacao_gestor' => now(),
        'rh_aprovacao_id' => $empresa_id,
        'obs_rh' => null,
        'status_aprovacao_rh' => \App\Models\Ferias::STATUS_APROVADO,
        'data_aprovacao_rh' => now(),
        'status_ferias' => \App\Models\Ferias::STATUS_AGUARDANDO,
        'data_status_ferias' => now(),
        'ferias_prevista_id' => null,
        'aprovado_via_script' => 1,
        'created_at' => now(),
        'updated_at' => now(),
        'quem_deletou_id' => null,
        'deleted_at' => null,
        'abono_pecuniario' => 0,
        'adiantamento_decimo_terceiro' => 0,
    ];

    $busca = DB::table('ferias')->where('admissao_id', $admissao_id)->where('periodo_aquisitivo_id', $periodo_aquisitivo);

    if ($busca->first()) {
        $busca->update([
            'quem_deletou_id' => $empresa_id,
            'deleted_at' => now(),
        ]);
    }

});

DB::table('ferias')->insert($itens);

Artisan::call('mybp:ferias');

die("FIM");
