<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\JobRecrutamento;
use App\Jobs\Recrutamento\JobRecrutamentoCadastro;
use App\Mail\Recrutamento\CadastroMail;
use App\Mail\RecrutamentoMail;
use App\Models\Cliente;
use App\Models\Curriculo;
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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
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
                        ->with('Municipio','Cargo:id,nome')
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
        $escolaridades = Escolaridade::get();
        $respostaVazia = [
            'possuiCadastro' => false,
            'escolaridades' => $escolaridades,
            'success' => true,
        ];

        //BUSCA POR CPF
        $cpf = Sistema::transformCpfCnpj($request->cpf);
        if (!Sistema::validaCPF($cpf)) {
            return response()->json([
                'msg' => 'CPF inválido',
                'success' => false
            ], 400);
        }

        $user = $this->queryUsuariosNaoEmpresaComCurriculoPorCpf($request->empresa_id, $cpf)
            ->select(['id', 'nome'])
            ->first();

        if (!$user) {
            // Resposta uniforme (sem oráculo de existência)
            return response()->json($respostaVazia);
        }

        $dataNascimento = Sistema::dataTransform($request->nascimento);
        $nascimento = new DataHora($dataNascimento);

        // Sem cid (dado sensível LGPD) na resposta pública
        $curriculo = $user->Curriculo()
            ->withoutGlobalScopes()
            ->select([
                'id', 'cpf', 'rg', 'rg_data_emissao', 'naturalidade', 'orgao_expeditor', 'carteira_trabalho',
                'nome', 'cnh', 'nascimento', 'logradouro', 'end_numero', 'complemento', 'bairro', 'municipio',
                'uf', 'cep', 'email', 'formacao', 'formacao_instituicao', 'formacao_curso', 'formacao_status',
                'vaga_pretendida', 'uf_vaga', 'municipio_id', 'pcd', 'viajar', 'filiacao_pai', 'filiacao_mae',
                'disponibilidade_sabado', 'disponibilidade_domingo', 'sexo'
            ])->first();

        if ($curriculo && $curriculo->nascimento == $nascimento->dataCompleta()) {
            $curriculo = $curriculo->load('Qualificacoes', 'Experiencias', 'Telefones');
            $curriculo->temqualificacao = $curriculo->Qualificacoes()->count() > 0 ? true : false;
            $curriculo->temexperiencia = $curriculo->Experiencias()->count() > 0 ? true : false;
            $curriculo->cid = null;

            $curriculo->pcd = $curriculo->pcd ?: '';
            $curriculo->viajar = $curriculo->viajar ?: '';
            $curriculo->municipio_id = $curriculo->municipio_id ?: '';

            $municipio = Municipio::find($curriculo->municipio_id);
            if ($municipio) {
                $curriculo->autocomplete_label_municipio_modal = $municipio->nome . ' - ' . $municipio->uf;
                $curriculo->autocomplete_label_municipio_modal_anterior = $municipio->nome . ' - ' . $municipio->uf;
            }

            $curriculo->cpf = Sistema::maskCpf($curriculo->cpf);
            return response()->json([
                'curriculo' => $curriculo,
                'possuiCadastro' => true,
                'escolaridades' => $escolaridades
            ]);
        }

        // CPF inexistente OU nascimento não confere → mesma resposta
        return response()->json($respostaVazia);
    }

    /**
     * Verifica se o CPF já possui currículo na empresa. Mesma base de {@see buscaCurriculo}
     * (usuário não-Empresa da empresa com currículo pelo CPF, escopo de currículo sem global scopes).
     *
     * Retorno: lista de `{ cpf mascarado, created_at }` ordenada por cadastro. Vazio se não houver conflito.
     * Se `nascimento` for enviado e bater com o do currículo, esse registro é ignorado (mesmo candidato).
     *
     * Espera `cpf` e `empresa_id` no body (na integração v2 o controller injeta `empresa_id` pelo apelido).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function buscaCpf(Request $request)
    {
        $cpf = Sistema::transformCpfCnpj((string) $request->input('cpf', ''));
        if ($cpf === '' || ! Sistema::validaCPF($cpf)) {
            return response()->json([]);
        }

        $empresaId = $request->input('empresa_id');
        if ($empresaId === null || $empresaId === '') {
            return response()->json([]);
        }

        if (! Schema::hasTable('curriculos')) {
            return response()->json([]);
        }

        $nascimentoCompletoInformado = null;
        $nascimentoBruto = $request->input('nascimento');
        if ($nascimentoBruto !== null && $nascimentoBruto !== '') {
            $dataNascimento = Sistema::dataTransform($nascimentoBruto);
            if ($dataNascimento) {
                $nascimentoCompletoInformado = (new DataHora($dataNascimento))->dataCompleta();
            }
        }

        $usuarios = $this->queryUsuariosNaoEmpresaComCurriculoPorCpf((int) $empresaId, $cpf)
            ->select(['id'])
            ->with(['Curriculo' => static function ($q) {
                $q->withoutGlobalScopes()
                    ->select(['id', 'cpf', 'nascimento', 'created_at']);
            }])
            ->orderBy('id')
            ->get();

        $curriculos = $usuarios
            ->map(static fn (User $user) => $user->Curriculo)
            ->filter();

        if ($nascimentoCompletoInformado !== null) {
            $curriculos = $curriculos->filter(
                static fn (Curriculo $c) => $c->nascimento !== $nascimentoCompletoInformado
            );
        }

        $payload = $curriculos
            ->sortBy('created_at')
            ->values()
            ->map(static function (Curriculo $c) {
                return [
                    'cpf' => Sistema::maskCpf($c->cpf),
                    'created_at' => (string) $c->created_at,
                ];
            })
            ->all();

        return response()->json($payload);
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

        $empresaId = (int) ($dados['empresa_id'] ?? 0);
        $vagaAbertaId = (int) ($dados['vaga_aberta_id'] ?? 0);

        $vaga_aberta = VagasAbertas::withoutGlobalScopes()
            ->where('id', $vagaAbertaId)
            ->where('empresa_id', $empresaId)
            ->with('Municipio')
            ->first();

        if (!$vaga_aberta || !$vaga_aberta->Municipio) {
            return response()->json([
                'msg' => 'Vaga inválida para esta empresa',
                'success' => false,
            ], 400);
        }

        $user = User::whereHas('Curriculo', function ($q) use ($cpf) {
            $q->withoutGlobalScopes()->whereCpf($cpf);
        })->whereEmpresaId($empresaId);

        $editando = $user->count() > 0;

        $dados['lido'] = false;
        $dados['email'] = mb_strtolower($dados['email'] ?? '');
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
                    'temp' => true,
                    'termos' => false,
                    'empresa_id' => $empresaId,
                    'password_changed_at' => null,
                    'require_password_reset' => true,
                ];

                $usuario = $user->create($userObj);
                $usuario->Curriculo()->create($this->camposCurriculoPermitidos($dados));

                if (!isset($dados['telefones'])) {
                    DB::rollBack();
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
                if (!$curriculo) {
                    DB::rollBack();
                    return response()->json(['msg' => 'Currículo não encontrado'], 400);
                }

                // Prova de posse: nascimento deve conferir para editar
                $nascimentoInformado = (new DataHora(Sistema::dataTransform($dados['nascimento'] ?? '')))->dataCompleta();
                if ($curriculo->nascimento !== $nascimentoInformado) {
                    DB::rollBack();
                    return response()->json([
                        'msg' => 'Não foi possível atualizar o cadastro. Verifique os dados informados.',
                        'success' => false,
                    ], 400);
                }

                if (isset($dados['telefonesDelete'])) {
                    foreach ($dados['telefonesDelete'] as $index) {
                        if ($index > 0) {
                            $curriculo->Telefones()->where('id', $index)->delete();
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
                        $tel = $curriculo->Telefones()->find($linha['id']);
                        if ($tel) {
                            $tel->update($linha);
                        }
                        if ($linha['principal']) {
                            $dados['telefone_id'] = $linha['id'];
                        }
                    }
                }

                if ($dados['temqualificacao'] == 'true') {
                    if (isset($dados['qualificacoesDelete'])) {
                        foreach ($dados['qualificacoesDelete'] as $index) {
                            if ($index > 0) {
                                $curriculo->Qualificacoes()->where('id', $index)->delete();
                            }
                        }
                    } else {
                        foreach ($dados['qualificacoes'] as $linha) {
                            if (isset($linha['id'])) {
                                $q = $curriculo->Qualificacoes()->find($linha['id']);
                                if ($q) {
                                    $q->update($linha);
                                }
                            } else {
                                $curriculo->Qualificacoes()->create($linha);
                            }
                        }
                    }

                } else {
                    if (isset($dados['qualificacoesDelete'])) {
                        foreach ($dados['qualificacoesDelete'] as $index) {
                            if ($index > 0) {
                                $curriculo->Qualificacoes()->where('id', $index)->delete();
                            }
                        }
                    }
                }

                if ($dados['temexperiencia'] == 'true') {
                    if (isset($dados['experienciasDelete'])) {
                        foreach ($dados['experienciasDelete'] as $index) {
                            if ($index > 0) {
                                $curriculo->Experiencias()->where('id', $index)->delete();
                            }
                        }
                    } else {
                        foreach ($dados['experiencias'] as $linha) {
                            if (isset($linha['id'])) {
                                $linha['data_fim'] = $linha['data_fim'] == "" ? null : $linha['data_fim'];
                                $exp = $curriculo->Experiencias()->find($linha['id']);
                                if ($exp) {
                                    $exp->update($linha);
                                }
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
                                $curriculo->Experiencias()->where('id', $index)->delete();
                            }
                        }
                    }
                }
                $curriculo->update($this->camposCurriculoPermitidos($dados, false));
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

    /**
     * Lista branca de campos de currículo aceitos no cadastro/edição público.
     * Impede mass assignment de flags internas (lido, usuario_lido, etc.).
     */
    private function camposCurriculoPermitidos(array $dados, bool $incluirCpf = true): array
    {
        $permitidos = [
            'rg', 'rg_data_emissao', 'naturalidade', 'nacionalidade', 'orgao_expeditor', 'carteira_trabalho',
            'nome', 'estado_civil', 'cnh', 'cnh_vencimento', 'nascimento', 'logradouro', 'end_numero',
            'complemento', 'bairro', 'municipio', 'uf', 'cep', 'email', 'formacao', 'formacao_instituicao',
            'formacao_curso', 'formacao_status', 'vaga_pretendida', 'uf_vaga', 'municipio_id', 'pcd', 'cid',
            'viajar', 'filiacao_pai', 'filiacao_mae', 'disponibilidade_sabado', 'disponibilidade_domingo', 'sexo',
            'lido',
        ];

        if ($incluirCpf) {
            array_unshift($permitidos, 'cpf');
        }

        $out = [];
        foreach ($permitidos as $campo) {
            if (array_key_exists($campo, $dados)) {
                $out[$campo] = $dados[$campo];
            }
        }

        // Flags internas sempre controladas pelo servidor
        $out['lido'] = false;
        unset($out['usuario_lido'], $out['datalido']);

        return $out;
    }

    /**
     * Usuários da empresa (tipo diferente de Empresa) que possuem currículo com o CPF informado.
     * Espelha o critério de {@see buscaCurriculo} para manter uma única fonte de verdade.
     */
    private function queryUsuariosNaoEmpresaComCurriculoPorCpf(int|string $empresaId, string $cpf): Builder
    {
        return User::query()
            ->where('tipo', '!=', User::EMPRESA)
            ->whereEmpresaId((int) $empresaId)
            ->whereHas('Curriculo', static function ($q) use ($cpf) {
                $q->withoutGlobalScopes()->whereCpf($cpf);
            });
    }
}
