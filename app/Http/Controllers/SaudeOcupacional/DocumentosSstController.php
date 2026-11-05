<?php

namespace App\Http\Controllers\SaudeOcupacional;

use App\Domain\SaudeOcupacional\Documentos\DocumentosSstService;
use App\Http\Controllers\Controller;
use App\Http\Requests\SaudeOcupacional\EnviarDocumentoSstRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Programas → Documentos SST: repositório versionado (PCMSO, PGR, LTCAT, laudos, ordens de serviço, ASOs).
 * Arquivos em disco privado: só saem por aqui, com permissão e trilha de acesso.
 */
class DocumentosSstController extends Controller
{
    public function __construct(private readonly DocumentosSstService $documentos)
    {
    }

    public function index()
    {
        return view('g.saude-ocupacional.documentos.index');
    }

    public function dados(Request $request): JsonResponse
    {
        $filtros = $request->validate([
            'tipo' => ['nullable', 'string', 'max:30'],
            'situacao' => ['nullable', 'string', 'max:15'],
            'busca' => ['nullable', 'string', 'max:100'],
            'feedback_id' => ['nullable', 'integer', 'min:1'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        return response()->json($this->documentos->listar($this->empresaId(), $filtros, $request->user()));
    }

    public function opcoes(Request $request): JsonResponse
    {
        return response()->json($this->documentos->opcoes($request->user()));
    }

    public function show(Request $request, int $id): JsonResponse
    {
        return response()->json($this->documentos->detalhe($this->empresaId(), $id, $request->user()));
    }

    public function store(EnviarDocumentoSstRequest $request): JsonResponse
    {
        $documento = $this->documentos->criar($this->empresaId(), $request->safe()->except('arquivo'), $request->file('arquivo'), $request->user());

        return response()->json(['id' => $documento->id, 'mensagem' => 'Documento enviado.'], 201);
    }

    public function novaVersao(EnviarDocumentoSstRequest $request, int $id): JsonResponse
    {
        $versao = $this->documentos->novaVersao($this->empresaId(), $id, $request->safe()->except('arquivo'), $request->file('arquivo'), $request->user());

        return response()->json(['mensagem' => 'Versão ' . $versao->versao . ' enviada.'], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $dados = $request->validate([
            'titulo' => ['required', 'string', 'max:200'],
            'responsavel' => ['nullable', 'string', 'max:150'],
            'observacao' => ['nullable', 'string', 'max:1000'],
        ]);
        $this->documentos->atualizar($this->empresaId(), $id, $dados, $request->user());

        return response()->json(['mensagem' => 'Documento atualizado.']);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->documentos->arquivar($this->empresaId(), $id, $request->user());

        return response()->json(['mensagem' => 'Documento arquivado. O arquivo continua guardado.']);
    }

    public function arquivo(Request $request, int $versaoId): StreamedResponse
    {
        $visualizar = $request->boolean('visualizar');
        $aberto = $this->documentos->abrir(
            $this->empresaId(),
            $versaoId,
            $request->user(),
            $visualizar ? DocumentosSstService::ACESSO_VISUALIZAR : DocumentosSstService::ACESSO_BAIXAR
        );
        $arquivo = $aberto['arquivo'];
        $disco = Storage::disk($arquivo->disco);
        abort_unless($disco->exists($arquivo->file), 404, 'Arquivo não encontrado.');

        $headers = [
            'Content-Type' => $aberto['mime'],
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ];

        // Só PDF e imagem abrem no navegador; Word/Excel sempre baixam.
        $inline = $visualizar && in_array($aberto['mime'], ['application/pdf', 'image/jpeg', 'image/png'], true);

        return $inline
            ? $disco->response($arquivo->file, $aberto['nome'], $headers, 'inline')
            : $disco->download($arquivo->file, $aberto['nome'], $headers);
    }

    private function empresaId(): int
    {
        return (int) auth()->user()->empresa_id;
    }
}
