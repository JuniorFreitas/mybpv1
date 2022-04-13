<?php

namespace App\Http\Controllers;

use App\Events\Chat\MensagemChatEvent;
use App\Models\MensagemChat;
use App\Models\User;
use Event;
use Illuminate\Http\Request;
use MasterTag\DataHora;

class MensagemChatController extends Controller {
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, User $empresa) {

        $dadosValidados = \Validator::make($request->all(), [
            'para_id' => 'min:1|numeric',
            'grupo_id' => 'nullable|min:1|umeric',
            'mensagem' => 'required|min:1',
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao enviar a mensagem',
                'erros' => $dadosValidados->errors()
            ], 400);
        }
        try {
            \DB::beginTransaction();

            $dados=$request->only(['para_id','grupo_id','mensagem']);
            $dados['tipo']=MensagemChat::TIPO_TEXT;

            $mensagem = MensagemChat::create($dados);
            $mensagem->load('De','Para');
            \DB::commit();

            Event::dispatch(new MensagemChatEvent($mensagem,MensagemChatEvent::INSERT));

            return response()->json($mensagem, 201);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['msg' => $e->getMessage()], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\MensagemChat $mensagemChat
     * @return \Illuminate\Http\Response
     */
    public function show(MensagemChat $mensagemChat) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\MensagemChat $mensagemChat
     * @return \Illuminate\Http\Response
     */
    public function edit(MensagemChat $mensagemChat) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\MensagemChat $mensagemChat
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, MensagemChat $mensagemChat) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\MensagemChat $mensagemChat
     * @return \Illuminate\Http\Response
     */
    public function destroy(MensagemChat $mensagemChat) {
        //
    }

    public function visualizarMensagem(Request $request, User $empresa){

        $agora = new DataHora();
        if(MensagemChat::whereIn('id',$request->input('lista'))
                ->whereParaId(auth()->id())->count() == count($request->input('lista'))){

            \DB::beginTransaction();
            MensagemChat::whereIn('id',$request->input('lista'))
                ->wherePara_id(auth()->id())
                ->update([
                    'datahora_visto'=>$agora->dataHoraInsert(),
                    'visto'=>true
                ]);

            \DB::commit();
            $lista = MensagemChat::whereIn('id',$request->input('lista'))->whereParaId(auth()->id())->get();

            Event::dispatch(new MensagemChatEvent($lista,MensagemChatEvent::VISTO));

            return response()->json(['mensagens'=>$lista], 200);

        }

    }

    public function carregarMaisMensagens(Request $request, User $empresa){
        //sleep(2);
        $mensagens=collect();
        $busca = MensagemChat::whereDeId(auth()->id())->whereParaId($request->contato_id)->orderByDesc('created_at')
            ->where('id','<',$request->input('ultimo_id'))
            ->take(5)->get();

        $mensagens = $mensagens->concat($busca);

        $busca = MensagemChat::whereDeId($request->contato_id)->whereParaId(auth()->id())->orderByDesc('created_at')
            ->where('id','<',$request->input('ultimo_id'))
            ->take(5)->get();
        $mensagens = $mensagens->concat($busca);

        return response()->json([
            'mensagens'=>$mensagens
        ],200);




    }
}


