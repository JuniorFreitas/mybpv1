<?php

use App\Classes\ZapNotificacao;
use App\Http\Controllers\Api\CboController;
use App\Models\CartaOferta;
use App\Models\Sistema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('convocacao/{resposta}/{hash}/', [\App\Http\Controllers\IntermitenteController::class, "respostaConvocacao"])->name('respostaConvocacao');

Route::middleware('apitoken')->post('envia-whats', function (Request $request) {
    //     $arquivo =  Sistema::convertBase2(CartaOferta::checklistArquivo('equatorialservicos'), true);
    try {
        (new ZapNotificacao())->enviar([
            'enviado_id' => 1,
            'telefone' => preg_replace('/[^0-9]/', '', $request->telefone),
            'mensagem' => $request->mensagem,
            'anexo' => $request->anexo
        ]);
        return response()->json([
            'msg' => __('msg.ENVIADO_FILA'),
            'status' => __('msg.SUCESSO')
        ]);
    } catch (Exception $exception) {
        Log::error("Error ao enviar o whatsapp " . $exception->getMessage());
        return response()->json(['msg' => __('msg.HOUVE_UM_ERRO')], 400);
    }

    //    'json' => [
    //        "enviado_id" => 104,
    //        "telefone" => env('APP_ENV') == 'local' ? "5598999023762" : $dados['telefone'],
    //        "mensagem" => $dados['mensagem'],
    //        "sistema" => "sgi"
    //    ]
});


Route::middleware('api')->get('/login', function (Request $request) {
    return response()->json(['erro' => 'Não autorizado'], 403);
})->name("api.login.unauthorized");

Route::middleware('api')
    ->post('/login', [\App\Http\Controllers\Api\LoginController::class, 'login'])->name("api.login");


//Route::middleware('auth:api')->group(function () {
Route::middleware(['api', 'auth:sanctum', 'usuario.ativo'])->group(function () {
    Route::get('/logout', [\App\Http\Controllers\Api\LoginController::class, 'logout'])->name("logout");
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    //Route::group(['middleware' => ['auth', 'habilidades', 'horario_ativo'], 'as' => 'g.'], function () {

    //    Route::apiResource('empresas', "EmpresasController", ['parameters' => ['empresas' => 'empresa']])->middleware('can:empresa');


    Route::apiResource('habilidades', \App\Http\Controllers\Api\HabilidadeController::class)->middleware('can.sanctum:habilidades');

    Route::put('usuarios/{usuario}/ativa-desativa', [\App\Http\Controllers\Api\UsuarioController::class, "ativaDesativa"])->middleware('can.sanctum:usuario_ativar_desativar');
    Route::apiResource('usuarios', \App\Http\Controllers\Api\UsuarioController::class)->middleware('can.sanctum:usuarios');
});


Route::group(['as' => 'v1', 'prefix' => 'v1/{empresa_slug}', 'middleware' => ['apitoken']], function () {
    Route::get('/', [\App\Http\Controllers\Api\IntegracaoVagaAbertaController::class, 'getVagasAbertasByEmpresa']);
    Route::get('/dadosempresa', [\App\Http\Controllers\Api\IntegracaoVagaAbertaController::class, 'getDadosEmpresa']);
});

Route::middleware(['api', 'apitoken'])->group(function () {
    Route::get('cbos', [CboController::class, 'index']);
    Route::get('cbos/{codigo}', [CboController::class, 'show'])->where('codigo', '[0-9]+');
});

Route::middleware(['api'])
    ->prefix('v2/integracao')
    ->group(function () {
        Route::get('media/{apelido}/logotipo', [\App\Http\Controllers\Api\IntegracaoSpaMediaController::class, 'logotipo'])
            ->where('apelido', '[a-zA-Z0-9_-]+')
            ->name('api.integracao_spa.media.logotipo');

        $apelidoPattern = '(?!media$)[a-zA-Z0-9_-]+';

        Route::middleware('apitoken')->group(function () use ($apelidoPattern) {
            Route::get('/', [\App\Http\Controllers\Api\IntegracaoSpaV2Controller::class, 'empresasAtivas']);
            Route::get('/empresas-ativas', [\App\Http\Controllers\Api\IntegracaoSpaV2Controller::class, 'empresasAtivas']);
            Route::get('{apelido}/vagas-abertas/{vaga_aberta_id}/{slug_vaga_aberta}', [\App\Http\Controllers\Api\IntegracaoSpaV2Controller::class, 'vagaAbertaDetalhe'])
                ->where('apelido', $apelidoPattern)
                ->whereNumber('vaga_aberta_id')
                ->where('slug_vaga_aberta', '[a-zA-Z0-9\-]+');
            Route::get('{apelido}/vagas-abertas', [\App\Http\Controllers\Api\IntegracaoSpaV2Controller::class, 'vagasAbertasPaginadas'])
                ->where('apelido', $apelidoPattern);
            Route::post('{apelido}/busca-curriculo', [\App\Http\Controllers\Api\IntegracaoSpaCurriculoController::class, 'buscaCurriculo'])
                ->where('apelido', $apelidoPattern);
            Route::post('{apelido}/busca-cpf', [\App\Http\Controllers\Api\IntegracaoSpaCurriculoController::class, 'buscaCpf'])
                ->where('apelido', $apelidoPattern);
            Route::post('{apelido}/cadastra-curriculo', [\App\Http\Controllers\Api\IntegracaoSpaCurriculoController::class, 'cadastraCurriculo'])
                ->where('apelido', $apelidoPattern);
            Route::get('{apelido}', [\App\Http\Controllers\Api\IntegracaoSpaV2Controller::class, 'empresaComPreview'])
                ->where('apelido', $apelidoPattern);
        });
    });

Route::group(['as' => 'vaga'], function () {
    Route::get('{empresa_slug}', [\App\Http\Controllers\Api\VagaAbertaController::class, 'getVagasAbertasByEmpresa']);
    Route::get('{empresa_slug}/{vaga_aberta_id}', [\App\Http\Controllers\Api\VagaAbertaController::class, 'getVagaAberta']);
    //    Route::get('{empresa_id}/vaga-aberta/{vaga_aberta_id}', [\App\Http\Controllers\Api\VagaAbertaController::class, 'index']);
    Route::post('{empresa_id}/vaga-aberta/{vaga_aberta_id}', [\App\Http\Controllers\Api\VagaAbertaController::class, 'atualizar']);

    Route::post('busca-curriculo', [\App\Http\Controllers\Api\VagaAbertaController::class, 'buscaCurriculo']);
    Route::post('busca-cpf', [\App\Http\Controllers\Api\VagaAbertaController::class, 'buscaCpf']);
    Route::post('cadastra-curriculo', [\App\Http\Controllers\Api\VagaAbertaController::class, 'store']);
});


Route::group(['as' => 'projetos', 'prefix' => '{empresa_slug}'], function () {
    Route::post('projetos/lista', [\App\Http\Controllers\Api\ProjetoController::class, 'index'])->middleware('apitoken');
});

//Integra SGI com MYBP
Route::post('integra-sgi-mybp-aceita-carta-oferta', [\App\Http\Controllers\Api\IntegraSgiMybpController::class, 'aceiteCartaOferta'])
    ->middleware('apitoken');

Route::fallback(function () {
    return response()->json(['msg' => 'Não encontrado', 'success' => false], 404);
});
