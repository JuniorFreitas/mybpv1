<?php
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
$empresa_id = $argv[1];
$Empresa = DB::table('clientes')->find($empresa_id);

if (strtolower($argv[2]) == 'padrao') {
    $lista[] = ['empresa_id' => $empresa_id, 'label' => 'Fixo', 'ativo' => true];
    $lista[] = ['empresa_id' => $empresa_id, 'label' => 'Parada', 'ativo' => true];
    try {
        echo "Iniciando...\n";
        DB::beginTransaction();
        foreach ($lista as $item) {
            if (\App\Models\Pcmso::withoutGlobalScopes()->whereLabel($item['label'])->whereEmpresaId($item['empresa_id'])->count() == 0) {
                echo "Criando... {$item['label']} para a empresa $Empresa->razao_social \n";
                DB::table('pcmsos')->insert($item);
            }
        }
        DB::commit();
    } catch (Exception $e) {
        DB::rollBack();
        echo $e->getTraceAsString();
    }
} else {
    $lista[] = ['empresa_id' => $empresa_id, 'label' => 'Fixo', 'ativo' => true];
    $lista[] = ['empresa_id' => $empresa_id, 'label' => 'Parada', 'ativo' => true];

    unset($argv[1]);

    foreach ($argv as $item) {
        $lista[] = ['empresa_id' => $empresa_id, 'label' => $item, 'ativo' => true];
    }
    try {
        echo "Iniciando...\n";
        DB::beginTransaction();
        foreach ($lista as $item) {
            if (\App\Models\Pcmso::withoutGlobalScopes()->whereLabel($item['label'])->whereEmpresaId($item['empresa_id'])->count() == 0) {
                echo "Criando... {$item['label']} para a empresa $Empresa->razao_social \n";
                DB::table('pcmsos')->insert($item);
            } else {
                echo "Existe... {$item['label']} para a empresa $Empresa->razao_social \n";
            }
        }
        echo "Fim...\n";
        DB::commit();
    } catch (Exception $e) {
        DB::rollBack();
        echo $e->getTraceAsString();
    }
}
