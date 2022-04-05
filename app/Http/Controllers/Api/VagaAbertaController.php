<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\JobRecrutamento;
use App\Jobs\Recrutamento\JobRecrutamentoCadastro;
use App\Mail\Recrutamento\CadastroMail;
use App\Mail\RecrutamentoMail;
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
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Mail;
use MasterTag\DataHora;

class VagaAbertaController extends Controller
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
                    $query->withoutGlobalScopes()
                        ->with('Municipio')
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

        $curriculo = Curriculo::withoutGlobalScopes()->whereCpf($cpf);
        $escolaridades = Escolaridade::get();

        if ($curriculo->count() > 0) {
            $dataNascimento = Sistema::dataTransform($request->nascimento);
            $nascimento = new DataHora($dataNascimento);
            $curriculo = $curriculo->whereNascimento($nascimento->dataInsert());

            if ($curriculo->count() > 0) {
                $curriculo = $curriculo->first()->load('Qualificacoes', 'Experiencias', 'Telefones');
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
                ], 200);
            } else {
                return response()->json(['msg' => 'CPF encontrado, porém data de nascimento não confere',
                    'success' => false
                ], 400);
            }
        } else {
            return response()->json([
                'possuiCadastro' => false,
                'escolaridades' => $escolaridades,
                'success' => true
            ], 200);
        }
    }

    public function atualizar(Request $request)
    {
        $vaga = VagasAbertas::whereEmpresaId($request->empresa_id)->whereId($request->vaga_aberta_id)->whereAtivo(true)->with('Vaga', 'Municipio')->first();

        return response()->json(['dados' => $vaga], 200);
    }


    public function buscaCpf(Request $request)
    {
        $cpf = Sistema::transformCpfCnpj($request->cpf);
        if (!Sistema::validaCPF($cpf)) {
            return response()->json(['msg' => 'CPF inválido'], 400);
        }
        $curriculo = Curriculo::whereCpf($cpf)->get();
        return response()->json($curriculo);
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
//        $this->authorize('funcionarios_insert');
        $dados = $request->input();

        $dados['lido'] = false;
        $dados['pcd'] = $dados['pcd'] == 'true' ? true : false;
        $dados['viajar'] = $dados['viajar'] == 'true' ? true : false;
        $dados['disponibilidade_sabado'] = $dados['disponibilidade_sabado'] == 'true' ? true : false;
        $dados['disponibilidade_domingo'] = $dados['disponibilidade_domingo'] == 'true' ? true : false;
        $dados['disponibilidade_domingo'] = $dados['disponibilidade_domingo'] == 'true' ? true : false;
        $dados['email'] = mb_strtolower($dados['email']);
        $vaga_aberta = VagasAbertas::whereId($dados['vaga_aberta_id'])->with('Municipio')->first();

        $dados['uf_vaga'] = mb_strtoupper($vaga_aberta->Municipio->uf);
        $dados['municipio_id'] = $vaga_aberta->municipio_id;
        $dados['vaga_pretendida'] = $vaga_aberta->id;

        $arrayValidacao = [
            'nome' => 'required|min:3',
//            'cpf' => 'required|min:14|unique:curriculos,cpf',
            'nascimento' => 'required|min:10',
            'email' => 'required|email',
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
//            'viajar' => 'required',

            'telefones.*.numero' => 'required|min:14',

        ];

        $arrayQualificacao = [];
        if ($dados['temqualificacao'] == 'true' && isset($dados['qualificacoes'])) {
            $arrayQualificacao = [
                'qualificacoes.*.nome' => 'required',
                'qualificacoes.*.instituicao' => 'required',
                'qualificacoes.*.mes_conclusao' => 'required',
                'qualificacoes.*.ano_conclusao' => 'required',
            ];
        } else {
            $dados['temqualificacao'] = 'false';
        }

        $arrayExperiencia = [];
        if ($dados['temexperiencia'] == 'true' && isset($dados['experiencias'])) {
            $arrayExperiencia = [
                'experiencias.*.empresa' => 'required',
                'experiencias.*.cargo' => 'required',
                'experiencias.*.principais_atv' => 'required',
                'experiencias.*.data_inicio' => 'required',
            ];
        } else {
            $dados['temexperiencia'] = 'false';
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

            $user = User::whereHas('Curriculo', function ($q) use ($dados) {
                $q->withoutGlobalScopes()->whereCpf($dados['cpf_padrao']);
            });

            if ($user->count() == 0) {
                $cpf = Sistema::transformCpfCnpj($request->cpf_padrao);
                if (!Sistema::validaCPF($cpf)) {
                    return response()->json(['msg' => 'CPF inválido'], 400);
                }

                $dados['cpf'] = $cpf;

                $userObj = [
                    'nome' => $dados['nome'],
                    'login' => $dados['email'],
                    'password' => Sistema::SenhaCpf($dados['cpf_padrao']),
                    'tipo' => 'Candidato',
                    'ativo' => true,
                    'temp' => false,
                    'termos' => false,
                    'empresa_id' => $dados['empresa_id']
                ];

                $usuario = $user->create($userObj);
                $userCurriculo = $usuario->Curriculo()->create($dados);

                if (!isset($dados['telefones'])) {
                    return response()->json([
                        'msg' => 'É Necessário Informar pelo menos Um número de telefone',
                        'erros' => $dadosValidados->errors()
                    ], 400);
                } else {
                    if (isset($dados['telefonesDelete'])) {
                        foreach ($dados['telefonesDelete'] as $index) {
                            TelefoneCurriculo::find($index)->delete();
                        }
                    }
                    foreach ($dados['telefones'] as $linha) {
                        if (!isset($linha['id'])) {
                            $linha['curriculo_id'] = $usuario->id;
                            TelefoneCurriculo::create($linha);
                        } else {
                            TelefoneCurriculo::find($linha['id'])->update($linha);
                        }
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
                        $linha['data_fim'] = $linha['data_fim'] == "" ? Carbon::now() : $linha['data_fim'];
                        CurriculoExperiencia::create($linha);
                    }
                }
            } else {
                $curriculo = Curriculo::withoutGlobalScopes()->whereCpf($dados['cpf_padrao'])->first();
                $atualizacao = ['curriculo_id' => $curriculo->id];
                CurriculoAtualizacao::create($atualizacao);

                if (!isset($dados['telefones'])) {
                    return response()->json([
                        'msg' => 'É Necessário Informar pelo menos Um número de telefone',
                        'erros' => $dadosValidados->errors()
                    ], 400);
                }
                if (isset($dados['telefonesDelete'])) {
                    foreach ($dados['telefonesDelete'] as $index) {
                        TelefoneCurriculo::find($index)->delete();
                    }
                }

                foreach($dados['telefones'] as $linha) {
                    $linha['principal'] = $linha['principal'] == 'true' ? true : false;
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
                            $curriculo->Qualificacoes()->find($index)->delete();
                        }
                    }
                    foreach ($dados['qualificacoes'] as $linha) {
                        if (isset($linha['id'])) {
                            $curriculo->Qualificacoes()->find($linha['id'])->update($linha);
                        } else {
                            $curriculo->Qualificacoes()->create($linha);
                        }
                    }
                }

                if ($dados['temexperiencia'] == 'true') {
                    if (isset($dados['experienciasDelete'])) {
                        foreach ($dados['experienciasDelete'] as $index) {
                            $curriculo->Experiencias()->find($index)->delete();
                        }
                    }
                    foreach ($dados['experiencias'] as $linha) {
                        if (isset($linha['id'])) {
                            $linha['data_fim'] = $linha['data_fim'] == "" ? Carbon::now() : $linha['data_fim'];
                            $curriculo->Experiencias()->find($linha['id'])->update($linha);
                        } else {
                            $linha['data_fim'] = $linha['data_fim'] == "" ? Carbon::now() : $linha['data_fim'];
                            $curriculo->Experiencias()->create($linha);
                        }
                    }
                }
                unset($dados['cpf']); //remove o cpf
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
            $msg = "Erro ao tentar cadastrar o Curriculo: " . $e->getMessage() . "trace " . $e->getTraceAsString() . " - Linha: " . $e->getLine() . " Empresa ID: " . $dados['empresa_id'];
            \Log::debug($e->getMessage());
            \Log::info("-------DADOS-------");
            \Log::alert($dados);
            \Log::info("-------FIM DE DADOS-------");
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
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
