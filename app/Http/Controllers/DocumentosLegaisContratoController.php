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
use App\Models\FormaContrato;
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

class DocumentosLegaisContratoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('g.administracao.documentoslegais.contrato.index');
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
        $dados['dados_cadastrais']['email'] = trim(mb_strtolower($dados['dados_cadastrais']['email']));
        $dados['ativo'] = $dados['ativo'] == 'true' ? true : false;

        $arrayValidacao = [
            'dados_cadastrais.nome' => [function ($attribute, $value, $fail) use ($dados) {
                if ($dados['dados_cadastrais']['tipo'] == 'Pessoa Física' && strlen($value) <= 2) {
                    $mensagem = strlen($value) <= 2 ? 'O campo nome deve conter no minimo três caracteres ' : 'Preencha o campo informando nome. ';
                    $fail($mensagem);
                }
            }],
            'dados_cadastrais.cpf' => [function ($attribute, $value, $fail) use ($dados) {

                if ($dados['dados_cadastrais']['tipo'] == 'Pessoa Física' && !Sistema::validaRuleCPF($value)) {
                    $fail('Informe um CPF válido.');
                }
                $verificaCpf = DocumentoContratos::whereJsonContains('dados_cadastrais->cpf', $value)->first();
                if ($dados['dados_cadastrais']['tipo'] == 'Pessoa Física' && $verificaCpf) {
                    $fail('CPF já cadastrado');
                }
            }],
            'dados_cadastrais.razao_social' => [function ($attribute, $value, $fail) use ($dados) {
                if ($dados['dados_cadastrais']['tipo'] == 'Pessoa Jurídica' && strlen($value) <= 3) {
                    $fail('Preencha o campo informando razão social.');
                }
            }],
            'dados_cadastrais.nome_fantasia' => [function ($attribute, $value, $fail) use ($dados) {
                if ($dados['dados_cadastrais']['tipo'] == 'Pessoa Jurídica' && strlen($value) <= 3) {
                    $fail('Preencha o campo informando nome fantasia.');
                }
            }],
            'dados_cadastrais.cnpj' => [function ($attribute, $value, $fail) use ($dados) {
                if ($dados['dados_cadastrais']['tipo'] == 'Pessoa Jurídica' && strlen($value) <= 14) {
                    $fail('Preencha o campo informando CNPJ.');
                }
                $verificaCnpj = DocumentoContratos::whereJsonContains('dados_cadastrais->cnpj', $value)->first();
                if ($dados['dados_cadastrais']['tipo'] == 'Pessoa Jurídica' && $verificaCnpj) {
                    $fail('CNPJ já cadastrado');
                }
            }],
            'dados_cadastrais.email' => 'required|email:rfc,dns',
            'dados_cadastrais.telefones' => ["required", "array", "min:1"],
            'dados_cadastrais.telefones.*.numero' => 'required|min:14',
            'dados_cadastrais.cep' => 'required|min:9',
            'dados_cadastrais.logradouro' => 'required',
            'dados_cadastrais.bairro' => 'required',
            'dados_cadastrais.municipio' => 'required',
            'dados_cadastrais.uf' => 'required|min:2',
            'dados_cadastrais.ramo' => 'required',
            'dados_cadastrais.area_id' => 'required',
        ];

        $dadosValidados = \Validator::make($dados, $arrayValidacao);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao cadastrar Documento Contrato',
                'erros' => $dadosValidados->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();
            DocumentoContratos::create($dados);

//                // Se tem Documento legais
//                if (isset($dados['servicos_cliente'])) {
//                    foreach ($dados['servicos_cliente'] as $linha) {
//                        $linha['ativo'] = $linha['ativo'] == 'true' ? true : false;
//                        if (isset($linha['anexos'])) {
//                            foreach ($linha['anexos'] as $index => $anexo) {
//                                //Se nao tem chave, entao é uma anexo que já estava cadastrada no banco
//                                if ($anexo['chave'] == null) {
//                                    Arquivo::whereId($anexo['id'])->update([
//                                        'nome' => $anexo['nome'],
//                                    ]);
//                                } else {
//                                    $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
//                                    if ($arquivo) {
//                                        $arquivo->temporario = false;
//                                        $arquivo->chave = '';
//                                        $arquivo->save();
//                                    }
//                                }
//
//                            }
//                        }
//                    }
//                }
//
//                if (isset($linha['logo'])) {
//                    foreach ($linha['logo'] as $index => $anexo) {
//                        //Se nao tem chave, entao é uma anexo que já estava cadastrada no banco
//                        if ($anexo['chave'] == null) {
//                            Arquivo::whereId($anexo['id'])->update([
//                                'nome' => $anexo['nome'],
//                            ]);
//                        } else {
//                            $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
//                            if ($arquivo) {
//                                $arquivo->temporario = false;
//                                $arquivo->chave = '';
//                                $arquivo->save();
//                                $cliente->Logo()->attach($arquivo->id);
//                            }
//                        }
//                    }
//                }
//                if (isset($dados['cliente_config'])) {
//                    $dados['cliente_config']['envia_whatsapp'] = $dados['cliente_config']['envia_whatsapp'] == 'true' ? true : false;
//                    $dadosClienteConfig = [
//                        'verifica_mes_vencimento' => $dados['cliente_config']['verifica_mes_vencimento'],
//                        'envia_whatsapp' => $dados['cliente_config']['envia_whatsapp'],
//                        'vencimento_aso' => $dados['cliente_config']['vencimento_aso'],
//                        'cliente_id' => $cliente->id
//                    ];
//                    ClienteConfig::create($dadosClienteConfig);
//
//                }
//


            DB::commit();
            return response()->json([], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            $msg = "error STORE DOCUMENTOS LEGAIS:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
            \Log::debug($msg);
            return response()->json([
                'msg' => $msg,
            ], 400);
        }
    }

    /**
     * @param DocumentoContratos $contrato
     * @return DocumentoContratos
     */
    public function edit(DocumentoContratos $contrato)
    {
        $contrato = $contrato->load('Anexo');
        $contrato->Anexo->transform(function ($item) {
            $item->anexosDel = [];
            return $item;
        });

        return $contrato;

    }

    /**
     * @param Request $request
     * @param DocumentoContratos $contrato
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(Request $request, DocumentoContratos $contrato)
    {
        $this->authorize('administracao_documentos_legais_insert');
        $dados = $request->input();
        $dados['dados_cadastrais']['email'] = trim(mb_strtolower($dados['dados_cadastrais']['email']));
        $dados['ativo'] = $dados['ativo'] == 'true' ? true : false;

        $arrayValidacao = [
            'dados_cadastrais.nome' => [function ($attribute, $value, $fail) use ($dados) {
                if ($dados['dados_cadastrais']['tipo'] == 'Pessoa Física' && strlen($value) <= 2) {
                    $mensagem = strlen($value) <= 2 ? 'O campo nome deve conter no minimo três caracteres ' : 'Preencha o campo informando nome. ';
                    $fail($mensagem);
                }
            }],
            'dados_cadastrais.cpf' => [function ($attribute, $value, $fail) use ($dados, $request) {

                if ($dados['dados_cadastrais']['tipo'] == 'Pessoa Física' && !Sistema::validaRuleCPF($value)) {
                    $fail('Informe um CPF válido.');
                }

//                $verificaCpf = DocumentoContratos::whereJsonContains('dados_cadastrais->cpf', $value)->where('id', $request->segment(5))->first();
//                if ($dados['dados_cadastrais']['tipo'] == 'Pessoa Física' && !$verificaCpf) {
//                    $fail('Não é possível alterar um CPF já cadastrado');
//                }
            }],
            'dados_cadastrais.razao_social' => [function ($attribute, $value, $fail) use ($dados) {
                if ($dados['dados_cadastrais']['tipo'] == 'Pessoa Jurídica' && strlen($value) <= 3) {
                    $fail('Preencha o campo informando razão social.');
                }
            }],
            'dados_cadastrais.nome_fantasia' => [function ($attribute, $value, $fail) use ($dados) {
                if ($dados['dados_cadastrais']['tipo'] == 'Pessoa Jurídica' && strlen($value) <= 3) {
                    $fail('Preencha o campo informando nome fantasia.');
                }
            }],
            'dados_cadastrais.cnpj' => [function ($attribute, $value, $fail) use ($dados, $request) {
                if ($dados['dados_cadastrais']['tipo'] == 'Pessoa Jurídica' && strlen($value) <= 14) {
                    $fail('Preencha o campo informando CNPJ.');
                }
//                $verificaCnpj = DocumentoContratos::whereJsonContains('dados_cadastrais->cnpj', $value)->where('id', $request->segment(5))->first();
//                if ($dados['dados_cadastrais']['tipo'] == 'Pessoa Jurídica' && !$verificaCnpj) {
//                    $fail('Não é possível alterar um CNPJ já cadastrado');
//                }
            }],
            'dados_cadastrais.email' => 'required|email:rfc,dns',
            'dados_cadastrais.telefones' => ["required", "array", "min:1"],
            'dados_cadastrais.telefones.*.numero' => 'required|min:14',
            'dados_cadastrais.cep' => 'required|min:9',
            'dados_cadastrais.logradouro' => 'required',
            'dados_cadastrais.bairro' => 'required',
            'dados_cadastrais.municipio' => 'required',
            'dados_cadastrais.uf' => 'required|min:2',
            'dados_cadastrais.ramo' => 'required',
            'dados_cadastrais.area_id' => 'required',
        ];

        $dadosValidados = \Validator::make($dados, $arrayValidacao);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao cadastrar Documento Legais',
                'erros' => $dadosValidados->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();

            $contrato->update($dados);

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
        $areas = Area::all();
        $tiposDocumentos = TipoDocumento::whereTipo('empresa')->orderBy('nome')->get();
        $tiposServicos = Servico::orderBy('titulo')->get();
        $formasContrato = FormaContrato::orderBy('titulo')->get();
        $permissoes = [
            'insert' => auth()->user()->can('administracao_documentos_legais_contrato_insert'),
            'update' => auth()->user()->can('administracao_documentos_legais_contrato_update'),
            'delete' => auth()->user()->can('administracao_documentos_legais_contrato_delete'),
              'show' => auth()->user()->can('administracao_documentos_legais_contrato_show'),
               'pdf' => auth()->user()->can('administracao_documentos_legais_contrato_pdf')
        ];
        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $resultado->items(),
                'tipos_documentos' => $tiposDocumentos,
                'tipos_servicos' => $tiposServicos,
                'formas_contrato' => $formasContrato,
                'areas' => $areas,
                'permissoes' => $permissoes
            ]
        ]);
    }

    public function filtro(Request $request)
    {
        $resultado = DocumentoContratos::orderByDesc('id');

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
            $resultado->whereTipoCliente($request->campoTipo);
        }

        if ($request->filled('campoStatus')) {
            $status = $request->campoStatus == 'true' ? true : false;
            $resultado->whereAtivo($status);
        }

        return $resultado;
    }

    public function ativaDesativa(DocumentoContratos $contrato)
    {
        $contrato->ativo = !$contrato->ativo;
        $contrato->save();
        $contrato->refresh();

        return response()->json(['ativo' => $contrato->ativo], 201);
    }

    public function buscaCNPJ(Request $request)
    {
        return Sistema::verificaCnpjCadastrado(Cliente::class, $request->cnpj);
    }

    public function buscaCPF(Request $request)
    {
        return Sistema::verificaCpfCadastrado(Cliente::class, $request->cpf);
    }

    // Logo-------------------------------------------------
    public function uploadLogo(Request $request)
    {
        return Arquivo::uploadAnexos($request, Arquivo::MIMEAPENASIMAGENS, Arquivo::DISCO_DOCUMENTO_CONTRATO);
    }

    public function logoShow(Request $request, $arquivo)
    {
        return Arquivo::anexoShow(Arquivo::DISCO_DOCUMENTO_CONTRATO, $arquivo);
    }

    public function logoDelete(Request $request, $arquivo)
    {
        return Arquivo::anexoDelete(Arquivo::DISCO_DOCUMENTO_CONTRATO, $arquivo);
    }

    public function uploadAnexos(Request $request)
    {
        return Arquivo::uploadAnexos($request, Arquivo::MIMEAPENASIMAGENSPDF, Arquivo::DISCO_DOCUMENTO_CONTRATO);
    }

    public function anexoShow(Request $request, $arquivo)
    {
        return Arquivo::anexoShow(Arquivo::DISCO_DOCUMENTO_CONTRATO, $arquivo);
    }

    public function anexoDelete(Request $request, $arquivo)
    {
        return Arquivo::anexoDelete(Arquivo::DISCO_DOCUMENTO_CONTRATO, $arquivo);
    }

    //foto
    public function logoDownload(Request $request, $arquivo)
    {
        return Arquivo::anexoDownload(Arquivo::DISCO_DOCUMENTO_CONTRATO, $arquivo);
    }

    //PDF
    public function getContratoPdf(DocumentoContratos $contrato)
    {
        $dados = $contrato;
        $pdf = PDF::loadView('pdf/administracao/documentoslegais/contrato/contratopdf', compact('dados'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream("contrato" . STR::slug($dados->tipo == 'Pessoa Jurídica' ? $dados->razao_social : $dados->nome) . ".pdf");
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
