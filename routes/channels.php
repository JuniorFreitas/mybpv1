<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Authorization: usuário autenticado só escuta canais da própria empresa
| ou canais privados do próprio usuário.
|
*/

// Weekly report ===============================================================================================
Broadcast::channel('weekly-report.quadros.{empresaId}', function (User $user, int $empresaId) {
    return (int) $user->empresa_id === (int) $empresaId ? $user : false;
});

Broadcast::channel('weekly-report.listas.{empresaId}', function (User $user, int $empresaId) {
    return (int) $user->empresa_id === (int) $empresaId ? $user : false;
});

Broadcast::channel('weekly-report.tarefas.checklists.itens.{empresaId}', function (User $user, int $empresaId) {
    return (int) $user->empresa_id === (int) $empresaId ? $user : false;
});

Broadcast::channel('weekly-report.tarefas.checklists.{empresaId}', function (User $user, int $empresaId) {
    return (int) $user->empresa_id === (int) $empresaId ? $user : false;
});

Broadcast::channel('weekly-report.tarefas.comentarios.{empresaId}', function (User $user, int $empresaId) {
    return (int) $user->empresa_id === (int) $empresaId ? $user : false;
});

Broadcast::channel('weekly-report.tarefas.anexos.{empresaId}', function (User $user, int $empresaId) {
    return (int) $user->empresa_id === (int) $empresaId ? $user : false;
});

Broadcast::channel('weekly-report.tarefas.{empresaId}', function (User $user, int $empresaId) {
    return (int) $user->empresa_id === (int) $empresaId ? $user : false;
});

Broadcast::channel('weekly-report.log.{empresaId}', function (User $user, int $empresaId) {
    return (int) $user->empresa_id === (int) $empresaId ? $user : false;
});

// Chat ===============================================================================================
// {empresa} = empresa_id (inteiro), não model User
Broadcast::channel('chat.{empresa}.mensagens.contato.{contato}', function (User $user, int $empresa, int $contato) {
    if ((int) $user->empresa_id === $empresa && (int) $user->id === $contato) {
        return $user;
    }

    return false;
});

Broadcast::channel('chat.{empresa}', function (User $user, int $empresa) {
    if ((int) $user->empresa_id === $empresa) {
        return $user;
    }

    return false;
});

Broadcast::channel('notificacoes.{usuario}', function (User $user, int $usuario) {
    if ((int) $user->id === $usuario) {
        return $user;
    }

    return false;
});
