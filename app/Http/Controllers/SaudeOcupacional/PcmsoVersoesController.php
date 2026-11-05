<?php

namespace App\Http\Controllers\SaudeOcupacional;

use App\Domain\SaudeOcupacional\Pcmso\ExamesCatalogoService;
use App\Domain\SaudeOcupacional\Pcmso\PcmsoPdfService;
use App\Domain\SaudeOcupacional\Pcmso\PcmsoService;
use App\Http\Controllers\Controller;
use App\Jobs\SaudeOcupacional\JobArquivarPcmsoSst;
use App\Models\ExameTipo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Programas → PCMSO: programas, versões, exames por cargo, catálogo de exames e exames por risco.
 * (O cadastro antigo de nomes de PCMSO continua em Cadastro → PCMSO.)
 */
class PcmsoVersoesController extends Controller
{
    public function __construct(
        private readonly PcmsoService $pcmso,
        private readonly ExamesCatalogoService $exames,
        private readonly PcmsoPdfService $pdf
    ) {
    }

    public function index()
    {
        return view('g.saude-ocupacional.pcmso.index');
    }

    public function dados(): JsonResponse
    {
        $empresaId = $this->empresaId();

        return response()->json([
            'programas' => $this->pcmso->listarProgramas($empresaId),
            'opcoes' => $this->pcmso->opcoes($empresaId),
        ]);
    }

    public function criarPrograma(Request $request): JsonResponse
    {
        $label = (string) $request->validate(['label' => ['required', 'string', 'max:120']])['label'];

        return response()->json(['id' => $this->pcmso->criarPrograma($this->empresaId(), $label), 'mensagem' => 'Programa criado.'], 201);
    }

    public function versoes(int $pcmsoId): JsonResponse
    {
        return response()->json(['versoes' => $this->pcmso->listarVersoes($this->empresaId(), $pcmsoId)]);
    }

    public function criarRascunho(Request $request, int $pcmsoId): JsonResponse
    {
        $versao = $this->pcmso->criarRascunho($this->empresaId(), $pcmsoId, $request->user());

        return response()->json(['id' => $versao->id, 'mensagem' => 'Versão ' . $versao->versao . ' criada em rascunho.'], 201);
    }

    public function detalhe(int $id): JsonResponse
    {
        return response()->json($this->pcmso->detalhe($this->empresaId(), $id));
    }

    public function cabecalho(Request $request, int $id): JsonResponse
    {
        $dados = $request->validate([
            'saude_medico_id' => ['nullable', 'integer'],
            'observacao' => ['nullable', 'string', 'max:1000'],
        ]);
        $this->pcmso->salvarCabecalho($this->empresaId(), $id, $dados);

        return response()->json(['mensagem' => 'Dados da versão salvos.']);
    }

    public function salvarCargo(Request $request, int $id, int $vagaId): JsonResponse
    {
        $dados = $request->validate([
            'exames' => ['present', 'array', 'max:60'],
            'exames.*.exame_id' => ['required', 'integer', 'min:1'],
            'exames.*.exame_tipo_ids' => ['required', 'array', 'min:1'],
            'exames.*.exame_tipo_ids.*' => ['integer'],
            'exames.*.obrigatorio' => ['boolean'],
            'exames.*.unidade' => ['nullable', 'in:anos,dias'],
            'exames.*.valor' => ['nullable', 'integer', 'min:1', 'max:730'],
            'exames.*.origem' => ['nullable', 'string', 'max:10'],
            'exames.*.observacao' => ['nullable', 'string', 'max:500'],
        ], [
            'exames.*.exame_tipo_ids.required' => 'Marque em quais tipos de ASO cada exame é feito.',
        ]);
        $this->pcmso->salvarCargo($this->empresaId(), $id, $vagaId, $dados['exames']);

        return response()->json(['mensagem' => 'Exames do cargo salvos.']);
    }

    public function sugerir(int $id): JsonResponse
    {
        $resultado = $this->pcmso->sugerirPeloPgr($this->empresaId(), $id);

        return response()->json($resultado + [
            'mensagem' => $resultado['exames_adicionados'] || $resultado['cargos']
                ? sprintf('%d exame(s) sugerido(s) em %d cargo(s) pelo PGR.', $resultado['exames_adicionados'], $resultado['cargos'])
                : 'Nada a sugerir: o rascunho já tem os exames dos riscos do PGR.',
        ]);
    }

    public function publicar(Request $request, int $id): JsonResponse
    {
        $dados = $request->validate([
            'vigencia_inicio' => ['required', 'string', 'max:10'],
            'vigencia_fim' => ['nullable', 'string', 'max:10'],
        ]);
        $versao = $this->pcmso->publicar($this->empresaId(), $id, $dados['vigencia_inicio'], $dados['vigencia_fim'] ?? null, $request->user());
        // O PDF da versão publicada vai para Documentos SST (em fila).
        JobArquivarPcmsoSst::dispatch($this->empresaId(), $versao->id, (int) $request->user()->id);

        return response()->json(['mensagem' => 'PCMSO versão ' . $versao->versao . ' publicado. O PDF será guardado em Documentos SST.']);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->pcmso->excluirRascunho($this->empresaId(), $id, $request->user());

        return response()->json(['mensagem' => 'Rascunho excluído.']);
    }

    public function pdf(Request $request, int $id)
    {
        $versao = $this->pcmso->versao($this->empresaId(), $id);
        $pdf = $this->pdf->gerar($this->empresaId(), $id, (string) ($request->user()?->nome ?? ''));

        return $pdf->stream('PCMSO versao ' . $versao->versao . ' ' . Str::slug((string) optional($versao->Programa()->withoutGlobalScopes()->first())->label) . '.pdf');
    }

    public function salvarExame(Request $request, ?int $exameId = null): JsonResponse
    {
        $dados = $request->validate([
            'label' => ['required', 'string', 'max:120'],
            'codigo_esocial' => ['nullable', 'string', 'max:10'],
            'ativo' => ['sometimes', 'boolean'],
        ]);
        $exame = $this->exames->salvar($this->empresaId(), $exameId, $dados);

        return response()->json(['id' => $exame->id, 'mensagem' => 'Exame salvo.'], $exameId ? 200 : 201);
    }

    public function examesDoRisco(Request $request, int $riscoId): JsonResponse
    {
        $dados = $request->validate([
            'exames' => ['present', 'array', 'max:30'],
            'exames.*.exame_id' => ['required', 'integer', 'min:1'],
            'exames.*.exame_tipo_ids' => ['required', 'array', 'min:1'],
            'exames.*.exame_tipo_ids.*' => ['integer'],
        ]);
        $this->exames->salvarExamesDoRisco(
            $this->empresaId(),
            $riscoId,
            $dados['exames'],
            ExameTipo::query()->where('ativo', true)->pluck('id')->map(fn ($id) => (int) $id)->all()
        );

        return response()->json(['mensagem' => 'Exames do risco salvos.']);
    }

    private function empresaId(): int
    {
        return (int) auth()->user()->empresa_id;
    }
}
