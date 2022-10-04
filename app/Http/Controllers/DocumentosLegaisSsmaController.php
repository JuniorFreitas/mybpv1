<?php

namespace App\Http\Controllers;

use App\Exports\ClientesExport;
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
use Maatwebsite\Excel\Facades\Excel;
use Mail;
use MasterTag\DataHora;
use PDF;


class DocumentosLegaisSsmaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('g.administracao.documentoslegais.documentossma.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->authorize('administracao_documentos_legais_insert');
        $dados = $request->input();
        $dados['ativo'] = $dados['ativo'] == 'true' ? true : false;

        $arrayValidacao = [
            'documentos_ssma.observacao' => [function ($attribute, $value, $fail) use ($dados) {
                if (strlen($value) <= 3) {
                    $fail('Informe uma observação maior que 3 caracteres.');
                }
            }],
            'documentos_ssma.tipo_id' => [function ($attribute, $value, $fail) use ($dados) {
                $tipo_ssma = $dados['tipo_ssma'] ? 'ssma' : 'contrato';
                $tipoDocumento = TipoDocumento::whereTipo($tipo_ssma)->first();
                if(!$tipoDocumento){
                    $fail('Verificar o tipo de documento');
                }
            }],
            'documentos_ssma.data_inicio' => [function ($attribute, $value, $fail) use ($dados) {

                $datainicio = $dados['documentos_ssma']['data_inicio'];
                $dataencerramento = $dados['documentos_ssma']['data_encerramento'];

                $diff_dias = DataHora::diferencaDias($datainicio, $dataencerramento);

                if ($diff_dias <= 0) {
                    $fail('Data Vencimento precisa ser maior que a Data início');
                }
            }],
        ];
        $dadosValidados = \Validator::make($dados, $arrayValidacao);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao cadastrar Documento SSMA',
                'erros' => $dadosValidados->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();
            DocumentoSsma::create($dados);
            DB::commit();

            return response()->json([], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            $msg = "error STORE DOCUMENTOS SSMA:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
            \Log::debug($msg);
            return response()->json([
                'msg' => $msg,
            ], 400);
        }

    }

    /**
     * @param DocumentoSsma $ssma
     * @return DocumentoSsma
     */
    public function edit(DocumentoSsma $ssma)
    {
        $ssma = $ssma->load('Anexo');
        $ssma->Anexo->transform(function ($item) {
            $item->anexosDel = [];
            return $item;
        });

        return $ssma;

    }

    /**
     * @param Request $request
     * @param DocumentoSsma $ssma
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(Request $request, DocumentoSsma $ssma)
    {

        $this->authorize('administracao_documentos_legais_insert');
        $dados = $request->input();
        $dados['ativo'] = $dados['ativo'] == 'true' ? true : false;

        $arrayValidacao = [
            'documentos_ssma.*.observacao' => [function ($attribute, $value, $fail) use ($dados) {
                if (strlen($value) <= 3) {
                    $fail('Informe uma observação maior que 3 caracteres.');
                }
            }],
            'documentos_ssma.tipo_id' => [function ($attribute, $value, $fail) use ($dados) {
                $tipo_ssma = $dados['tipo_ssma'] ? 'ssma' : 'contrato';
                $tipoDocumento = TipoDocumento::whereTipo($tipo_ssma)->first();
                if(!$tipoDocumento){
                    $fail('Verificar o tipo de documento');
                }
            }],
            'documentos_ssma.data_inicio' => [function ($attribute, $value, $fail) use ($dados) {

                $datainicio = $dados['documentos_ssma']['data_inicio'];
                $dataencerramento = $dados['documentos_ssma']['data_encerramento'];
                $diff_dias = DataHora::diferencaDias($datainicio, $dataencerramento);

                if ($diff_dias <= 0) {
                    $fail('Data Vencimento precisa ser maior que a Data início');
                }
            }],
        ];
        $dadosValidados = \Validator::make($dados, $arrayValidacao);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao atualizar Documento SSMA',
                'erros' => $dadosValidados->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();

            $ssma->update($dados);

            DB::commit();
            return response()->json([], 201);

        } catch (\Exception $e) {

            DB::rollBack();
            $msg = "error UPDATE DOCUMENTOS SSMA:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
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

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $resultado->items(),
                'lista_contratos' => $contratos,
                'tipos_documentos' => $tiposDocumentos,
                'tipo_pessoa_fisica' => DocumentoContratos::TIPO_PESSOA_FISICA
            ]
        ]);
    }

    public function filtro(Request $request)
    {
        $resultado = DocumentoSsma::with('Empresa','Contrato')
//            ->whereAtivo(true)
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
            $resultado->whereTipoSsma($request->campoTipo);
        }

        if ($request->filled('campoStatus')) {
            $status = $request->campoStatus == 'true' ? true : false;
            $resultado->whereAtivo($status);
        }

        return $resultado;
    }

    /**
     * @param DocumentoSsma $ssma
     * @return \Illuminate\Http\JsonResponse
     */
    public function ativaDesativa(DocumentoSsma $ssma)
    {
        $ssma->ativo = !$ssma->ativo;
        $ssma->save();
        $ssma->refresh();

        return response()->json(['ativo' => $ssma->ativo], 201);
    }

    public function buscaCNPJ(Request $request)
    {
        return Sistema::verificaCnpjCadastrado(Cliente::class, $request->cnpj);
    }

    public function buscaCPF(Request $request)
    {
        return Sistema::verificaCpfCadastrado(Cliente::class, $request->cpf);
    }

    public function uploadAnexos(Request $request)
    {
        return Arquivo::uploadAnexos($request, Arquivo::MIMEAPENASIMAGENSPDF, Arquivo::DISCO_DOCUMENTO_SSMA);
    }

    public function anexoShow(Request $request, $arquivo)
    {
        return Arquivo::anexoShow(Arquivo::DISCO_DOCUMENTO_SSMA, $arquivo);
    }

    public function anexoDelete(Request $request, $arquivo)
    {
        return Arquivo::anexoDelete(Arquivo::DISCO_DOCUMENTO_SSMA, $arquivo);
    }

    //anexo ou foto
    public function download(Request $request, $arquivo)
    {
        return Arquivo::download(Arquivo::DISCO_DOCUMENTO_SSMA, $arquivo);
    }

    //foto
    public function logoDownload(Request $request, $arquivo)
    {
        return Arquivo::anexoDownload(Arquivo::DISCO_DOCUMENTO_SSMA, $arquivo);
    }

    //PDF
    public function getFichaPdf(Cliente $cliente)
    {
        $dados = $cliente;
        $pdf = PDF::loadView('pdf.cliente.pdf', compact('dados'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream("ficha_cliente_" . STR::slug($dados->tipo == 'Pessoa Jurídica' ? $dados->razao_social : $dados->nome) . ".pdf");
    }

    public function export()
    {
        $dataUnica = new DataHora(null);
        return Excel::download(new ClientesExport, "cliente_{$dataUnica->nomeUnico()}.xlsx");
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
