<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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


Route::get('{empresa_id}/vaga-aberta/{vaga_aberta_id}', [\App\Http\Controllers\Api\VagaAbertaController::class, 'index']);
Route::post('{empresa_id}/vaga-aberta/{vaga_aberta_id}', [\App\Http\Controllers\Api\VagaAbertaController::class, 'atualizar']);
Route::post('busca-curriculo', [\App\Http\Controllers\Api\VagaAbertaController::class, 'buscaCurriculo']);
Route::post('busca-cpf', [\App\Http\Controllers\Api\VagaAbertaController::class, 'buscaCpf']);
Route::post('cadastra-curriculo', [\App\Http\Controllers\Api\VagaAbertaController::class, 'store']);


Route::fallback(function () {
    return response()->getStatusCode();
});
