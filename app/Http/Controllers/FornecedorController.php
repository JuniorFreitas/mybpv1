<?php

namespace App\Http\Controllers;

use App\Models\Arquivo;
use App\Models\Fornecedor;
use App\Models\Sistema;
use App\Models\TipoServicoFornecedor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FornecedorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('g.administracao.fornecedores.index');
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
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('fornecedores_insert');
        $dados = $request->input();
        $dados['ativo'] = $dados['ativo'] == 'true' ? true : false;

        if ($dados['tipo_pessoa'] == Fornecedor::PESSOA_JURIDICA) {
            $validar = [
                'cnpj' => 'required|min:18|unique:fornecedores,cnpj',
                'razao_social' => 'required|min:2',
            ];
        } else {
            $validar = [
                'cpf' => 'required|min:14|unique:fornecedores,cpf',
                'nome' => 'required|min:2',
            ];
        }

        $validaComum = [
            'tipo_pessoa' => 'required',
            'contato' => 'required',
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
                'msg' => 'Erro ao cadastrar Fornecedor',
                'erros' => $dadosValidados->errors()
            ], 400);

        } else {
            try {
                DB::beginTransaction();

                $user = User::create([
                    'nome' => $dados['tipo_pessoa'] == Fornecedor::PESSOA_JURIDICA ? $dados['razao_social'] : $dados['nome'],
                    'password' => bcrypt('mybp2021'),
                    'login' => $dados['email'],
                    'tipo' => 'Fornecedor',
                    'temp' => false,
                    'empresa_id' => auth()->user()->empresa_id,
                    'ativo' => $dados['ativo'],
                ]);

                auth()->user()->FornecedoresEmpresa()->attach($user->id);
                $fornecedor = $user->Fornecedor()->create($dados);

                /**ToDo VER PORQUE TA ZERADO **/
                $fornecedor->id = $user->id;
                $fornecedor->save();


                foreach ($dados['telefones'] as $linha) {
                    $linha['fornecedor_id'] = $fornecedor->id;
                    $fornecedor->Telefones()->create($linha);
                }


                if (isset($dados['anexos'])) {
                    foreach ($dados['anexos'] as $index => $anexo) {
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
                                $fornecedor->Anexos()->attach($arquivo->id);
                            }
                        }

                    }
                }

                if (isset($dados['servicos'])) {
                    foreach ($dados['servicos'] as $linha) {
                        $linha['ativo'] = $linha['ativo'] == 'true' ? true : false;
                        $fornecedorServico = $fornecedor->Servicos()->create($linha);
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
                                        $fornecedorServico->Anexos()->attach($arquivo->id);
                                    }
                                }

                            }
                        }
                    }
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
     * @param \App\Models\Fornecedor $fornecedor
     * @return \Illuminate\Http\Response
     */
    public function show(Fornecedor $fornecedor)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Fornecedor $fornecedor
     * @return \Illuminate\Http\Response
     */
    public function edit(Fornecedor $fornecedor)
    {
        $fornecedor->load('Anexos', 'Servicos.Anexos', 'Telefones');

        $fornecedor->Servicos->transform(function ($item) {
            $item->anexosDel = [];
            return $item;
        });

        return $fornecedor;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Fornecedor $fornecedor
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Fornecedor $fornecedor)
    {
        $this->authorize('fornecedores_update');
        $dados = $request->input();
        $dados['ativo'] = $dados['ativo'] == 'true' ? true : false;

        if ($dados['tipo_pessoa'] == Fornecedor::PESSOA_JURIDICA) {
            $validar = [
                'cnpj' => 'required|min:18|unique:fornecedores,cnpj,' . $fornecedor->id,
                'razao_social' => 'required|min:2',
            ];
        } else {
            $validar = [
                'cpf' => 'required|min:14|unique:fornecedores,cpf,' . $fornecedor->id,
                'nome' => 'required|min:2',
            ];
        }

        $validaComum = [
            'tipo_pessoa' => 'required',
            'contato' => 'required',
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
                'msg' => 'Erro ao atualizar o Fornecedor',
                'erros' => $dadosValidados->errors()
            ], 400);

        } else {
            try {
                DB::beginTransaction();

                $fornecedor->update($dados);

                if (isset($dados['telefonesDelete'])) {
                    foreach ($dados['telefonesDelete'] as $telefonesDelete) {
                        $fornecedor->Telefones()->find($telefonesDelete)->delete();
                    }
                }

                if (isset($dados['telefones'])) {
                    foreach ($dados['telefones'] as $linha) {
                        if (isset($linha['id'])) {
                            $fornecedor->Telefones()->find($linha['id'])->update($linha);
                        } else {
                            $fornecedor->Telefones()->create($linha);
                        }
                    }
                }

                if (isset($dados['servicosDelete'])) {
                    foreach ($dados['servicosDelete'] as $id) {
                        $fornecedor->Servicos()->find($id)->delete();
                    }
                }

                if (isset($dados['anexosDel'])) {
                    foreach ($dados['anexosDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();
                    }
                }


                if (isset($dados['anexos'])) {
                    foreach ($dados['anexos'] as $index => $anexo) {
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
                                $fornecedor->Anexos()->attach($arquivo->id);
                            }
                        }

                    }
                }


                if (isset($dados['servicos'])) {
                    foreach ($dados['servicos'] as $linha) {

                        if (isset($linha['anexosDel'])) {
                            foreach ($linha['anexosDel'] as $id_anexo) {
                                $arquivo = Arquivo::find($id_anexo);
                                $arquivo->excluir();
                            }
                        }

                        $linha['ativo'] = $linha['ativo'] == 'true' ? true : false;
                        if (isset($linha['id'])) {
                            $fornecedor->Servicos()->find($linha['id'])->update($linha);
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
                                            $fornecedor->Servicos()->find($linha['id'])->Anexos()->attach($arquivo->id);
                                        }
                                    }

                                }
                            }
                        } else {
                            $fornecedorServico = $fornecedor->Servicos()->create($linha);
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
                                            $fornecedorServico->Servicos()->find($linha['id'])->Anexos()->attach($arquivo->id);
                                        }
                                    }

                                }
                            }
                        }
                    }
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
     * @param \App\Models\Fornecedor $fornecedor
     * @return \Illuminate\Http\Response
     */
    public function destroy(Fornecedor $fornecedor)
    {
        $this->authorize('fornecedores_delete');
        $fornecedor->delete();
    }

    public function atualizar(Request $request)
    {
        $resultado = Fornecedor::with('Servicos', 'Telefones');
        if ($request->filled('campoBusca')) {

            $resultado->where('nome', 'like', '%' . $request->campoBusca . '%');
            $resultado->orWhere('razao_social', 'like', '%' . $request->campoBusca . '%')
                ->orWhere('nome_fantasia', 'like', '%' . $request->campoBusca . '%')
                ->orWhere('cnpj', 'like', '%' . $request->campoBusca . '%')
                ->orWhere('cpf', 'like', '%' . $request->campoBusca . '%')
                ->orWhere('nome', 'like', '%' . $request->campoBusca . '%')
                ->orWhere('id', $request->campoBusca);
        }
//
        if ($request->filled('campoTipo')) {
            $resultado->whereTipo($request->campoTipo);
        }
//
        if ($request->filled('campoStatus')) {
            $status = $request->campoStatus == 'true' ? true : false;
            $resultado->whereAtivo($status);
        }

        $resultado = $resultado->orderByDesc('created_at')->paginate(50);
//
        $servicos = TipoServicoFornecedor::whereAtivo(true)->orderBy('label')->get();
//
//
        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => ['itens' => $resultado->items(), 'servicos' => $servicos]
        ]);
    }

    public function ativaDesativa(Fornecedor $fornecedor)
    {
        $this->authorize('fornecedores_update');
        $fornecedor->ativo = !$fornecedor->ativo;
        $fornecedor->save();
        $fornecedor->refresh();
        return response()->json(['ativo' => $fornecedor->ativo], 201);
    }

    public function buscaCNPJ(Request $request)
    {
        return Sistema::verificaCnpjCadastrado(Fornecedor::class, $request->cnpj);
    }

    public function buscaCPF(Request $request)
    {
        return Sistema::verificaCpfCadastrado(Fornecedor::class, $request->cpf);
    }

    // Anexos-------------------------------------------------
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
        ], Arquivo::DISCO_FORNECEDOR);
    }

    public function anexoShow(Request $request, $arquivo)
    {
        return Arquivo::anexoShow(Arquivo::DISCO_FORNECEDOR, $arquivo);
    }

    public function anexoDelete(Request $request, $arquivo)
    {
        return Arquivo::anexoDelete(Arquivo::DISCO_FORNECEDOR, $arquivo);
    }

    //anexo ou foto
    public function download(Request $request, $arquivo)
    {
        return Arquivo::anexoDownload(Arquivo::DISCO_FORNECEDOR, $arquivo);
    }

    // Anexos-------------------------------------------------
    public function uploadServicoAnexos(Request $request)
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
        ], Arquivo::DISCO_SERVICO_FORNECEDOR);

    }

    public function anexoServicoShow(Request $request, $arquivo)
    {
        return Arquivo::anexoShow(Arquivo::DISCO_SERVICO_FORNECEDOR, $arquivo);
    }

    public function anexoServicoDelete(Request $request, $arquivo)
    {
        return Arquivo::anexoDelete(Arquivo::DISCO_SERVICO_FORNECEDOR, $arquivo);
    }

    //anexo ou foto
    public function downloadServico(Request $request, $arquivo)
    {
        return Arquivo::anexoDownload(Arquivo::DISCO_SERVICO_FORNECEDOR, $arquivo);
    }
}
