<?php

namespace App\Http\Controllers;

use App\Exports\ClientesExport;
use App\Mail\Movimentacao\FeriasPrevista\SaidaMail;
use App\Mail\Movimentacao\FeriasPrevista\VencimentoMail;
use App\Models\Area;
use App\Models\Arquivo;
use App\Models\Cliente;
use App\Models\ClienteConfig;
use App\Models\FeriasPrevista;
use App\Models\Servico;
use App\Models\Sistema;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Mail;
use MasterTag\DataHora;
use PDF;


class ClientesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('g.administracao.clientes.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->authorize('administracao_clientes_insert');
        $dados = $request->input();
        $dados['ativo'] = $dados['ativo'] == 'true' ? true : false;

        if ($dados['tipo'] == Cliente::TIPO_PESSOA_JURIDICA) {
            $validar = [
                'cnpj' => 'required|min:18|unique:clientes,cnpj',
                'razao_social' => 'required|min:2',
            ];
        } else {
            $validar = [
                'cpf' => 'required|min:14|unique:clientes,cpf',
                'nome' => 'required|min:2',
            ];
        }

        $validaComum = [
            'area_id' => 'required',
            'contato' => 'required',
            'aniversario' => 'required',
            'uf' => 'required|min:2',
            'logradouro' => 'required|min:3',
            'bairro' => 'required|min:3',
            'municipio' => 'required|min:3',
            'email' => 'required|email',
            'ativo' => 'required',
        ];


        array_merge($validar, $validaComum);

        if (!isset($dados['telefones'])) {
            return response()->json([
                'msg' => 'É Necessário Informar pelo menos Um número de telefone'
            ], 400);
        }

        $dadosValidados = \Validator::make($dados, $validar);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao cadastrar Cliente',
                'erros' => $dadosValidados->errors()
            ], 400);

        } else {
            try {
                DB::beginTransaction();
                $user = User::create([
                    'nome' => $dados['tipo'] == Cliente::TIPO_PESSOA_JURIDICA ? $dados['razao_social'] : $dados['nome'],
                    'password' => bcrypt('mybp2021'),
                    'login' => $dados['email'],
                    'tipo' => 'Empresa',
                    'temp' => false,
                    'empresa_id' => auth()->user()->empresa_id,
                    'ativo' => true,
                ]);

                User::find($user->id)->update(['empresa_id' => $user->id]);

                auth()->user()->ClientesEmpresa()->attach($user->id);

                $cliente = $user->Cliente()->create($dados);

                /**ToDo VER PORQUE TA ZERADO **/
                $cliente->id = $user->id;
                $cliente->save();

                foreach ($dados['telefones'] as $linha) {
                    $linha['cliente_id'] = $cliente->id;
                    $cliente->Telefones()->create($linha);
                }
                // Se tem Cliente
                if (isset($dados['servicos_cliente'])) {
                    foreach ($dados['servicos_cliente'] as $linha) {
                        $linha['ativo'] = $linha['ativo'] == 'true' ? true : false;
                        $clienteServico = $cliente->ServicosCliente()->create($linha);
                        if (isset($linha['anexos'])) {
                            foreach ($linha['anexos'] as $index => $anexo) {
                                //Se nao tem chave, entao é uma anexo que já estava cadastrada no banco
                                if ($anexo['chave'] == null) {
                                    Arquivo::whereId($anexo['id'])->update([
                                        'nome' => $anexo['nome'],
                                    ]);
                                } else {
                                    $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                                    if ($arquivo) {
                                        $arquivo->temporario = false;
                                        $arquivo->chave = '';
                                        $arquivo->save();
                                        $clienteServico->Anexos()->attach($arquivo->id);
                                    }
                                }

                            }
                        }
                    }
                }

                // Se tem Prospect
                if (isset($dados['servicos_prospect'])) {
                    foreach ($dados['servicos_prospect'] as $linha) {
                        $prospect = $cliente->ServicosProspect()->create($linha);
                        if (isset($linha['anexos'])) {
                            foreach ($linha['anexos'] as $index => $anexo) {
                                //Se nao tem chave, entao é uma anexo que já estava cadastrada no banco
                                if ($anexo['chave'] == null) {
                                    Arquivo::whereId($anexo['id'])->update([
                                        'nome' => $anexo['nome'],
                                    ]);
                                } else {
                                    $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                                    if ($arquivo) {
                                        $arquivo->temporario = false;
                                        $arquivo->chave = '';
                                        $arquivo->save();
                                        $prospect->Anexos()->attach($arquivo->id);
                                    }
                                }
                            }
                        }
                    }
                }

                if (isset($linha['logo'])) {
                    foreach ($linha['logo'] as $index => $anexo) {
                        //Se nao tem chave, entao é uma anexo que já estava cadastrada no banco
                        if ($anexo['chave'] == null) {
                            Arquivo::whereId($anexo['id'])->update([
                                'nome' => $anexo['nome'],
                            ]);
                        } else {
                            $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                            if ($arquivo) {
                                $arquivo->temporario = false;
                                $arquivo->chave = '';
                                $arquivo->save();
                                $cliente->Logo()->attach($arquivo->id);
                            }
                        }
                    }
                }

                if (isset($linha['mascote'])) {
                    foreach ($linha['mascote'] as $index => $anexo) {
                        //Se nao tem chave, entao é uma anexo que já estava cadastrada no banco
                        if ($anexo['chave'] == null) {
                            Arquivo::whereId($anexo['id'])->update([
                                'nome' => $anexo['nome'],
                            ]);
                        } else {
                            $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                            if ($arquivo) {
                                $arquivo->temporario = false;
                                $arquivo->chave = '';
                                $arquivo->save();
                                $cliente->Mascote()->attach($arquivo->id);
                            }
                        }
                    }
                }

                if (isset($dados['cliente_config'])) {
                    $dados['cliente_config']['envia_whatsapp'] = $dados['cliente_config']['envia_whatsapp'] == 'true' ? true : false;
                    $dados = [
                        'verifica_mes_vencimento' => $dados['cliente_config']['verifica_mes_vencimento'],
                        'envia_whatsapp' => $dados['cliente_config']['envia_whatsapp'],
                        'vencimento_aso' => $dados['cliente_config']['vencimento_aso'],
                        'cliente_id' => $cliente->id
                    ];
                    ClienteConfig::create($dados);

                }

                DB::commit();
                return response()->json([], 201);

            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'msg' => $e->getMessage(),
                ], 400);
            }
        }

    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Cliente $clientes
     * @return \Illuminate\Http\Response
     */
    public function show(Cliente $clientes)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Cliente $cliente
     * @return Cliente
     */
    public function edit(Cliente $cliente)
    {
        $cliente = $cliente->load('Telefones', 'AreasEtiquetas', 'ServicosCliente.Anexos', 'ServicosProspect.Anexos', 'Logo', 'Mascote', 'ClienteConfig');
        $cliente->areas_etiquetas_del = [];
        $cliente->ServicosCliente->transform(function ($item) {
            $item->anexosDel = [];
            return $item;
        });
        $cliente->ServicosProspect->transform(function ($item) {
            $item->anexosDel = [];
            return $item;
        });
        return $cliente;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Cliente $cliente
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Cliente $cliente)
    {
        $this->authorize('administracao_clientes_insert');
        $dados = $request->input();
        $dados['ativo'] = $dados['ativo'] == 'true' ? true : false;

        if ($dados['tipo'] == Cliente::TIPO_PESSOA_JURIDICA) {
            $validar = [
                'cnpj' => 'required|min:18|unique:clientes,cnpj,' . $cliente->id,
                'razao_social' => 'required|min:2',
            ];
        } else {
            $validar = [
                'cpf' => 'required|min:14|unique:clientes,cpf,' . $cliente->id,
                'nome' => 'required|min:2',
            ];
        }

        $validaComum = [
            'area_id' => 'required',
            'contato' => 'required',
            'aniversario' => 'required',
            'uf' => 'required|min:2',
            'logradouro' => 'required|min:3',
            'bairro' => 'required|min:3',
            'municipio' => 'required|min:3',
            'email' => 'required|email',
            'ativo' => 'required',
        ];

        array_merge($validar, $validaComum);

        $dadosValidados = \Validator::make($dados, $validar);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao atualizar Cliente',
                'erros' => $dadosValidados->errors()
            ], 400);

        } else {
            try {
                DB::beginTransaction();

                $cliente->update($dados);


                if (isset($dados['telefonesDelete'])) {
                    foreach ($dados['telefonesDelete'] as $telefonesDelete) {
                        $cliente->Telefones()->find($telefonesDelete)->delete();
                    }
                }
                if (isset($dados['telefones'])) {
                    foreach ($dados['telefones'] as $linha) {
                        if (isset($linha['id'])) {
                            $cliente->Telefones()->find($linha['id'])->update($linha);
                        } else {
                            $cliente->Telefones()->create($linha);
                        }
                    }
                }

                if (isset($dados['logoDel'])) {
                    foreach ($dados['logoDel'] as $id) {
                        $cliente->Logo()->find($id)->delete();
                    }
                }


                if (isset($dados['logo'])) {
                    foreach ($dados['logo'] as $index => $anexo) {
                        //Se nao tem chave, entao é uma anexo que já estava cadastrada no banco
                        if ($anexo['chave'] == null) {
                            Arquivo::whereId($anexo['id'])->update([
                                'nome' => $anexo['nome'],
                            ]);
                        } else {
                            $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                            if ($arquivo) {
                                $arquivo->temporario = false;
                                $arquivo->chave = '';
                                $arquivo->save();
                                $cliente->Logo()->attach($arquivo->id);
                            }
                        }
                    }
                }


                if (isset($dados['mascoteDel'])) {
                    foreach ($dados['mascoteDel'] as $id) {
                        $cliente->Mascote()->find($id)->delete();
                    }
                }


                if (isset($dados['mascote'])) {
                    foreach ($dados['mascote'] as $index => $anexo) {
                        //Se nao tem chave, entao é uma anexo que já estava cadastrada no banco
                        if ($anexo['chave'] == null) {
                            Arquivo::whereId($anexo['id'])->update([
                                'nome' => $anexo['nome'],
                            ]);
                        } else {
                            $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                            if ($arquivo) {
                                $arquivo->temporario = false;
                                $arquivo->chave = '';
                                $arquivo->save();
                                $cliente->Mascote()->attach($arquivo->id);
                            }
                        }
                    }
                }


                if (isset($dados['servicos_clienteDelete'])) {
                    foreach ($dados['servicos_clienteDelete'] as $id) {
                        $cliente->ServicosCliente()->find($id)->delete();
                    }
                }

                if (isset($dados['servicos_prospectDelete'])) {
                    foreach ($dados['servicos_prospectDelete'] as $id) {
                        $cliente->ServicosProspect()->find($id)->delete();
                    }
                }

                // Se Tem Serviço Cliente
                if (isset($dados['servicos_cliente'])) {

                    foreach ($dados['servicos_cliente'] as $linha) {
                        $linha['ativo'] = $linha['ativo'] == 'true' ? true : false;

                        if (isset($linha['anexosDel'])) {
                            foreach ($linha['anexosDel'] as $id_anexo) {
                                $arquivo = Arquivo::find($id_anexo);
                                $arquivo->excluir();
                            }
                        }

                        if (isset($linha['id'])) {
                            $cliente->ServicosCliente()->find($linha['id'])->update($linha);
                            foreach ($linha['anexos'] as $index => $anexo) {
                                //Se nao tem chave, entao é uma anexo que já estava cadastrada no banco
                                if ($anexo['chave'] == null) {
                                    Arquivo::whereId($anexo['id'])->update([
                                        'nome' => $anexo['nome'],
                                    ]);
                                } else {
                                    $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                                    if ($arquivo) {
                                        $arquivo->temporario = false;
                                        $arquivo->chave = '';
                                        $arquivo->save();
                                        $cliente->ServicosCliente()->find($linha['id'])->Anexos()->attach($arquivo->id);
                                    }
                                }
                            }
                        } else {
                            $servico = $cliente->ServicosCliente()->create($linha);
                            if (isset($linha['anexos'])) {
                                foreach ($linha['anexos'] as $index => $anexo) {
                                    //Se nao tem chave, entao é uma anexo que já estava cadastrada no banco
                                    if ($anexo['chave'] == null) {
                                        Arquivo::whereId($anexo['id'])->update([
                                            'nome' => $anexo['nome'],
                                        ]);
                                    } else {
                                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                                        if ($arquivo) {
                                            $arquivo->temporario = false;
                                            $arquivo->chave = '';
                                            $arquivo->save();

                                            $servico->Anexos()->attach($arquivo->id);

                                        }
                                    }

                                }
                            }
                        }

                    }
                }


                if (isset($dados['servicos_prospect'])) {
                    foreach ($dados['servicos_prospect'] as $linha) {

                        if (isset($linha['anexosDel'])) {
                            foreach ($linha['anexosDel'] as $id_anexo) {
                                $arquivo = Arquivo::find($id_anexo);
                                $arquivo->excluir();
                            }
                        }

                        if (isset($linha['id'])) {

                            $cliente->ServicosProspect()->find($linha['id'])->update($linha);
                            if (isset($linha['anexos'])) {
                                foreach ($linha['anexos'] as $index => $anexo) {
                                    //Se nao tem chave, entao é uma anexo que já estava cadastrada no banco
                                    if ($anexo['chave'] == null) {
                                        Arquivo::whereId($anexo['id'])->update([
                                            'nome' => $anexo['nome'],
                                        ]);
                                    } else {
                                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                                        if ($arquivo) {
                                            $arquivo->temporario = false;
                                            $arquivo->chave = '';
                                            $arquivo->save();
                                            $cliente->ServicosProspect()->find($linha['id'])->Anexos()->attach($arquivo->id);
                                        }
                                    }
                                }
                            }
                        } else {
                            $servico = $cliente->ServicosProspect()->create($linha);
                            if (isset($linha['anexos'])) {
                                foreach ($linha['anexos'] as $index => $anexo) {
                                    //Se nao tem chave, entao é uma anexo que já estava cadastrada no banco
                                    if ($anexo['chave'] == null) {
                                        Arquivo::whereId($anexo['id'])->update([
                                            'nome' => $anexo['nome'],
                                        ]);
                                    } else {
                                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                                        if ($arquivo) {
                                            $arquivo->temporario = false;
                                            $arquivo->chave = '';
                                            $arquivo->save();
                                            $servico->Anexos()->attach($arquivo->id);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                if (isset($dados['cliente_config']) && !empty($dados['cliente_config']['id'])) {
                    $config = ClienteConfig::find($dados['cliente_config']['id']);
                    $config->update([
                        'verifica_mes_vencimento' => $dados['cliente_config']['verifica_mes_vencimento'],
                        'envia_whatsapp' => $dados['cliente_config']['envia_whatsapp'],
                        'vencimento_aso' => $dados['cliente_config']['vencimento_aso'],
                    ]);
                } else {
                    $dados = [
                        'verifica_mes_vencimento' => $dados['cliente_config']['verifica_mes_vencimento'],
                        'envia_whatsapp' => $dados['cliente_config']['envia_whatsapp'],
                        'vencimento_aso' => $dados['cliente_config']['vencimento_aso'],
                        'cliente_id' => $cliente->id
                    ];
                    ClienteConfig::create($dados);
                }

                DB::commit();
                return response()->json([], 201);

            } catch (\Exception $e) {

                DB::rollBack();
                return response()->json([
                    'msg' => $e->getMessage(),
                ], 400);
            }
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Cliente $clientes
     * @return \Illuminate\Http\Response
     */

    public function destroy(Cliente $cliente)
    {
        $this->authorize('administracao_clientes_delete');
        $cliente->delete();
    }

    public function atualizar(Request $request)
    {
        //        $resultado = User::with('Area:id,label', 'Telefones:id,cliente_id,numero');
        $resultado = Cliente::with('Area:id,label', 'Telefones:id,cliente_id,numero');

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

        $servicos = Servico::whereAtivo(true)->orderBy('titulo')->get();
        $areas = Area::whereAtivo(true)->get();
        $resultado = $resultado->orderByDesc('ativo')->orderBy('razao_social')->orderBy('nome')->orderBy('tipo_cliente')->paginate(50);

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => ['itens' => $resultado->items(), 'servicos' => $servicos, 'areas' => $areas]
        ]);
    }

    public function ativaDesativa(Cliente $cliente)
    {
        $cliente->ativo = !$cliente->ativo;
        $cliente->save();
        $cliente->refresh();

        $users = User::whereEmpresaId($cliente->id)->get();
        foreach ($users as $user) {
            $user['ativo'] = $cliente->ativo;
            $user->save();
            $user->refresh();
        }

        return response()->json(['ativo' => $cliente->ativo], 201);
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
        return Arquivo::uploadAnexos($request, [
            Arquivo::MIME_JPEG,
            Arquivo::MIME_JPG,
            Arquivo::MIME_PNG,
            Arquivo::MIME_PDF,
            Arquivo::MIME_DOC,
            Arquivo::MIME_DOCX,
            Arquivo::MIME_PPS,
            Arquivo::MIME_PPSX,
            Arquivo::MIME_PPT,
            Arquivo::MIME_PPTX,
            Arquivo::MIME_XLS,
            Arquivo::MIME_XLSX,
            Arquivo::MIME_ZIP,
            Arquivo::MIME_RAR,
        ], Arquivo::DISCO_CLIENTE);
    }

    public function anexoShow(Request $request, $arquivo)
    {
        return Arquivo::anexoShow(Arquivo::DISCO_CLIENTE, $arquivo);
    }

    public function anexoDelete(Request $request, $arquivo)
    {
        return Arquivo::anexoDelete(Arquivo::DISCO_CLIENTE, $arquivo);
    }

    //anexo ou foto
    public function download(Request $request, $arquivo)
    {
        return Arquivo::download(Arquivo::DISCO_CLIENTE, $arquivo);
    }

    // Logo-------------------------------------------------
    public function uploadLogo(Request $request)
    {
        return Arquivo::uploadAnexos($request, Arquivo::MIMEAPENASIMAGENS, Arquivo::DISCO_CLIENTE);
    }

    public function logoShow(Request $request, $arquivo)
    {
        return Arquivo::anexoShow(Arquivo::DISCO_CLIENTE, $arquivo);
    }

    public function logoDelete(Request $request, $arquivo)
    {
        return Arquivo::anexoDelete(Arquivo::DISCO_CLIENTE, $arquivo);
    }

    //foto
    public function logoDownload(Request $request, $arquivo)
    {
        return Arquivo::anexoDownload(Arquivo::DISCO_CLIENTE, $arquivo);
    }

    // Mascote-------------------------------------------------
    public function uploadMascote(Request $request)
    {
        return Arquivo::uploadAnexos($request, Arquivo::MIMEAPENASIMAGENS, Arquivo::DISCO_CLIENTE);
    }

    public function mascoteShow(Request $request, $arquivo)
    {
        return Arquivo::anexoShow(Arquivo::DISCO_CLIENTE, $arquivo);
    }

    public function mascoteDelete(Request $request, $arquivo)
    {
        return Arquivo::anexoDelete(Arquivo::DISCO_CLIENTE, $arquivo);
    }

    public function mascoteDownload(Request $request, $arquivo)
    {
        return Arquivo::anexoDownload(Arquivo::DISCO_CLIENTE, $arquivo);
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
