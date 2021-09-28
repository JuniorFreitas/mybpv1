<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

/*Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});*/

// Weekly report ===============================================================================================
//Quadros
Broadcast::channel('weekly-report.quadros.{empresa}', function (User $user, User $empresa) {
    if($user->empresa_id === $empresa->id){
        return $user;
    }
});
// Listas de tarefas
Broadcast::channel('weekly-report.listas.{empresa}', function (User $user, User $empresa) {
    if($user->empresa_id === $empresa->id){
        return $user;
    }
});


// Tarefas > Checklists > Itens
Broadcast::channel('weekly-report.tarefas.checklists.itens.{empresa}', function (User $user, User $empresa) {
    if($user->empresa_id === $empresa->id){
        return $user;
    }
});

// Tarefas > Checklists
Broadcast::channel('weekly-report.tarefas.checklists.{empresa}', function (User $user, User $empresa) {
    if($user->empresa_id === $empresa->id){
        return $user;
    }
});

// Anexos das tarefas
Broadcast::channel('weekly-report.tarefas.anexos.{empresa}', function (User $user, User $empresa) {
    if($user->empresa_id === $empresa->id){
        return $user;
    }
});
// Tarefas
Broadcast::channel('weekly-report.tarefas.{empresa}', function (User $user, User $empresa) {
    if($user->empresa_id === $empresa->id){
        return $user;
    }
});
//logs
Broadcast::channel('weekly-report.log.{empresa}', function (User $user, User $empresa) {
    if($user->empresa_id === $empresa->id){
        return $user;
    }
});

// Chat ===============================================================================================
Broadcast::channel('chat.{empresa}.mensagens.contato.{contato}', function (User $user, User $empresa, User $contato) {
    if($user->empresa_id === $empresa->id && $contato->id== auth()->id()){
        return $user;
    }
});

Broadcast::channel('chat.{empresa}', function (User $user, User $empresa) { //serve só para saber quais pessoas da emrpesa estão on-line ou não
    if($user->empresa_id === $empresa->id){
        return $user;
    }
});

Broadcast::channel('notificacoes.{usuario}', function (User $user,User $usuario) { //serve só para saber quais pessoas da emrpesa estão on-line ou não
    if($usuario->id === auth()->id()){ // só escuta se for o mesmo que está autenticado
        return $user;
    }
});
