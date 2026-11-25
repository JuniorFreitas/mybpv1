<?php

namespace App\Http\Controllers;

use App\Exports\ClientesExport;
use App\Http\Requests\Documentos\StoreDocumentoEmpresaRequest;
use App\Http\Requests\Documentos\UpdateDocumentoEmpresaRequest;
use App\Mail\Movimentacao\FeriasPrevista\SaidaMail;
use App\Mail\Movimentacao\FeriasPrevista\VencimentoMail;
use App\Models\Area;
use App\Models\Arquivo;
use App\Models\Cliente;
use App\Models\ClienteConfig;
use App\Models\DocumentoEmpresa;
use App\Models\DocumentoContratos;
use App\Models\DocumentoSsma;
use App\Models\FeriasPrevista;
use App\Models\Habilidade;
use App\Models\Papel;
use App\Models\Servico;
use App\Models\Sistema;
use App\Models\TipoDocumento;
use App\Models\User;
use App\Rules\CpfValidoEmpresaRules;
use App\Rules\VerificaCpfEmpresaRules;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Mail;
use MasterTag\DataHora;
use PDF;


class DocumentosLegaisEmpresaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('g.administracao.documentoslegais.documentoempresa.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function store(StoreDocumentoEmpresaRequest $request)
    {
        $this->authorize('administracao_documentos_legais_insert');
        $dados = $request->all();

        try {
            DB::beginTransaction();
            DocumentoEmpresa::create($dados);
            DB::commit();


            return response()->json([], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            $msg = "error STORE DOCUMENTOS EMPRESA:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
            \Log::debug($msg);
            return response()->json([
                'msg' => $msg,
            ], 400);
        }

    }

    /**
     * @param DocumentoEmpresa $empresa
     * @return DocumentoEmpresa
     */
    public function edit(DocumentoEmpresa $empresa)
    {
        $empresa = $empresa->load('Anexo');
        $empresa->Anexo->transform(function ($item) {
            $item->anexosDel = [];
            return $item;
        });

        return $empresa;

    }

    /**
     * @param Request $request
     * @param DocumentoEmpresa $empresa
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(UpdateDocumentoEmpresaRequest $request, DocumentoEmpresa $empresa)
    {
        $this->authorize('administracao_documentos_legais_insert');
        $dados = $request->all();

        try {
            DB::beginTransaction();

            $empresa->update($dados);

            DB::commit();
            return response()->json([], 201);

        } catch (\Exception $e) {

            DB::rollBack();
            $msg = "error UPDATE DOCUMENTOS EMPRESA:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
            \Log::debug($msg);
            return response()->json([
                'msg' => $msg,
            ], 400);
        }
    }

    public function atualizar(Request $request)
    {
        $resultado = $this->filtro($request)->paginate($request->porPag ?: 20);
        $contratos = DocumentoContratos::select([
            'id',
            'dados_cadastrais->razao_social as razao_social',
            'dados_cadastrais->nome as nome',
            'dados_cadastrais->tipo as tipo'
        ])
        ->whereAtivo(true)
        ->get();

        $tiposDocumentos = TipoDocumento::orderBy('nome')->get();

        $permite_envio_whatsapp = app(\App\Domain\Whatsapp\Services\WhatsappNotificationGateService::class)
            ->empresaPermiteWhatsapp((int) auth()->user()->empresa_id);

        $permissoes = [
            'whatsapp' => $permite_envio_whatsapp,
              'insert' => auth()->user()->can('administracao_documentos_legais_documentos_empresa_insert'),
              'update' => auth()->user()->can('administracao_documentos_legais_documentos_empresa_update'),
              'delete' => auth()->user()->can('administracao_documentos_legais_documentos_empresa_delete'),
                'show' => auth()->user()->can('administracao_documentos_legais_documentos_empresa_show'),
                 'pdf' => auth()->user()->can('administracao_documentos_legais_documentos_empresa_pdf'),
        ];

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $resultado->items(),
                'lista_contratos' => $contratos,
                'tipos_documentos' => $tiposDocumentos,
                'tipo_pessoa_fisica' => DocumentoContratos::TIPO_PESSOA_FISICA,
                'permissoes' => $permissoes
            ]
        ]);
    }

    public function filtro(Request $request)
    {
        $resultado = DocumentoEmpresa::with('Empresa', 'Contrato')
//        ->whereAtivo(true)
        ->orderByDesc('id');

        if ($request->filled('campoBusca')) {
            $resultado->where('nome', 'like', '%' . $request->campoBusca . '%');

            $resultado->where('razao_social', 'like', '%' . $request->campoBusca . '%')
                ->orWhere('nome_fantasia', 'like', '%' . $request->campoBusca . '%')
                ->orWhere('cnpj', 'like', '%' . $request->campoBusca . '%')
                ->orWhere('cpf', 'like', '%' . $request->campoBusca . '%')
                ->orWhere('nome', 'like', '%' . $request->campoBusca . '%')
                ->orWhere('id', $request->campoBusca);
        }

        if ($request->filled('campoTipo')) {
            $resultado->whereTipoEmpresa($request->campoTipo);
        }

        if ($request->filled('campoStatus')) {
            $status = $request->campoStatus == 'true' ? true : false;
            $resultado->whereAtivo($status);
        }

        return $resultado;
    }


    /**
     * @param DocumentoEmpresa $empresa
     * @return \Illuminate\Http\JsonResponse
     */
    public function ativaDesativa(DocumentoEmpresa $empresa)
    {
        $dados = json_decode(json_encode($empresa->documentos_empresa),JSON_PRETTY_PRINT);
        $dados['ativo'] = !$dados['ativo'];
        $empresa->documentos_empresa = $dados;
        $empresa->save();
        $empresa->refresh();

        return response()->json(['ativo' => $empresa->documentos_empresa->ativo], 201);
    }

    public function uploadAnexos(Request $request)
    {
        return Arquivo::uploadAnexos($request, Arquivo::MIMEAPENASIMAGENSPDF, Arquivo::DISCO_DOCUMENTO_EMPRESA);
    }

    public function anexoShow(Request $request, $arquivo)
    {
        return Arquivo::anexoShow(Arquivo::DISCO_DOCUMENTO_EMPRESA, $arquivo);
    }

    public function anexoDelete(Request $request, $arquivo)
    {
        return Arquivo::anexoDelete(Arquivo::DISCO_DOCUMENTO_EMPRESA, $arquivo);
    }

    //anexo ou foto
    public function download(Request $request, $arquivo)
    {
        return Arquivo::download(Arquivo::DISCO_DOCUMENTO_EMPRESA, $arquivo);
    }

    // Logo-------------------------------------------------
    public function uploadLogo(Request $request)
    {
        return Arquivo::uploadAnexos($request, Arquivo::MIMEAPENASIMAGENS, Arquivo::DISCO_DOCUMENTO_EMPRESA);
    }

    public function logoShow(Request $request, $arquivo)
    {
        return Arquivo::anexoShow(Arquivo::DISCO_DOCUMENTO_EMPRESA, $arquivo);
    }

    public function logoDelete(Request $request, $arquivo)
    {
        return Arquivo::anexoDelete(Arquivo::DISCO_DOCUMENTO_EMPRESA, $arquivo);
    }

    //foto
    public function logoDownload(Request $request, $arquivo)
    {
        return Arquivo::anexoDownload(Arquivo::DISCO_DOCUMENTO_EMPRESA, $arquivo);
    }

    //PDF
    public function getDocumentoEmpresaPdf(DocumentoEmpresa $documento)
    {
        $dados = $documento;
        $pdf = PDF::loadView('pdf/administracao/documentoslegais/documentosempresa/documentosempresapdf', compact('dados'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream("documentos_legais_empresa.pdf");
    }

    public function export()
    {
        $dataUnica = new DataHora(null);
        $nomeArquivo = 'cliente_' . $dataUnica->nomeUnico() . '.xlsx';
        \App\Jobs\JobStoreMaatwebsiteExport::dispatch(
            auth()->id(),
            'Administração - Documentos Empresa',
            $nomeArquivo,
            ClientesExport::class
        );

        return response()->json([
            'msg' => 'Estamos gerando seu arquivo excel, assim que finalizado você será notificado.',
            'regra_unica' => true,
        ]);
    }

    //Verifica Servicos Clientes Vencidos
    public function clientesProximoVencimento()
    {
        $hoje = new DataHora();
        $trintaDias = new DataHora($hoje->addDia(30));

        $clientes = Cliente::whereAtivo(true)->whereHas('ServicosCliente', function ($query) use ($trintaDias) {
            $query->whereAtivo(true)->whereAtivo(true)->where('data_encerramento', '<=', $trintaDias->dataInsert());
        })->with(['ServicosCliente' => function ($query) use ($trintaDias) {
            $query->with('Servico')->whereAtivo(true)
                ->where('data_encerramento', '<=', $trintaDias->dataInsert());
        }]);

        if ($clientes->count() >= 1) {
            $dados = ['dados' => $clientes->get(['id', 'razao_social', 'nome_fantasia', 'nome'])];
            try {
                Mail::send('email.clientes.vencendo', $dados, function ($m) use ($dados) {
                    $m->from('naoresponda@mybp.com.br', 'SGIBPSE - E-mail Automatico');
                    $m->subject("Serviços de Clientes Vencidos ou próximo ao vencimento");
                    $m->to('adm.sede@bpse.com.br');
                });
                \Log::info("E-mail enviado com sucesso para clientes vencidos total de {$clientes->count()}");
                return response()->json(['enviado' => true], 200);
            } catch (\Exception $e) {
                \Log::debug("Error ao enviar e-maill de Vencimento de Servicos: {$e->getMessage()}, {$e->getFile()}, {$e->getLine()}, {$e->getCode()}, {$e->getTrace()} ");
                return response()->json(['enviado' => false], 400);
            }
        }

    }
}
