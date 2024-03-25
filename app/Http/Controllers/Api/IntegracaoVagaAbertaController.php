<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\JobRecrutamento;
use App\Jobs\Recrutamento\JobRecrutamentoCadastro;
use App\Mail\Recrutamento\CadastroMail;
use App\Mail\RecrutamentoMail;
use App\Models\CentroCusto;
use App\Models\Cliente;
use App\Models\Curriculo;
use App\Models\CurriculoAtualizacao;
use App\Models\CurriculoExperiencia;
use App\Models\CurriculoQualificacao;
use App\Models\Escolaridade;
use App\Models\Municipio;
use App\Models\Sistema;
use App\Models\TelefoneCurriculo;
use App\Models\User;
use App\Models\VagasAbertas;
use App\Rules\CpfValidoEmpresaRules;
use App\Rules\VagaAbertaEmpresaRules;
use App\Rules\VerificaCpfEmpresaRules;
use DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Mail;
use MasterTag\DataHora;

class IntegracaoVagaAbertaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\JsonResponse
     */
    public function index($empresa_id, $vaga_aberta_id)
    {
        return view('vagasabertas.index', compact('empresa_id', 'vaga_aberta_id'));
    }

    /**
     * @param $empresa_slug
     * @param $vaga_aberta_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getVagaAberta($empresa_slug, $vaga_aberta_id): object
    {
        $vaga = VagasAbertas::whereHas('Empresa', function ($query) use ($empresa_slug) {
            $query->withoutGlobalScopes()->where('apelido', $empresa_slug);
        })
            ->with(['Empresa' => function ($query) {
                $query->withoutGlobalScopes()->select(['id', 'razao_social', 'cnpj'])->with('Logo');
            }, 'Municipio'])
            ->whereId($vaga_aberta_id)
            ->whereAtivo(true)
            ->first();

        if (!$vaga) {
            return response()->json([
                'msg' => 'Vaga não encontrada',
                'success' => false
            ], 404);
        }
        return response()->json([
            'dados' => $vaga,
            'success' => true
        ]);
    }

    /**
     * @param $empresa_slug
     * @return \Illuminate\Http\JsonResponse
     */
    public function getVagasAbertasByEmpresa($empresa_slug): object
    {
        $vagas = Cliente::select(['id', 'razao_social', 'cnpj', 'missao', 'visao', 'valores', 'logradouro', 'municipio', 'uf', 'cep'])
            ->where('apelido', $empresa_slug)
            ->with(
                ['VagasAbertas' => function ($query) {
                    $query->select([
                        'id', 'vaga_id', 'titulo', 'descricao', 'municipio_id', 'empresa_id', 'ativo', 'created_at', 'ativo_sistema'
                    ])->withoutGlobalScopes()
                        ->with('Municipio', 'Cargo:id,nome')
                        ->whereAtivo(true);
                }, 'Logo'])
            ->withoutGlobalScopes()
            ->first();

        if (!$vagas) {
            return response()->json([
                'msg' => 'Empresa não encontrada',
                'success' => false
            ], 404);
        }

        return response()->json([
            'dados' => $vagas,
            'success' => true
        ]);
    }

    public function getDadosEmpresa($empresa_slug)
    {
        $empresa = Cliente::select(['id', 'razao_social', 'cnpj', 'missao', 'visao', 'valores', 'logradouro', 'municipio', 'uf', 'cep', 'apelido'])
            ->where('apelido', $empresa_slug)
            ->with('Logo')
            ->withoutGlobalScopes()
            ->first();

        if (!$empresa) {
            return response()->json([
                'msg' => 'Empresa não encontrada',
                'success' => false
            ], 404);
        }

        $dadosEmpresa = [
            'id' => $empresa->id,
            'apelido' => $empresa->apelido,
            'razao_social' => $empresa->razao_social,
            'cnpj' => $empresa->cnpj,
            'missao' => $empresa->missao,
            'visao' => $empresa->visao,
            'valores' => $empresa->valores,
            'endereco_completo' => $empresa->endereco_completo,
            'logo' => [
                'url' => $empresa->Logo[0]->url,
                'urlThumb' => $empresa->Logo[0]->urlThumb,
                'imagem' => $empresa->Logo[0]->imagem,
                'layout' => $empresa->Logo[0]->layout,
            ]
        ];

        return response()->json([
            'dados' => $dadosEmpresa,
            'success' => true
        ]);


//        $empresa = DB::table('clientes', 'cliente')
//            ->select([
//                'cliente.id as empresa_id',
//                'cliente.razao_social',
//                'cliente.nome_fantasia',
//                'cliente.cnpj',
//                'a.*',
//            ])
//            ->join('cliente_logotipo as cl', 'cl.cliente_id', '=', 'cliente.id')
//            ->join('arquivos as a', 'a.id', '=', 'cl.arquivo_id')
//            ->where('apelido', $empresa_apelido)
//            ->first();
////        $cc = (new CentroCusto())->listaCentroCustoPorCnpj($empresa->id);
//
//        return response()->json([
//            'dados' => $empresa,
//            'success' => true
//        ]);
    }

    public function buscaCurriculo(Request $request)
    {
        //BUSCA POR CPF
        $cpf = Sistema::transformCpfCnpj($request->cpf);
        if (!Sistema::validaCPF($cpf)) {
            return response()->json([
                'msg' => 'CPF inválido',
                'success' => false
            ], 400);
        }

        $user = User::select(['id', 'nome'])->where('tipo', '!=', User::EMPRESA)
            ->whereEmpresaId($request->empresa_id)->whereHas('Curriculo', function ($q) use ($cpf) {
                $q->withoutGlobalScopes()->whereCpf($cpf);
            })->first();

        $escolaridades = Escolaridade::get();

        if (!$user) {
            return response()->json([
                'possuiCadastro' => false,
                'escolaridades' => $escolaridades,
                'success' => true
            ]);
        }

        $dataNascimento = Sistema::dataTransform($request->nascimento);
        $nascimento = new DataHora($dataNascimento);

        $curriculo = $user->Curriculo()
            ->withoutGlobalScopes()
            ->select([
                'id', 'cpf', 'rg', 'rg_data_emissao', 'naturalidade', 'orgao_expeditor', 'carteira_trabalho',
                'nome', 'cnh', 'nascimento', 'logradouro', 'end_numero', 'complemento', 'bairro', 'municipio',
                'uf', 'cep', 'email', 'formacao', 'formacao_instituicao', 'formacao_curso', 'formacao_status',
                'vaga_pretendida', 'uf_vaga', 'municipio_id', 'pcd', 'cid', 'viajar', 'filiacao_pai', 'filiacao_mae',
                'disponibilidade_sabado', 'disponibilidade_domingo', 'sexo'
            ])->first();

        if ($curriculo) {
            $cpfNascimentoValido = $curriculo->nascimento == $nascimento->dataCompleta();
            if ($cpfNascimentoValido) {
                $curriculo = $curriculo->load('Qualificacoes', 'Experiencias', 'Telefones');
                $curriculo->temqualificacao = $curriculo->Qualificacoes()->count() > 0 ? true : false;
                $curriculo->temexperiencia = $curriculo->Experiencias()->count() > 0 ? true : false;

                $curriculo->pcd = $curriculo->pcd ?: '';
                $curriculo->viajar = $curriculo->viajar ?: '';
                $curriculo->municipio_id = $curriculo->municipio_id ?: '';

                $municipio = Municipio::find($curriculo->municipio_id);

                $curriculo->autocomplete_label_municipio_modal = $municipio->nome . ' - ' . $municipio->uf;
                $curriculo->autocomplete_label_municipio_modal_anterior = $municipio->nome . ' - ' . $municipio->uf;

                $curriculo->cpf = Sistema::maskCpf($curriculo->cpf);
                return response()->json([
                    'curriculo' => $curriculo,
                    'possuiCadastro' => true,
                    'escolaridades' => $escolaridades
                ]);
            }
            return response()->json(['msg' => 'CPF encontrado, porém data de nascimento não confere',
                'success' => false
            ], 400);
        }

        return response()->json([
            'possuiCadastro' => false,
            'escolaridades' => $escolaridades,
            'success' => true
        ]);
    }

    public function atualizar(Request $request)
    {
        $vaga = VagasAbertas::withoutGlobalScopes()
            ->whereEmpresaId($request->empresa_id)
            ->whereId($request->vaga_aberta_id)
            ->whereAtivo(true)
            ->with(['Vaga' => function ($q) {
                $q->withoutGlobalScopes();
            }, 'Municipio'])
            ->first();
        return response()->json(['dados' => $vaga], 200);
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
        $dados = $request->input();

        $cpf = Sistema::transformCpfCnpj($request->cpf_padrao);
        $dados['cpf'] = $cpf;

        $user = User::whereHas('Curriculo', function ($q) use ($cpf) {
            $q->withoutGlobalScopes()->whereCpf($cpf);
        })->whereEmpresaId($dados['empresa_id']);

        $editando = $user->count() > 0;

        $dados['lido'] = false;
//        $dados['pcd'] = $dados['pcd'];
//        $dados['viajar'] = $dados['viajar'] == 'true';
//        $dados['disponibilidade_sabado'] = $dados['disponibilidade_sabado'] == 'true';
//        $dados['disponibilidade_domingo'] = $dados['disponibilidade_domingo'] == 'true';
//        $dados['disponibilidade_domingo'] = $dados['disponibilidade_domingo'] == 'true';
        $dados['email'] = mb_strtolower($dados['email']);
        $vaga_aberta = VagasAbertas::whereId($dados['vaga_aberta_id'])->with('Municipio')->first();

        $dados['uf_vaga'] = mb_strtoupper($vaga_aberta->Municipio->uf);
        $dados['municipio_id'] = $vaga_aberta->municipio_id;
        $dados['vaga_pretendida'] = $vaga_aberta->id;

        $arrayValidacao = [
            'nome' => 'required|min:3',
            'cpf' => ['required', 'min:14',
                new CpfValidoEmpresaRules($dados['empresa_id']),
                new VerificaCpfEmpresaRules($dados['empresa_id'], $editando)
            ],

            'vaga_aberta_id' => ['required', new VagaAbertaEmpresaRules($dados['empresa_id'])],

            'nascimento' => 'required|min:10',
            'email' => 'required|email:rfc,dns',
            'cep' => 'required|min:9',
            'logradouro' => 'required',
            'bairro' => 'required',
            'municipio' => 'required',
            'uf' => 'required|min:2',
            'formacao' => 'required',
            'formacao_instituicao' => 'required',
            'formacao_status' => 'required',
            'vaga_pretendida' => 'required',
            'municipio_id' => 'required',
            'pcd' => 'required',
            'disponibilidade_sabado' => 'required',
            'disponibilidade_domingo' => 'required',

            'telefones' => ["required", "array", "min:1"],
            'telefones.*.numero' => 'required|min:14',

        ];

        $arrayQualificacao = [];
        if ($dados['temqualificacao'] && isset($dados['qualificacoes'])) {
            $arrayQualificacao = [
                'qualificacoes.*.nome' => 'required',
                'qualificacoes.*.instituicao' => 'required',
                'qualificacoes.*.mes_conclusao' => 'required',
                'qualificacoes.*.ano_conclusao' => 'required',
            ];
        }

        $arrayExperiencia = [];
        if ($dados['temexperiencia'] && isset($dados['experiencias'])) {
            $arrayExperiencia = [
                'experiencias.*.empresa' => 'required',
                'experiencias.*.cargo' => 'required',
                'experiencias.*.principais_atv' => 'required',
                'experiencias.*.data_inicio' => 'required',
            ];
        }

        $arrayNovo = array_merge($arrayValidacao, $arrayQualificacao, $arrayExperiencia);

        $dadosValidados = \Validator::make($dados, $arrayNovo);


        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao cadastrar curriculo',
                'erros' => $dadosValidados->errors()
            ], 400);

        }

        try {
            DB::beginTransaction();

            if ($user->count() == 0 && !$editando) {
                $userObj = [
                    'nome' => $dados['nome'],
                    'login' => $dados['email'],
                    'password' => Sistema::SenhaCpf($dados['cpf_padrao']),
                    'tipo' => User::CANDIDATO,
                    'ativo' => true,
                    'temp' => false,
                    'termos' => false,
                    'empresa_id' => $dados['empresa_id']
                ];

                $usuario = $user->create($userObj);
                $usuario->Curriculo()->create($dados);

                if (!isset($dados['telefones'])) {
                    return response()->json([
                        'msg' => 'É Necessário Informar pelo menos Um número de telefone',
                        'erros' => $dadosValidados->errors()
                    ], 400);
                }

                foreach ($dados['telefones'] as $linha) {
                    if (isset($linha['id']) && $linha['id'] == 0) {
                        $linha['id'] = null;
                        $linha['principal'] = $linha['principal'] == 'true' ? true : false;
                        $linha['curriculo_id'] = $usuario->id;
                        TelefoneCurriculo::create($linha);
                    }
                }

                if ($dados['temqualificacao'] == 'true') {
                    foreach ($dados['qualificacoes'] as $linha) {
                        $linha['curriculo_id'] = $usuario->id;
                        CurriculoQualificacao::create($linha);
                    }
                }

                if ($dados['temexperiencia'] == 'true') {
                    foreach ($dados['experiencias'] as $linha) {
                        $linha['curriculo_id'] = $usuario->id;
                        $linha['data_fim'] = $linha['data_fim'] == "" ? null : $linha['data_fim'];
                        CurriculoExperiencia::create($linha);
                    }
                }
            } else {
                $curriculo = Curriculo::withoutGlobalScopes()->find($user->first()->id);
                $atualizacao = ['curriculo_id' => $curriculo->id];
                CurriculoAtualizacao::create($atualizacao);

                if (isset($dados['telefonesDelete'])) {
                    foreach ($dados['telefonesDelete'] as $index) {
                        if ($index > 0) {
                            TelefoneCurriculo::find($index)->delete();
                        }
                    }
                }

                foreach ($dados['telefones'] as $linha) {
                    $linha['principal'] = $linha['principal'] == 'true';
                    if ($linha['id'] == 0) {
                        $telPrincipal = $curriculo->Telefones()->create($linha)->id;
                        if ($linha['principal']) {
                            $dados['telefone_id'] = $telPrincipal;
                        }
                    } else {
                        $curriculo->Telefones->find($linha['id'])->update($linha);
                        if ($linha['principal']) {
                            $dados['telefone_id'] = $linha['id'];
                        }
                    }
                }

                if ($dados['temqualificacao'] == 'true') {
                    if (isset($dados['qualificacoesDelete'])) {
                        foreach ($dados['qualificacoesDelete'] as $index) {
                            if ($index > 0) {
                                $curriculo->Qualificacoes()->find($index)->delete();
                            }
                        }
                    } else {
                        foreach ($dados['qualificacoes'] as $linha) {
                            if (isset($linha['id'])) {
                                $curriculo->Qualificacoes()->find($linha['id'])->update($linha);
                            }
                            $curriculo->Qualificacoes()->create($linha);
                        }
                    }

                } else {
                    if (isset($dados['qualificacoesDelete'])) {
                        foreach ($dados['qualificacoesDelete'] as $index) {
                            if ($index > 0) {
                                $curriculo->Qualificacoes()->find($index)->delete();
                            }
                        }
                    }
                }

                if ($dados['temexperiencia'] == 'true') {
                    if (isset($dados['experienciasDelete'])) {
                        foreach ($dados['experienciasDelete'] as $index) {
                            if ($index > 0) {
                                $curriculo->Experiencias()->find($index)->delete();
                            }
                        }
                    } else {
                        foreach ($dados['experiencias'] as $linha) {
                            if (isset($linha['id'])) {
                                $linha['data_fim'] = $linha['data_fim'] == "" ? null : $linha['data_fim'];
                                $curriculo->Experiencias()->find($linha['id'])->update($linha);
                            } else {
                                $linha['data_fim'] = $linha['data_fim'] == "" ? null : $linha['data_fim'];
                                $curriculo->Experiencias()->create($linha);
                            }
                        }
                    }
                } else {
                    if (isset($dados['experienciasDelete'])) {
                        foreach ($dados['experienciasDelete'] as $index) {
                            if ($index > 0) {
                                $curriculo->Experiencias()->find($index)->delete();
                            }
                        }
                    }
                }
                unset($dados['cpf']);
                $curriculo->update($dados);
            }

            DB::commit();
            $dadosEmail = [
                'nome' => $dados['nome'],
                'email' => $dados['email'],
                'empresa_id' => $dados['empresa_id'],
                'vaga_aberta_id' => $dados['vaga_aberta_id'],
            ];

            JobRecrutamento::dispatch($dadosEmail);
            return response()->json([], 201);

        } catch (\Exception $e) {
            DB::rollback();
            $msg = "Erro ao tentar cadastrar o Curriculo: " . $e->getMessage() . " - Linha: " . $e->getLine() . " Empresa ID: " . $dados['empresa_id'] . " CPF:" . $dados['cpf_padrao'];
            \Log::debug($msg);
            \Log::debug($e->getTraceAsString());
            Sistema::LogFormatado($dados);

            if ($e->getLine() == 297) {
                return response()->json(['msg' => 'Remova os telefones adicione novamente, caso o erro persistir atualize a página!'], 400);
            }

            return response()->json(['msg' => 'Houve um erro,  por favor tente novamente!'], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
