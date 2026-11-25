<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admissao\CadastraMassaAdmissaoRequest;
use App\Http\Requests\Admissao\DemitirViaPrivilegioRequest;
use App\Http\Requests\Admissao\ImportAdmissaoUploadRequest;
use App\Http\Requests\Admissao\StoreAdmissaoRequest;
use App\Http\Requests\Admissao\UpdateAdmissaoRequest;
use App\Models\Arquivo;
use App\Models\FeedbackCurriculo;
use App\Models\Sistema;
use App\Models\User;
use App\Services\Admissao\AdmissaoCadastraMassaService;
use App\Services\Admissao\AdmissaoCpfLookupService;
use App\Services\Admissao\AdmissaoDemissaoPrivilegioService;
use App\Services\Admissao\AdmissaoExportDispatcher;
use App\Services\Admissao\AdmissaoImportUploadService;
use App\Services\Admissao\AdmissaoListSelectsService;
use App\Services\Admissao\AdmissaoProcessoEditPresenter;
use App\Services\Admissao\AdmissaoProcessoFiltroQuery;
use App\Services\Admissao\AdmissaoProcessoListagemPresenter;
use App\Services\Admissao\AdmissaoStoreService;
use App\Services\Admissao\AdmissaoTabelaColunasService;
use App\Services\Admissao\AdmissaoUpdateService;
use App\Services\Treinamento\CarteiraImagemCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use MasterTag\DataHora;
use PDF;

class AdmissaoController extends Controller
{
    public function index()
    {
        return view('g.admissao.processo.index');
    }

    public function create(): Response
    {
        return response()->noContent();
    }

    public function store(StoreAdmissaoRequest $request): JsonResponse
    {
        $result = app(AdmissaoStoreService::class)->criar($request);

        return response()->json($result['body'], $result['status']);
    }

    public function show(FeedbackCurriculo $admissao): Response
    {
        $this->authorize('admissao_processo');

        return response()->noContent();
    }

    public function edit(FeedbackCurriculo $admissao): JsonResponse
    {
        $payload = app(AdmissaoProcessoEditPresenter::class)->montar($admissao);

        return response()->json($payload, 200);
    }

    public function update(UpdateAdmissaoRequest $request, FeedbackCurriculo $admissao): JsonResponse
    {
        $this->authorize('admissao_processo_update');
        $result = app(AdmissaoUpdateService::class)->atualizar($request, $admissao);

        return response()->json($result['body'], $result['status']);
    }

    public function cadastraMassa(CadastraMassaAdmissaoRequest $request): JsonResponse
    {
        $this->authorize('admissao_processo_update');
        $result = app(AdmissaoCadastraMassaService::class)->executar(
            $request->validated(),
            (int) auth()->user()->empresa_id
        );

        return response()->json($result['body'], $result['status']);
    }

    public function destroy(FeedbackCurriculo $admissao): Response
    {
        return response()->noContent();
    }

    public function filtro(Request $request)
    {
        return app(AdmissaoProcessoFiltroQuery::class)->aplicar($request);
    }

    /**
     * Stub da rota legada `admissao.script` (sem implementação ativa).
     */
    public function script(): Response
    {
        return response()->noContent();
    }

    public function atualizar(Request $request): JsonResponse
    {
        return response()->json(app(AdmissaoProcessoListagemPresenter::class)->montarResposta($request));
    }

    public function getColunasTabela(): JsonResponse
    {
        $colunasTabela = app(AdmissaoTabelaColunasService::class)
            ->getColunas((int) auth()->user()->empresa_id, (int) auth()->id());

        return response()->json($colunasTabela);
    }

    public function atualizarColunasTabela(Request $request): JsonResponse
    {
        $colunasTabela = $request->input('colunasTabela', []);

        if (!is_array($colunasTabela)) {
            return response()->json(['error' => 'Formato de dados inválido'], 400);
        }

        app(AdmissaoTabelaColunasService::class)
            ->atualizarColunas((int) auth()->user()->empresa_id, (int) auth()->id(), $colunasTabela);

        return response()->json(['success' => 'Colunas atualizadas e cache atualizado']);
    }

    public function getTiposDependentes()
    {
        return app(AdmissaoListSelectsService::class)->tiposDependentes();
    }

    public function listSelects(): JsonResponse
    {
        return response()->json(app(AdmissaoListSelectsService::class)->listSelects());
    }

    public function uploadAnexos(Request $request)
    {
        return Arquivo::uploadAnexos($request, Arquivo::MIMEAPENASIMAGENS, Arquivo::DISCO_FOTOCURRICULO);
    }

    public function anexoShow(Request $request, $arquivo)
    {
        if ($request->query('thumb')) {
            $extensaoArquivo = substr($arquivo, strrpos($arquivo, '.') + 1);
            $nomeArquivo = substr($arquivo, 0, strrpos($arquivo, '.'));
            $arquivo = $nomeArquivo . '_p.' . $extensaoArquivo;
        }

        return Arquivo::anexoShow(Arquivo::DISCO_FOTOCURRICULO, $arquivo);
    }

    public function anexoShowBase64(Request $request, $arquivo): JsonResponse
    {
        if ($request->query('thumb')) {
            $extensaoArquivo = substr($arquivo, strrpos($arquivo, '.') + 1);
            $nomeArquivo = substr($arquivo, 0, strrpos($arquivo, '.'));
            $arquivo = $nomeArquivo . '_p.' . $extensaoArquivo;
        }
        $data = Arquivo::getFotocurriculoAnexoContentAndMime($arquivo);
        if ($data === null) {
            abort(404);
        }

        return response()->json([
            'base64' => base64_encode($data['content']),
            'mime' => $data['mime'],
        ]);
    }

    public function anexoDelete(Request $request, $arquivo)
    {
        return Arquivo::anexoDelete(Arquivo::DISCO_FOTOCURRICULO, $arquivo);
    }

    public function download(Request $request, $arquivo)
    {
        return Arquivo::anexoDownload(Arquivo::DISCO_FOTOCURRICULO, $arquivo);
    }

    public function export(Request $request): JsonResponse
    {
        $result = app(AdmissaoExportDispatcher::class)->dispatch((int) auth()->id(), $request->all());

        return response()->json($result['body'], $result['status']);
    }

    public function getFichaPdf($fc_token)
    {
        $feedback = FeedbackCurriculo::select([
            'id',
            'curriculo_id',
            'vagas_abertas_id',
            'empresa_id',
        ])->find(\Crypt::decrypt($fc_token))
            ->load(
                'ResultadoIntegrado:id,feedback_id',
                'Admissao',
                'UltimoAso',
                'Curriculo.FotoTres',
            );

        $foto = $feedback->Curriculo?->FotoTres?->first();
        if ($foto && $foto->file) {
            $foto->setAttribute('url_base64', CarteiraImagemCache::fotoCurriculo3x4ParaDataUrl(
                (string) $foto->file,
                $foto->disco ? (string) $foto->disco : null
            ));
        }

        $dados = [
            'dados_empresa' => Sistema::getEmpresaFilialMatriz($feedback->Admissao->centro_custo_filial_id, $feedback['empresa_id']),
            'dados_colaborador' => $feedback,
            'solicitante' => User::select('nome')->find(auth()->id())->nome,
        ];

        $pdf = PDF::loadView('pdf.admissao.ficha', compact('dados'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('ficha_admissao_' . ($dados['dados_colaborador']->Curriculo->nome) . '_' . (new DataHora())->nomeUnico() . '.pdf');
    }

    public function buscaCPF(Request $request): JsonResponse
    {
        $result = app(AdmissaoCpfLookupService::class)->lookup((string) $request->cpf);

        return response()->json($result['body'], $result['status']);
    }

    /**
     * Stub mantido pela rota legada; o front monta o form localmente via formDemitir.
     */
    public function getDadosDemitidoViaPrivilegio(Request $request): JsonResponse
    {
        return response()->json([], 200);
    }

    public function demitirViaPrivilegio(DemitirViaPrivilegioRequest $request): JsonResponse
    {
        $result = app(AdmissaoDemissaoPrivilegioService::class)->demitir(
            $request->validated(),
            (int) auth()->user()->empresa_id,
            auth()->id() ? (int) auth()->id() : null
        );

        return response()->json($result['body'], $result['status']);
    }

    public function import(Request $request)
    {
        return view('g.admissao.import.index');
    }

    public function importUpload(ImportAdmissaoUploadRequest $request): JsonResponse
    {
        $result = app(AdmissaoImportUploadService::class)->enfileirar(
            $request->file('planilha'),
            (int) $request->input('empresa_id'),
            auth()->id() ? (int) auth()->id() : null
        );

        return response()->json($result['body'], $result['status']);
    }
}
