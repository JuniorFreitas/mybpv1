<?php

namespace App\Http\Controllers;

use App\Jobs\JobBoasVindas;
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
     */
    public function store(Request $request)
    {
        $this->authorize('administracao_fornecedores_insert');

        $dados = $request->input();
        $dados['cadastrou'] = auth()->id();
        $dados['empresa_id'] = auth()->user()->empresa_id;

        if (!isset($dados['telefones'])) {
            return response()->json(['msg' => 'É necessário informar pelo menos um número de telefone'], 400);
        }

        $validator = $this->validarDados($dados);
        if ($validator->fails()) {
            return response()->json([
                'msg' => 'Erro ao cadastrar Fornecedor',
                'erros' => $validator->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();

            $password = \Str::random(8);
            $nomeFornecedor = $this->getNomeFornecedor($dados);

            // Criar usuário
            $user = User::create([
                'nome' => $nomeFornecedor,
                'password' => bcrypt($password),
                'login' => $dados['email'],
                'tipo' => User::FORNECEDOR,
                'temp' => false,
                'empresa_id' => $dados['empresa_id'],
                'ativo' => $dados['ativo'],
            ]);

            // Criar fornecedor
            $fornecedor = $user->fornecedor()->create($dados);

            // Processar dados relacionados
            $this->processarTelefones($user, $dados['telefones']);
            $this->processarAnexos($fornecedor, $dados['anexos'] ?? []);
            $this->processarServicos($fornecedor, $dados['servicos'] ?? []);

            DB::commit();

            // Enviar email de boas vindas
            JobBoasVindas::dispatch([
                'nome' => $nomeFornecedor,
                'email' => $dados['email'],
                'empresa_id' => $dados['empresa_id'],
                'senha' => $password,
            ]);

            return response()->json([], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['msg' => 'Erro ao criar usuário: ' . $e->getMessage()], 400);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Fornecedor $fornecedor)
    {
        $fornecedor->load('anexos', 'servicos.anexos', 'telefones');

        $fornecedor->servicos->transform(function ($item) {
            $item->anexosDel = [];
            return $item;
        });

        return $fornecedor;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Fornecedor $fornecedor)
    {
        $this->authorize('administracao_fornecedores_update');

        $dados = $request->input();
        $dados['ativo'] = $dados['ativo'] == 'true';

        $validator = $this->validarDados($dados, $fornecedor->id);
        if ($validator->fails()) {
            return response()->json([
                'msg' => 'Erro ao atualizar o Fornecedor',
                'erros' => $validator->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();

            $fornecedor->update($dados);

            // Processar exclusões
            $this->excluirTelefones($fornecedor, $dados['telefonesDelete'] ?? []);
            $this->excluirServicos($fornecedor, $dados['servicosDelete'] ?? []);
            $this->excluirAnexos($dados['anexosDel'] ?? []);

            // Processar atualizações/criações
            $this->processarTelefones($fornecedor->usuario, $dados['telefones'] ?? []);
            $this->processarAnexos($fornecedor, $dados['anexos'] ?? []);
            $this->processarServicos($fornecedor, $dados['servicos'] ?? []);

            DB::commit();
            return response()->json([], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['msg' => $e->getMessage()], 400);
        }
    }

// Métodos auxiliares privados

    private function validarDados(array $dados, $fornecedorId = null)
    {
        $regrasEspecificas = $dados['tipo_pessoa'] == Fornecedor::PESSOA_JURIDICA
            ? [
                'cnpj' => 'required|min:18|unique:fornecedores,cnpj' . ($fornecedorId ? ",$fornecedorId" : ''),
                'razao_social' => 'required|min:2',
            ]
            : [
                'cpf' => 'required|min:14|unique:fornecedores,cpf' . ($fornecedorId ? ",$fornecedorId" : ''),
                'nome' => 'required|min:2',
            ];

        $regrasComuns = [
            'tipo_pessoa' => 'required',
            'contato' => 'required',
            'uf' => 'required|min:2',
            'logradouro' => 'required|min:3',
            'bairro' => 'required|min:3',
            'municipio' => 'required|min:3',
            'email' => 'required|email' . ($fornecedorId ? '' : '|unique:users,login'),
            'ativo' => 'required',
        ];

        return \Validator::make($dados, array_merge($regrasEspecificas, $regrasComuns));
    }

    private function getNomeFornecedor(array $dados)
    {
        return $dados['tipo_pessoa'] == Fornecedor::PESSOA_JURIDICA
            ? $dados['razao_social']
            : $dados['nome'];
    }

    private function processarTelefones($user, array $telefones)
    {
        foreach ($telefones as $telefone) {
            if (isset($telefone['id']) && $telefone['id'] > 0) {
                $user->telefones()->find($telefone['id'])->update($telefone);
            } else {
                $user->telefones()->create($telefone);
            }
        }
    }

    private function processarAnexos($model, array $anexos)
    {
        foreach ($anexos as $anexo) {
            if ($anexo['chave'] == null) {
                Arquivo::whereId($anexo['id'])->update(['nome' => $anexo['nome']]);
            } else {
                $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                if ($arquivo) {
                    $arquivo->update(['temporario' => false, 'chave' => '']);
                    $model->anexos()->attach($arquivo->id);
                }
            }
        }
    }

    private function processarServicos(Fornecedor $fornecedor, array $servicos)
    {
        foreach ($servicos as $servico) {
            $servico['ativo'] = $servico['ativo'] == 'true';

            // Processar exclusões de anexos do serviço
            if (isset($servico['anexosDel'])) {
                $this->excluirAnexos($servico['anexosDel']);
            }

            if (isset($servico['id'])) {
                // Atualizar serviço existente
                $servicoModel = $fornecedor->servicos()->find($servico['id']);
                $servicoModel->update($servico);
                $this->processarAnexos($servicoModel, $servico['anexos'] ?? []);
            } else {
                // Criar novo serviço
                $servicoModel = $fornecedor->servicos()->create($servico);
                $this->processarAnexos($servicoModel, $servico['anexos'] ?? []);
            }
        }
    }

    private function excluirTelefones(Fornecedor $fornecedor, array $telefonesDelete)
    {
        foreach ($telefonesDelete as $telefoneId) {
            $fornecedor->usuario->telefones()->find($telefoneId)->delete();
        }
    }

    private function excluirServicos(Fornecedor $fornecedor, array $servicosDelete)
    {
        foreach ($servicosDelete as $servicoId) {
            $fornecedor->servicos()->find($servicoId)->delete();
        }
    }

    private function excluirAnexos(array $anexosDelete)
    {
        foreach ($anexosDelete as $anexoId) {
            Arquivo::find($anexoId)?->excluir();
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
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Fornecedor $fornecedor
     * @return \Illuminate\Http\Response
     */
    public function destroy(Fornecedor $fornecedor)
    {
        $this->authorize('administracao_fornecedores_delete');
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
        $this->authorize('administracao_fornecedores_update');
        $fornecedor->ativo = !$fornecedor->ativo;
        $fornecedor->save();
        $fornecedor->refresh();

        User::find($fornecedor->id)->update([
            'ativo' => $fornecedor->ativo
        ]);
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
