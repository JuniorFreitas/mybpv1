<?php

use Illuminate\Support\Facades\DB;
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

//unset($argv[0]);
//$empresa_id = $argv[1];
$empresa_id = 60263;

$emp_jor = [
    60263 => [
        16 => [
            'escala_id' => 11,
            'duracao' => 480, // 8h
            'dias_semana' => [1,2,3,4,5], // seg - sex
            'jornada_correcao' => 48
        ],
        48 => [
            'escala_id' => 11,
            'duracao' => 240, // 4h
            'dias_semana' => [6], // sab
            'jornada_correcao' => 16
        ],
        46 => [
            'escala_id' => 12,
            'duracao' => 540, // 9h
            'dias_semana' => [1,2,3,4], // seg - qui
            'jornada_correcao' => 53
        ],
        53 => [
            'escala_id' => 12,
            'duracao' => 480, // 8h
            'dias_semana' => [5], // sex
            'jornada_correcao' => 46
        ],
        18 => [
            'escala_id' => 13,
            'duracao' => 540, // 9h
            'dias_semana' => [1,2,3,4], // seg - qui
            'jornada_correcao' => 51
        ],
        51 => [
            'escala_id' => 13,
            'duracao' => 480, // 8h
            'dias_semana' => [5], // sex
            'jornada_correcao' => 18
        ],
        19 => [
            'escala_id' => 14,
            'duracao' => 480, // 8h
            'dias_semana' => [1,2,3,4,5], // seg - sex
            'jornada_correcao' => 52
        ],
        52 => [
            'escala_id' => 14,
            'duracao' => 240, // 4h
            'dias_semana' => [6], // sab
            'jornada_correcao' => 19
        ]
    ]
];

$cont_pontos = 1;

$data_inicio = "2023-01-01 00:00:00";
$dia_anterior = (new DataHora());
$dia_anterior->subtrairDia(1);
$data_fim = $dia_anterior->dataInsert()." 23:59:59";

$ponto_eletronico = DB::select("SELECT *
FROM ponto_eletronicos
WHERE empresa_id = $empresa_id
  AND ocorrencia_id = 4
  AND created_at >= '".$data_inicio."' AND created_at <= '".$data_fim."'
  AND jornada_id in (16,18,19,46,48,51,52,53)
  AND duracao_normal is not null
ORDER BY created_at ASC");


foreach ($ponto_eletronico as $pe) {
    $id_ponto = $pe->id;
    $base_data_ponto = (new DataHora($pe->created_at));
    $data_ponto = $base_data_ponto->dataInsert();
    $dia_semana_data_ponto = (int) $base_data_ponto->diaSemanaNum();
    $jornada_id = $pe->jornada_id;
    $duracao = $pe->duracao;
    $duracao_normal = $pe->duracao_normal;
    $duracao_extra = $pe->duracao_extra;

    $dias_jornada = (array) $emp_jor[$empresa_id][$jornada_id]['dias_semana'];
    if(!in_array($dia_semana_data_ponto, $dias_jornada)){
        $hora_registrada = $duracao_normal + $duracao_extra;
        $id_jornada_correta = (int) $emp_jor[$empresa_id][$jornada_id]['jornada_correcao'];
        $duracao_jornada_correta = $emp_jor[$empresa_id][$id_jornada_correta]['duracao'];
        $saldo = $hora_registrada-$duracao_jornada_correta;


        echo "### DADOS PONTO ###\n\n";

        echo "Dentro da jornada: NAO\n".
             "ID: ".$id_ponto."\n".
             "Data: ".$base_data_ponto->dataHoraCompleta()."\n".
             "Dia: ".$base_data_ponto->diaSemanaExt()."\n".
             "Jornada: ".$jornada_id."\n".
             "Semana jornada: ".implode(",", (array) $emp_jor[$empresa_id][$jornada_id]['dias_semana'])."\n".
             "Semana ponto: ".$dia_semana_data_ponto."\n".
             "Durancao jornada: ".$duracao."\n".
             "Durancao ponto: ".$emp_jor[$empresa_id][$jornada_id]['duracao']."\n".
             "Durancao normal: ".$duracao_normal."\n".
             "Durancao extra: ".$duracao_extra."\n".
             "Jornada Correta: ".$id_jornada_correta."\n".
             "Semana Correta: ".implode(",", (array) $emp_jor[$empresa_id][$id_jornada_correta]['dias_semana'])."\n".
             "Hora Registrada: ".$hora_registrada."\n",
             "Duração Jorn. Correta: ".$duracao_jornada_correta."\n",
             "Saldo:".$saldo."\n\n";

        echo "### CORPO UPDATE ###\n\n";

        $body_update[$empresa_id][$id_jornada_correta] = [
            'duracao' => $duracao_jornada_correta,
            'duracao_normal' => $hora_registrada,
            'duracao_extra' => $saldo,
            'jornada_id' => $id_jornada_correta,
        ];

        print_r($body_update[$empresa_id][$id_jornada_correta]);

        echo "-------------------------------------------\n\n";

        try{
            DB::table('ponto_eletronicos')->where('id', $id_ponto)->update($body_update[$empresa_id][$id_jornada_correta]);
        } catch (Exception $exception) {
            echo $exception->getMessage();
        }

        $cont_pontos++;
    }
}
\Log::info('PONTOS COM ERRO CORRIGIDOS: ' . $cont_pontos);
