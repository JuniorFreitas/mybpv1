<?php

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

//$empresa_id = 57861;
//$user_id = 58050;

$DB = DB::select("SELECT fec.id, fec.vaga_id, va.vaga_id
    FROM
    curriculos cur
        INNER JOIN feedback_curriculos as fec on cur.id = fec.curriculo_id
        INNER JOIN users as usu on cur.id = usu.id
        INNER JOIN resultado_integrados ri on fec.id = ri.feedback_id
        INNER JOIN vagas_abertas va on fec.vagas_abertas_id = va.id
");
//WHERE usu.empresa_id = 57861

$cont = 0;
foreach ($DB as $v) {
    try {
        DB::table('feedback_curriculos')->where('id', $v->id)->update([
            'vaga_id' => $v->vaga_id
        ]);
        echo "ID : " . $v->id . " | " . $cont++ . "\n";
    } catch (Exception $exception) {
        echo $exception->getMessage();
    }

}
