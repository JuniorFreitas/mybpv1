<?php

use App\Classes\ZapNotificacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware('apitoken')->post('envia-whats', function (Request $request) {
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
});


Route::middleware('api')->get('/login', function (Request $request) {
    return response()->json(['erro' => 'Não autorizado'], 403);
})->name("login");

Route::middleware('api')
    ->post('/login', [\App\Http\Controllers\Api\LoginController::class, 'login'])->name("login");


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

Route::group(['as' => 'vaga'], function () {
    Route::get('{empresa_slug}', [\App\Http\Controllers\Api\VagaAbertaController::class, 'getVagasAbertasByEmpresa']);
    Route::get('{empresa_slug}/{vaga_aberta_id}', [\App\Http\Controllers\Api\VagaAbertaController::class, 'getVagaAberta']);
//    Route::get('{empresa_id}/vaga-aberta/{vaga_aberta_id}', [\App\Http\Controllers\Api\VagaAbertaController::class, 'index']);
    Route::post('{empresa_id}/vaga-aberta/{vaga_aberta_id}', [\App\Http\Controllers\Api\VagaAbertaController::class, 'atualizar']);
    Route::post('busca-curriculo', [\App\Http\Controllers\Api\VagaAbertaController::class, 'buscaCurriculo']);
    Route::post('busca-cpf', [\App\Http\Controllers\Api\VagaAbertaController::class, 'buscaCpf']);
    Route::post('cadastra-curriculo', [\App\Http\Controllers\Api\VagaAbertaController::class, 'store']);
});


Route::fallback(function () {
    return response()->getStatusCode();
});
