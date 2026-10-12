<?php

namespace App\Http\Controllers;

use App\Models\CartaOferta;
use App\Models\Cliente;
use App\Services\CartaOferta\CartaOfertaAcessoPublicoService;
use App\Services\Marca\MarcaClienteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use MasterTag\DataHora;
use RuntimeException;

class CartaOfertaController extends Controller
{
    public function index(Request $request, CartaOfertaAcessoPublicoService $acesso, MarcaClienteService $marca)
    {
        $apelido = $request->segment(1);
        $publicId = $request->segment(3);

        $carta = $acesso->encontrarPorPublicId($apelido, $publicId);
        if (!$carta) {
            return abort(404);
        }

        $Empresa = $carta->empresa;
        $sessionKey = $acesso->sessionKey($publicId);
        $autenticado = (bool) session($sessionKey);

        if ($autenticado) {
            $payload = $acesso->payloadAutenticado($carta);
        } elseif (session($acesso->sessionKeyCpfConfirmado($publicId))) {
            $payload = $acesso->payloadDesafioPendente($carta);
        } else {
            $payload = $acesso->payloadPublicoMinimo($carta);
        }

        return view('cartaoferta.index', [
            'Empresa' => $Empresa,
            'marca' => $marca->dados($Empresa),
            'payload' => $payload,
            'publicId' => $publicId,
        ]);
    }

    public function solicitarCodigo(Request $request, CartaOfertaAcessoPublicoService $acesso)
    {
        $request->validate([
            'cpf' => 'nullable|string|min:11|max:20',
        ]);

        $publicId = $request->segment(3);
        $carta = $acesso->encontrarPorPublicId($request->segment(1), $publicId);
        if (!$carta) {
            return response()->json(['message' => 'Carta oferta não encontrada.'], 404);
        }

        $cpfKey = $acesso->sessionKeyCpfConfirmado($publicId);

        try {
            if ($request->filled('cpf')) {
                $resultado = $acesso->solicitarCodigo($carta, $request->input('cpf'));
                session([$cpfKey => true]);
            } elseif (session($cpfKey)) {
                $resultado = $acesso->reenviarCodigo($carta);
            } else {
                return response()->json(['message' => 'Informe seu CPF para receber o código.'], 422);
            }

            return response()->json($resultado, 200);
        } catch (RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'acesso' => session($cpfKey) ? $acesso->statusAcesso($carta) : null,
            ], 422);
        }
    }

    public function validarCodigo(Request $request, CartaOfertaAcessoPublicoService $acesso)
    {
        $request->validate([
            'codigo' => 'required|string|min:8|max:12',
        ]);

        $publicId = $request->segment(3);
        if (!session($acesso->sessionKeyCpfConfirmado($publicId))) {
            return response()->json(['message' => 'Informe seu CPF para receber o código.'], 422);
        }

        $carta = $acesso->encontrarPorPublicId($request->segment(1), $publicId);
        if (!$carta) {
            return response()->json(['message' => 'Carta oferta não encontrada.'], 404);
        }

        try {
            $payload = $acesso->validarCodigo($carta, $request->input('codigo'));
            session([$acesso->sessionKey($publicId) => true]);
            session()->forget($acesso->sessionKeyCpfConfirmado($publicId));

            return response()->json($payload, 200);
        } catch (RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'acesso' => $acesso->statusAcesso($carta),
            ], 422);
        }
    }

    public function salvarCartaOferta(Request $request, CartaOfertaAcessoPublicoService $acesso)
    {
        $Empresa = Cliente::select(['id', 'apelido'])->withoutGlobalScopes()->whereApelido($request->segment(1))->first();
        if (!$Empresa) {
            return abort(404);
        }

        $publicId = $request->segment(3);
        if (!session($acesso->sessionKey($publicId))) {
            return response()->json(['message' => 'Autentique-se com CPF e código para continuar.'], 401);
        }

        $cartaOferta = \DB::table('curriculo_carta_oferta')
            ->where('empresa_id', $Empresa->id)
            ->where(function ($q) use ($publicId) {
                $q->where('public_id', $publicId)->orWhere('token', $publicId);
            });

        $row = $cartaOferta->first();
        if (!$row) {
            return response()->json(['message' => 'Nao encontrado'], 404);
        }

        if ($row->status == CartaOferta::STATUS_AGUARDANDO_RH) {
            return response()->json(['message' => 'Carta de oferta já enviada!'], 422);
        }

        if ($row->status == CartaOferta::STATUS_EXPIRADO) {
            return response()->json(['message' => 'Carta de oferta expirada'], 422);
        }

        if ($row->status == CartaOferta::STATUS_PENDENTE_ANEXO) {
            $request->validate([
                'arquivo.id' => 'required',
            ]);

            $logsAntigo = json_decode($row->logs, true) ?: [];
            $logsAntigo[] = [
                'data' => (new DataHora())->dataHoraInsert(),
                'mensagem' => 'Carta de oferta enviada pelo candidato',
                'status' => 'Enviado pelo candidato',
                'usuario' => \DB::table('users')->where('id', $row->curriculo_id)->value('nome'),
            ];

            usort($logsAntigo, function ($a, $b) {
                return strtotime($b['data']) - strtotime($a['data']);
            });

            $cartaOferta->update([
                'status' => CartaOferta::STATUS_AGUARDANDO_RH,
                'arquivo_id' => $request->arquivo['id'],
                'logs' => json_encode($logsAntigo),
            ]);

            return response()->json(['message' => 'Carta de oferta enviada com sucesso!'], 200);
        }

        return response()->json(['message' => 'Nao encontrado'], 404);
    }

    /**
     * Download do checklist PDF da empresa (arquivo privado no storage).
     */
    public function downloadChecklist(string $empresa)
    {
        $key = CartaOferta::checklistStorageKey($empresa);
        $disk = Storage::disk(config('filesystems.default'));

        if (!$disk->exists($key)) {
            abort(404, 'Checklist não encontrado para esta empresa.');
        }

        $apelido = pathinfo($key, PATHINFO_FILENAME);

        return $disk->download($key, $apelido . '.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
