<?php

namespace App\Http\Controllers;

use App\Models\CartaOferta;
use App\Models\Cliente;
use Illuminate\Http\Request;
use MasterTag\DataHora;

class CartaOfertaController extends Controller
{
    public function index(Request $request)
    {
        $Empresa = Cliente::select(['id', 'apelido'])->withoutGlobalScopes()->whereApelido($request->segment(1))->first();

        if (!$Empresa) {
            return abort(404);
        }

        $cartaOferta = CartaOferta::withoutGlobalScopes()->where('empresa_id', $Empresa->id)
            ->where('token', $request->segment(3))
            ->with([
                'Curriculo' => function ($q) {
                    $q->withoutGlobalScopes()
                        ->select([
                            'id', 'nome', 'cpf', 'nascimento', 'rg', 'orgao_expeditor', 'email', 'vaga_pretendida']);
                },
                'vagaAberta' => function ($q) {
                    $q->select(['id', 'vaga_id', 'titulo', 'municipio_id'])->withoutGlobalScopes()
                        ->with(['Vaga' => function ($q) {
                            $q->withoutGlobalScopes();
                        }]);
                },
                'vagaProjeto' => function ($q) {
                    $q->withoutGlobalScopes();
                },
                'Empresa' => function ($q) {
                    $q->withoutGlobalScopes();
                },
            ])
            ->first()->toArray();

        return view('cartaoferta.index', compact('cartaOferta', 'Empresa'));
    }

    public function salvarCartaOferta(Request $request)
    {

        $Empresa = Cliente::select(['id', 'apelido'])->withoutGlobalScopes()->whereApelido($request->segment(1))->first();

        if (!$Empresa) {
            return abort(404);
        }

        $cartaOferta = \DB::table('curriculo_carta_oferta')->where('empresa_id', $Empresa->id)
            ->where('token', $request->segment(3));

        if ($cartaOferta->first()->status == CartaOferta::STATUS_AGUARDANDO_RH) {
            return response()->json(['message' => 'Carta de oferta já enviada!'], 422);
        }

        if ($cartaOferta->first()->status == CartaOferta::STATUS_EXPIRADO) {
            return response()->json(['message' => 'Carta de oferta expirada'], 422);
        }

        if ($cartaOferta->first()->status == CartaOferta::STATUS_PENDENTE_ANEXO) {

            $logs = $cartaOferta->first()->logs;

            $logs[] = [
                'data' => (new DataHora())->dataHoraInsert(),
                'mensagem' => 'Carta de oferta enviada pelo o candidato',
                'status' => 'Enviado pelo candidato',
                'usuario' => \DB::table('users')->where('id', $cartaOferta->first()->curriculo_id)->first()->nome
            ];

            $cartaOferta->update([
                'status' => CartaOferta::STATUS_AGUARDANDO_RH,
                'arquivo_id' => $request->arquivo['id'],
                'logs' => $logs
            ]);

            return response()->json(['message' => 'Carta de oferta enviada com sucesso!'], 200);
        }

        return response()->json(['message' => 'Nao encontrado'], 404);
    }
}
