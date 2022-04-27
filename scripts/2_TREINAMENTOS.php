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

$lista[] = ['label' => 'EBTV', 'descricao' => 'TODOS OS FUNCIONÁRIOS', 'prazo_parada' => 89, 'prazo_fixo' => 364, 'ordem' => 1, 'ativo' => true, 'empresa_id' => $empresa_id];
$lista[] = ['label' => 'TRABALHOS A QUENTE', 'descricao' => 'TODOS OS FUNCIONÁRIOS', 'prazo_parada' => 89, 'prazo_fixo' => 364, 'ordem' => 6, 'ativo' => true, 'empresa_id' => $empresa_id];
$lista[] = ['label' => 'PREVENÇÃO DE QUEDAS', 'descricao' => 'TODOS OS FUNCIONÁRIOS', 'prazo_parada' => 89, 'prazo_fixo' => 364, 'ordem' => 7, 'ativo' => true, 'empresa_id' => $empresa_id];
$lista[] = ['label' => 'ESPAÇO CONFINADO', 'descricao' => 'TODOS OS FUNCIONÁRIOS', 'prazo_parada' => 89, 'prazo_fixo' => 364, 'ordem' => 8, 'ativo' => true, 'empresa_id' => $empresa_id];
$lista[] = ['label' => 'PROTEÇÃO DE MÁQUINAS', 'descricao' => 'TODOS OS FUNCIONÁRIOS', 'prazo_parada' => 89, 'prazo_fixo' => 1094, 'ordem' => 9, 'ativo' => true, 'empresa_id' => $empresa_id];
$lista[] = ['label' => 'NR35', 'descricao' => 'TODOS MAO DE OBRA DIRETA', 'prazo_parada' => 728, 'prazo_fixo' => 728, 'ordem' => 3, 'ativo' => true, 'empresa_id' => $empresa_id];
$lista[] = ['label' => 'NR33', 'descricao' => 'TODOS MAO DE OBRA DIRETA', 'prazo_parada' => 364, 'prazo_fixo' => 364, 'ordem' => 2, 'ativo' => true, 'empresa_id' => $empresa_id];
$lista[] = ['label' => 'NORMA 3260,69,70 N10', 'descricao' => 'TODOS OS FUNCIONÁRIOS', 'prazo_parada' => 89, 'prazo_fixo' => 364, 'ordem' => 10, 'ativo' => true, 'empresa_id' => $empresa_id];
$lista[] = ['label' => 'NORMA 3260,69,70 N10 AUTOR', 'descricao' => 'OBRIGATÓRIO AOS ELETRICITÁRIOS', 'prazo_parada' => 728, 'prazo_fixo' => 728, 'ordem' => 11, 'ativo' => true, 'empresa_id' => $empresa_id];
$lista[] = ['label' => 'NORMA 3260,69,70 N10 QUALIF', 'descricao' => 'OBRIGATÓRIO AOS ELETRICITÁRIOS', 'prazo_parada' => 728, 'prazo_fixo' => 728, 'ordem' => 12, 'ativo' => true, 'empresa_id' => $empresa_id];
$lista[] = ['label' => 'DEFENSIVA', 'descricao' => 'OBRIGATÓRIO AOS MOTORISTAS', 'prazo_parada' => 1094, 'prazo_fixo' => 1094, 'ordem' => 13, 'ativo' => true, 'empresa_id' => $empresa_id];
$lista[] = ['label' => 'PONTE ROLANTE', 'descricao' => 'SOLICITADOS', 'prazo_parada' => 364, 'prazo_fixo' => 364, 'ordem' => 14, 'ativo' => true, 'empresa_id' => $empresa_id];
$lista[] = ['label' => 'PLATAFORMA MÓVEL', 'descricao' => 'SOLICITADOS', 'prazo_parada' => 364, 'prazo_fixo' => 364, 'ordem' => 15, 'ativo' => true, 'empresa_id' => $empresa_id];
$lista[] = ['label' => 'OPER EQUIPAM ESPECIF', 'descricao' => 'AOS OPERADORES MUCK, RETRO', 'prazo_parada' => 364, 'prazo_fixo' => 364, 'ordem' => 16, 'ativo' => true, 'empresa_id' => $empresa_id];
$lista[] = ['label' => 'CRP', 'descricao' => 'SOLICITADOS', 'prazo_parada' => 1094, 'prazo_fixo' => 1094, 'ordem' => 17, 'ativo' => true, 'empresa_id' => $empresa_id];
$lista[] = ['label' => 'RIGGER(MOV. DE CARGAS)', 'descricao' => 'OBRIGATÓRIO A MUCK, AJUDANTE MUCK, RIGGER', 'prazo_parada' => 728, 'prazo_fixo' => 728, 'ordem' => 18, 'ativo' => true, 'empresa_id' => $empresa_id];
$lista[] = ['label' => 'PREV. FATALIDAD', 'descricao' => NULL, 'prazo_parada' => NULL, 'prazo_fixo' => NULL, 'ordem' => 19, 'ativo' => true, 'empresa_id' => $empresa_id];
$lista[] = ['label' => 'RECONHECIMENTO DE PERIGO', 'descricao' => NULL, 'prazo_parada' => NULL, 'prazo_fixo' => NULL, 'ordem' => 20, 'ativo' => true, 'empresa_id' => $empresa_id];
$lista[] = ['label' => 'FIBRAS MINERAIS', 'descricao' => NULL, 'prazo_parada' => NULL, 'prazo_fixo' => NULL, 'ordem' => 21, 'ativo' => true, 'empresa_id' => $empresa_id];
$lista[] = ['label' => 'ROMPEDOR PNEUMÁTIUCO/HIDRÁULICO - TOMPSON, TOP TEC, BLOOCK', 'descricao' => NULL, 'prazo_parada' => NULL, 'prazo_fixo' => NULL, 'ordem' => 22, 'ativo' => true, 'empresa_id' => $empresa_id];
$lista[] = ['label' => 'OPERAÇÃO DE MUNCK', 'descricao' => NULL, 'prazo_parada' => 728, 'prazo_fixo' => 728, 'ordem' => 23, 'ativo' => true, 'empresa_id' => $empresa_id];
$lista[] = ['label' => 'OPERAÇÃO DE GUINDASTE', 'descricao' => NULL, 'prazo_parada' => 728, 'prazo_fixo' => 728, 'ordem' => 24, 'ativo' => true, 'empresa_id' => $empresa_id];
$lista[] = ['label' => 'BOB CAT', 'descricao' => NULL, 'prazo_parada' => 728, 'prazo_fixo' => 728, 'ordem' => 25, 'ativo' => true, 'empresa_id' => $empresa_id];
$lista[] = ['label' => 'EMPILHADEIRA', 'descricao' => NULL, 'prazo_parada' => 728, 'prazo_fixo' => 728, 'ordem' => 26, 'ativo' => true, 'empresa_id' => $empresa_id];
$lista[] = ['label' => 'TESTE DE SELAGEM', 'descricao' => NULL, 'prazo_parada' => NULL, 'prazo_fixo' => NULL, 'ordem' => 27, 'ativo' => true, 'empresa_id' => $empresa_id];
$lista[] = ['label' => 'SUPERVISOR DE ESPAÇO CONFINADO', 'descricao' => NULL, 'prazo_parada' => 364, 'prazo_fixo' => 364, 'ordem' => 5, 'ativo' => true, 'empresa_id' => $empresa_id];
$lista[] = ['label' => 'PROD.QUIMICOS / PREV. QUEIMADURAS QUÍMICAS', 'descricao' => NULL, 'prazo_parada' => 364, 'prazo_fixo' => 364, 'ordem' => 28, 'ativo' => true, 'empresa_id' => $empresa_id];
$lista[] = ['label' => 'PRIMEIROS SOCORROS', 'descricao' => 'SOLICITADOS', 'prazo_parada' => 89, 'prazo_fixo' => 364, 'ordem' => 29, 'ativo' => true, 'empresa_id' => $empresa_id];
$lista[] = ['label' => 'NR20', 'descricao' => 'SOLICITADO PARA OPERADOR DE COMBOIO E LUBRIFICADOR', 'prazo_parada' => 1034, 'prazo_fixo' => 1034, 'ordem' => 30, 'ativo' => true, 'empresa_id' => $empresa_id];
$lista[] = ['label' => 'USO DE MANGUEIRA', 'descricao' => 'SOMENTE PARA AJUDANTES', 'prazo_parada' => 89, 'prazo_fixo' => 364, 'ordem' => 31, 'ativo' => true, 'empresa_id' => $empresa_id];
$lista[] = ['label' => 'NR10 ', 'descricao' => NULL, 'prazo_parada' => 728, 'prazo_fixo' => 728, 'ordem' => 4, 'ativo' => true, 'empresa_id' => $empresa_id];
$lista[] = ['label' => 'VEIM ', 'descricao' => NULL, 'prazo_parada' => 364, 'prazo_fixo' => 364, 'ordem' => 32, 'ativo' => true, 'empresa_id' => $empresa_id];
$lista[] = ['label' => 'CTPV', 'descricao' => NULL, 'prazo_parada' => 1094, 'prazo_fixo' => 1094, 'ordem' => 33, 'ativo' => true, 'empresa_id' => $empresa_id];

try {
    echo "Iniciando...\n";
    DB::beginTransaction();
    foreach ($lista as $item) {
        if (\App\Models\Vencimento::withoutGlobalScopes()->whereLabel($item['label'])->whereEmpresaId($item['empresa_id'])->count() == 0) {
            echo "Criando... {$item['label']} para a empresa $Empresa->razao_social \n";
            DB::table('vencimentos')->insert($item);
        }else{
            echo "Existe... {$item['label']} para a empresa $Empresa->razao_social \n";
        }
    }
    echo "Fim...\n";
    DB::commit();
} catch (Exception $e) {
    DB::rollBack();
    echo $e->getTraceAsString();
}

