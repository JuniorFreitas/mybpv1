<?php

namespace App\Http\Controllers;

use App\Models\MensagemChat;
use App\Models\User;
use DB;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index()
    {
        return view('g.chat.index');
    }

    public function show(Request $request, User $empresa)
    {

        //select DISTINCT para_id from mensagem_chats where de_id= auth()->id() and para_id != auth()->id()
        $recentesPara = DB::table('mensagem_chats')->distinct('para_id')->where('de_id', auth()->id())
            ->where('para_id', '!=', auth()->id())->select(['para_id'])->get()->pluck('para_id');
        //select DISTINCT de_id from mensagem_chats where para_id= auth()->id() and de_id != auth()->id()
        $recentesDe = DB::table('mensagem_chats')->distinct('de_id')->where('para_id', auth()->id())
            ->where('de_id', '!=', auth()->id())->select(['de_id'])->get()->pluck('de_id');

        $listaRecentes = $recentesPara->concat($recentesDe)->unique()->toArray();

        //Pegar a mensagens mais recente de cada contato desse
        $mensagens = collect();


        foreach ($listaRecentes as $contato_id) {
            // De mim para o contato...
            $msg = MensagemChat::whereDeId(auth()->id())->whereParaId($contato_id)->orderByDesc('created_at')->take(1)->first();
            if ($msg) {
                $mensagens[] = $msg->id;
            }
            // Do contato para mim....
            $msg = MensagemChat::whereDeId($contato_id)->whereParaId(auth()->id())->orderByDesc('created_at')->take(1)->first();
            if ($msg) {
                $mensagens[] = $msg->id;
            }

        }
        // select DISTINCT de_id from mensagem_chats where para_id=1 and de_id != 1

        //Aplicando Cache
        if (!\Cache::get("contatosEmpresa" . auth()->user()->empresa_id)) {
            \Cache::rememberForever("contatosEmpresa" . auth()->user()->empresa_id, function () {
                return User::select(['id', 'nome', 'empresa_id'])
                    ->whereAtivo(true)
                    ->whereIn("tipo", [User::ADMINISTRADOR, User::FUNCIONARIO])->whereEmpresaId(auth()->user()->empresa_id)
                    ->whereNotIn('id', [auth()->id()])
                    ->orderBy('nome')->get();
            });
        }

        return response()->json([
            'contatos' => \Cache::get("contatosEmpresa" . auth()->user()->empresa_id),
            'eu' => User::getUser(['id', 'nome', 'empresa_id']),
            'mensagens' => MensagemChat::whereIn('id', $mensagens)->orderBy('created_at')->distinct()->get()
        ], 200);
    }

    public function buscarContato(Request $request)
    {
        $consulta = User::whereAtivo(true)
            ->whereTipo(User::ADMINISTRADOR)
            ->whereEmpresaId(auth()->user()->empresa_id)
            ->whereNotIn('id', [auth()->id()])
            ->orderBy('nome');

        $busca = $request->query('busca');
        if ($busca != '') {
            $consulta->where('nome', 'like', '%' . $busca . '%');
        }

        return $consulta->get();
    }

}
