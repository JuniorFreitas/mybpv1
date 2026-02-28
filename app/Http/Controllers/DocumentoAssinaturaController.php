<?php

namespace App\Http\Controllers;

use App\Jobs\AssinaturaDigital\JobEnvioEmailAssinatura;
use App\Models\Arquivo;
use App\Models\DocumentoAssinaturaEvento;
use App\Models\DocumentoParaAssinatura;
use App\Services\AssinaturaDigital\AssinaturaDigitalService;
use Illuminate\Http\Request;

class DocumentoAssinaturaController extends Controller
{
    protected AssinaturaDigitalService $service;

    public function __construct(AssinaturaDigitalService $service)
    {
        $this->service = $service;
    }

    /**
     * Exibe a página de gerenciamento de documentos para assinatura.
     */
    public function indexView()
    {
        return view('g.administracao.documento-assinatura.index');
    }

    /**
     * Lista documentos para assinatura da empresa (multi-tenant por empresa_id do usuário).
     */
    public function index(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;
        $filtros = $request->only(['status', 'tipo_documento', 'solicitante_id', 'signatario', 'data_inicio', 'data_fim', 'id', 'per_page', 'page']);
        $filtros['per_page'] = $filtros['per_page'] ?? $request->get('porPagina', 15);
        $lista = $this->service->listar($empresaId, $filtros);

        return response()->json([
            'atual' => $lista->currentPage(),
            'ultima' => $lista->lastPage(),
            'total' => $lista->total(),
            'dados' => ['itens' => $lista->items()],
        ]);
    }

    /**
     * Lista solicitantes (usuários que enviaram ao menos um documento) para o filtro.
     */
    public function solicitantes()
    {
        $empresaId = auth()->user()->empresa_id;
        $ids = DocumentoParaAssinatura::where('empresa_id', $empresaId)
            ->whereNotNull('solicitante_id')
            ->distinct()
            ->pluck('solicitante_id');
        $usuarios = \App\Models\User::whereIn('id', $ids)
            ->orderBy('nome')
            ->get(['id', 'nome'])
            ->map(fn ($u) => ['id' => $u->id, 'nome' => $u->nome ?? $u->name ?? '—']);
        return response()->json($usuarios);
    }

    /**
     * Detalhe de um documento com signatários e eventos (auditoria).
     * Aceita id numérico ou token (hash) na URL.
     */
    public function show($idOrToken)
    {
        $empresaId = auth()->user()->empresa_id;
        $doc = DocumentoParaAssinatura::with(['signatarios', 'eventos', 'arquivo', 'arquivoAssinado', 'solicitante'])
            ->where('empresa_id', $empresaId)
            ->porIdOuToken($idOrToken)
            ->firstOrFail();

        return response()->json($doc);
    }

    /**
     * Download do documento assinado (PDF com marca d'água). Apenas quando status = concluído.
     * Aceita id numérico ou token (hash) na URL.
     */
    public function downloadAssinado($idOrToken)
    {
        $empresaId = auth()->user()->empresa_id;
        $doc = DocumentoParaAssinatura::with('arquivoAssinado')
            ->where('empresa_id', $empresaId)
            ->porIdOuToken($idOrToken)
            ->firstOrFail();

        if ($doc->status !== DocumentoParaAssinatura::STATUS_CONCLUIDO) {
            abort(404, 'Documento ainda não está concluído. Aguarde todas as assinaturas.');
        }
        if (!$doc->arquivo_assinado_id || !$doc->arquivoAssinado) {
            abort(404, 'Documento assinado não encontrado.');
        }

        $arquivo = $doc->arquivoAssinado;
        if (!$arquivo->disco || !$arquivo->file) {
            abort(404, 'Arquivo inválido.');
        }

        $user = auth()->user();
        $this->service->registrarEvento($doc->id, DocumentoAssinaturaEvento::EVENTO_DOWNLOAD, [
            'user_id' => $user->id,
            'nome' => $user->nome ?? $user->name ?? 'Sistema',
            'email' => $user->email ?? null,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return Arquivo::anexoDownload($arquivo->disco, $arquivo->file);
    }

    /**
     * Cancela um documento (rascunho ou em_assinatura).
     * Aceita id numérico ou token (hash) na URL.
     */
    public function cancelar(Request $request, $idOrToken)
    {
        $empresaId = auth()->user()->empresa_id;
        $doc = DocumentoParaAssinatura::where('empresa_id', $empresaId)->porIdOuToken($idOrToken)->first();
        if (!$doc) {
            return response()->json(['success' => false, 'message' => 'Documento não encontrado.'], 404);
        }
        $result = $this->service->cancelar($doc->id, $empresaId);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Reenvia e-mail de assinatura para os signatários do documento.
     * Aceita id numérico ou token (hash) na URL.
     */
    public function reenviarEmail($idOrToken)
    {
        $empresaId = auth()->user()->empresa_id;
        $doc = DocumentoParaAssinatura::where('empresa_id', $empresaId)->porIdOuToken($idOrToken)->first();
        if (!$doc) {
            return response()->json(['success' => false, 'message' => 'Documento não encontrado.'], 404);
        }
        if ($doc->status !== DocumentoParaAssinatura::STATUS_EM_ASSINATURA) {
            return response()->json(['success' => false, 'message' => 'Só é possível reenviar e-mail para documentos em assinatura.'], 422);
        }

        JobEnvioEmailAssinatura::dispatch($doc->id);

        $user = auth()->user();
        $this->service->registrarEvento($doc->id, DocumentoAssinaturaEvento::EVENTO_REENVIADO, [
            'user_id' => $user->id,
            'nome' => $user->nome ?? $user->name ?? 'Sistema',
        ]);

        return response()->json(['success' => true, 'message' => 'E-mail de assinatura reenviado para os signatários.']);
    }
}
