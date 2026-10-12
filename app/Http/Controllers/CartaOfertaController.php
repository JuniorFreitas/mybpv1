<?php

namespace App\Http\Controllers;

use App\Models\CartaOferta;
use App\Models\Cliente;
use App\Services\AcessoPublico\AuditoriaAcessoService;
use App\Services\CartaOferta\CartaOfertaAcessoPublicoService;
use App\Services\Marca\MarcaClienteService;
use App\Services\Preadmissao\AnexoPreAdmissaoAcessoService;
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
            app(AnexoPreAdmissaoAcessoService::class)->liberarCandidato($request, (int) $carta->curriculo_id);
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
                // "Já recebi o código": só confere o CPF, sem enviar outro código.
                $jaTemCodigo = $request->boolean('ja_tenho_codigo');
                $resultado = $jaTemCodigo
                    ? $acesso->confirmarCpf($carta, $request->input('cpf'))
                    : $acesso->solicitarCodigo($carta, $request->input('cpf'));
                session([$cpfKey => true]);
                $evento = $jaTemCodigo ? AuditoriaAcessoService::EVENTO_CPF_CONFIRMADO : AuditoriaAcessoService::EVENTO_CODIGO_SOLICITADO;
            } elseif (session($cpfKey)) {
                $resultado = $acesso->reenviarCodigo($carta);
                $evento = AuditoriaAcessoService::EVENTO_CODIGO_REENVIADO;
            } else {
                return response()->json(['message' => 'Informe seu CPF para receber o código.'], 422);
            }
            $this->auditar($request, $carta, $evento, ['email_mascarado' => $resultado['email_mascarado']]);

            return response()->json($resultado, 200);
        } catch (RuntimeException $e) {
            $this->auditar($request, $carta, AuditoriaAcessoService::EVENTO_CODIGO_NEGADO, [
                'cpf' => $request->input('cpf'),
                'dados' => ['motivo' => $e->getMessage()],
            ]);

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
            app(AnexoPreAdmissaoAcessoService::class)->liberarCandidato($request, (int) $carta->curriculo_id);
            session()->forget($acesso->sessionKeyCpfConfirmado($publicId));
            $this->auditar($request, $carta, AuditoriaAcessoService::EVENTO_CODIGO_VALIDADO);

            return response()->json($payload, 200);
        } catch (RuntimeException $e) {
            $this->auditar($request, $carta, AuditoriaAcessoService::EVENTO_CODIGO_INVALIDO, [
                'dados' => ['motivo' => $e->getMessage()],
            ]);

            return response()->json([
                'message' => $e->getMessage(),
                'acesso' => $acesso->statusAcesso($carta),
            ], 422);
        }
    }

    protected function auditar(Request $request, CartaOferta $carta, string $evento, array $info = []): void
    {
        app(AuditoriaAcessoService::class)->registrar(
            CartaOfertaAcessoPublicoService::CONTEXTO,
            (int) $carta->empresa_id,
            $evento,
            $request,
            $info + ['registro_id' => $carta->id, 'curriculo_id' => $carta->curriculo_id]
        );
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

            // Só aceita o arquivo que o próprio candidato acabou de enviar (a chave vem do upload).
            $arquivoEnviado = \DB::table('arquivos')
                ->where('id', $request->input('arquivo.id'))
                ->where('disco', \App\Models\Arquivo::DISCO_DOCUMENTOS_PRE_ADMISSAO)
                ->where('chave', (string) $request->input('arquivo.chave'))
                ->where('chave', '!=', '')
                ->exists();
            if (!$arquivoEnviado) {
                return response()->json(['message' => 'Arquivo inválido. Anexe a carta novamente.'], 422);
            }

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

            app(AuditoriaAcessoService::class)->registrar(
                CartaOfertaAcessoPublicoService::CONTEXTO,
                (int) $row->empresa_id,
                AuditoriaAcessoService::EVENTO_CARTA_ENVIADA,
                $request,
                [
                    'registro_id' => $row->id,
                    'curriculo_id' => $row->curriculo_id,
                    'dados' => ['arquivo_id' => (int) $request->arquivo['id']],
                ]
            );

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
