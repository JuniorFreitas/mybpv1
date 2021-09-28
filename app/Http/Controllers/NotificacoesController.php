<?php

namespace App\Http\Controllers;

use App\Models\Notificacao;
use App\Models\User;
use Illuminate\Http\Request;

class NotificacoesController extends Controller
{
    public function index() {
        //return view('g.chat.index');
    }

    public function getUpdate(Request $request,User $usuario) {
        //Usuario aqui é só pro segurança
        return Notificacao::whereUserId(auth()->id())->whereUserId($usuario->id)
            //->whereVisto(false)
            ->orderByDesc('created_at')->take(5)->get();

    }

    public function marcarVisto(Request $request,User $usuario) {
        //Usuario aqui é só pro segurança
        Notificacao::whereUserId(auth()->id())->whereUserId($usuario->id)
            ->whereVisto(false)
            ->whereIn('id',$request->lista)->update(['visto'=>true]);

        return response()->json(null,200);


    }
}
