<?php

namespace App\Http\Controllers;

use App\Domain\Whatsapp\Services\WhatsappConfigService;
use App\Http\Requests\WhatsappPreferenciasUsuarioRequest;
use App\Jobs\JobBoasVindas;
use App\Jobs\JobRecuperaSenha;
use App\Models\Arquivo;
use App\Models\Cliente;
use App\Models\ClienteConfig;
use App\Models\ClienteFilial;
use App\Models\Curriculo;
use App\Models\GrupoCloud;
use App\Models\Papel;
use App\Models\RecrutamentoHistorico;
use App\Models\RecuperacaoSenha;
use App\Models\TipoRecebeEmail;
use App\Models\User;
use App\Models\TelefoneCurriculo;
use DB;
use Illuminate\Http\Request;
use MasterTag\DataHora;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $listaEmpresas = Cliente::whereAtivo(true)->get(['id', 'nome_fantasia']);
        $empresaId = auth()->user()->empresa_id;
        $isMybpEmpresa = $empresaId === User::MYBP_EMPRESA_ID;
        $canInsert = auth()->user()->can('usuario_usuarios_insert');
        $canUpdate = auth()->user()->can('usuario_usuarios_update');
        $canDelete = auth()->user()->can('usuario_usuarios_delete');
        $podeSimular = (int) auth()->user()->grupo_id === 1;
        $urlAtualizar = route('g.usuarios.usuarios.atualizar');

        return view('g.usuarios.usuarios.index', compact(
            'listaEmpresas',
            'empresaId',
            'isMybpEmpresa',
            'canInsert',
            'canUpdate',
            'canDelete',
            'podeSimular',
            'urlAtualizar'
        ));
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
        $this->authorize('usuario_usuarios_insert');
        $dados = $request->input();
        $dados['login'] = strtolower(trim($dados['login']));

        $dadosValidados = \Validator::make($dados, [
            'nome' => 'required|min:3',
            'login' => 'required|email:rfc,dns',
            'tipo' => 'required',
            'ativo' => 'required|boolean',
            'empresa_id' => 'required'
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao cadastrar usuário',
                'erros' => $dadosValidados->errors()
            ], 400);
        }
//            $dados['tipo'] = Papel::find($dados['grupo_id'])->nome;
        $password = \Str::random(8);
        $dados['password'] = bcrypt($password);
        $dados['cadastrou'] = auth()->id();

        JobBoasVindas::dispatch([
            'nome' => $dados['nome'],
            'email' => $dados['login'],
            'empresa_id' => $dados['empresa_id'],
            'senha' => $password,
        ]);

        try {
            DB::beginTransaction();

            $usuario = User::create($dados);

            if (isset($dados['user_recebe_email'])) {
                unset($dados['user_recebe_email'][0]);
                foreach ($dados['user_recebe_email'] as $index => $email) {
                    $usuario->UserRecebeEmail()->attach($index, ['ativo' => $email == null ? false : true]);
                }
            }

            $this->processarTelefones($usuario, $dados['telefones'] ?? []);
            $this->salvarWhatsappPreferenciasAdmin($usuario, $dados['whatsapp_preferencias'] ?? null);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'msg' => 'Erro ao cadastrar usuário',
            ], 400);
        }

        return response()->json([], 201);

    }


    public function show(User $user)
    {
        //
    }


    public function edit(User $usuario)
    {
        $this->authorize('usuario_usuarios_update');
        $usuario->load('papel:id,nome', 'Empresa', 'UserRecebeEmail');
        $usuario->setRelation(
            'telefones',
            TelefoneCurriculo::where('curriculo_id', $usuario->id)->get()
        );

        $ids_form = array();
        foreach ($usuario->UserRecebeEmail as $f) {
            $ids_form[$f->pivot->tipo_email_id] = $f->pivot->ativo;
        }

        $formulario_vazio = collect($ids_form);

        $papeis = Papel::whereEmpresaId($usuario->empresa_id)->NotClinica()->orderBy('nome');
        $cloud = GrupoCloud::whereEmpresaId($usuario->empresa_id)->orderBy('nome')->get();

        return response()->json([
            'usuario' => $usuario,
            'papeis' => $papeis->get(),
            'cloud' => $cloud,
            'formulario_vazio' => $formulario_vazio,
            'whatsapp_liberado' => $this->whatsappLiberadoParaEmpresa((int) $usuario->empresa_id),
            'whatsapp_preferencias' => app(WhatsappConfigService::class)->listPreferenciasUsuarioForApi(
                (int) $usuario->id,
                (int) $usuario->empresa_id,
            ),
        ], 200);
    }


    public function update(Request $request, User $usuario)
    {
        $this->authorize('usuario_usuarios_update');
        $dados = $request->input();
        $dados['ativo'] = $dados['ativo'] == 'true' ? true : false;
        $dados['login'] = strtolower(trim($dados['login']));

        // Validacao para ajax sem dar erro de HTTP (402)
        $dadosValidados = \Validator::make($dados, [
            'nome' => 'required|min:3',
            'login' => 'required|email:rfc,dns',
            'ativo' => 'required|boolean',
            'tipo' => 'required',
        ]);
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao atualizar os dados do usuário',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();

                if (isset($dados['user_recebe_email'])) {
                    if (count($usuario->UserRecebeEmail) == 0) {
                        unset($dados['user_recebe_email'][0]);
                        foreach ($dados['user_recebe_email'] as $index => $email) {
                            $usuario->UserRecebeEmail()->attach($index, ['ativo' => $email == null ? false : true]);
                        }
                    } else if (count($usuario->UserRecebeEmail) < count($dados['user_recebe_email'])) {
                        foreach ($dados['user_recebe_email'] as $index => $email) {
                            $usuario->UserRecebeEmail()->detach($index);
                            $usuario->UserRecebeEmail()->attach($index, ['ativo' => $email == null ? false : true]);
                        }
                    } else {
                        foreach ($dados['user_recebe_email'] as $index => $email) {
                            $usuario->UserRecebeEmail()->updateExistingPivot($index, ['ativo' => $email]);
                        }
                    }
                }

                $this->excluirTelefones($usuario, $dados['telefonesDelete'] ?? []);
                $this->processarTelefones($usuario, $dados['telefones'] ?? []);
                $this->salvarWhatsappPreferenciasAdmin($usuario, $dados['whatsapp_preferencias'] ?? null);

                $usuario->update($dados);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();

                return response()->json([
                    'msg' => 'Erro ao atualizar os dados do usuário',
                ], 400);
            }

            return response()->json([], 201);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $usuario)
    {
        $this->authorize('usuario_usuarios_delete');
        $usuario->delete();
    }

    public function getUsuario()
    {

//        $usuario = User::find(auth()->id(), ['id', 'cliente_id'])->load('Cliente:id,area_id');
        $cliente = auth()->user()->ClienteFuncionarios->first();
        //$cliente = auth()->user()->ClienteFuncionarios()->where('cliente_id',auth()->id())->first();

        $whatsappLiberado = ClienteConfig::select('envia_whatsapp')->whereClienteId(auth()->user()->empresa_id)->first();
        $temfilial = ClienteFilial::select('id')->whereEmpresaId(auth()->user()->empresa_id)->whereAtivo(true)->first();

        if ($cliente) {
            $usuario = [
                'cliente_id' => auth()->user()->empresa_id,
                'area_id' => $cliente->Cliente->area_id,
                'config_empresa' => auth()->user()->EmpresaPontoConfiguracoes,
                'empresa_configuracoes' => auth()->user()->EmpresaConfiguracoes,
                'empresa_id' => auth()->user()->empresa_id,
                'user_id' => auth()->id(),
                'nome' => auth()->user()->nome,
                'whatsappLiberado' => $whatsappLiberado ? $whatsappLiberado->envia_whatsapp : false,
                'podeConfigurarWhatsapp' => auth()->user()->can('configuracao_whatsapp') || auth()->user()->can('administracao_clientes'),
                'podeConfigurarPreferenciasWhatsapp' => auth()->user()->can('preferencias_notificacao_whatsapp'),
                'temFilial' => (bool)$temfilial,
                'apelido' => Cliente::select('apelido')->whereId(auth()->user()->empresa_id)->first()->apelido,
                'cnpjs' => (new Cliente())->Cnpjs(auth()->user()->empresa_id),
                'gestao_rh' => auth()->user()->can('privilegio_gestao_rh')
            ];

        } else {
//            $usuario = auth()->user()->ClientesEmpresa()->select(['id'])->with('Cliente:id,area_id')->get();
            $usuario = [
                'cliente_id' => auth()->user()->empresa_id,
                'area_id' => 0,
                'config_empresa' => auth()->user()->EmpresaPontoConfiguracoes,
                'empresa_configuracoes' => auth()->user()->EmpresaConfiguracoes,
                'empresa_id' => auth()->user()->empresa_id,
                'user_id' => auth()->id(),
                'nome' => auth()->user()->nome,
                'whatsappLiberado' => $whatsappLiberado ? $whatsappLiberado->envia_whatsapp : false,
                'podeConfigurarWhatsapp' => auth()->user()->can('configuracao_whatsapp') || auth()->user()->can('administracao_clientes'),
                'podeConfigurarPreferenciasWhatsapp' => auth()->user()->can('preferencias_notificacao_whatsapp'),
                'temFilial' => (bool)$temfilial,
                'apelido' => Cliente::select('apelido')->whereId(auth()->user()->empresa_id)->first()->apelido,
                'cnpjs' => (new Cliente())->Cnpjs(auth()->user()->empresa_id),
                'gestao_rh' => auth()->user()->can('privilegio_gestao_rh')
            ];
        }

//        \Cache::pull("getUsuario_" . auth()->id());

        /*if (!\Cache::get("getUsuario_" . auth()->id())) {
            \Cache::rememberForever("getUsuario_" . auth()->id(), function () use ($usuario) {
                return $usuario;
            });
        }

        $userCache = \Cache::get("getUsuario_" . auth()->id());*/

        return response()->json($usuario, 200);
    }

    public function ativaDesativa(Request $request)
    {

        $this->authorize('usuario_usuarios_update');
        $user = User::select('id', 'ativo', 'empresa_id')->find($request->id);
        $user->ativo = !$user->ativo;
        $user->save();
        $user->refresh();
        return response()->json(['ativo' => $user->ativo], 201);
    }

    public function atualizar(Request $request)
    {
        $this->authorize('usuario_usuarios');
        $porPagina = $request->get('porPagina');

        $resultado = User::with('Papel:id,nome')
            ->with('Empresa:id,nome_fantasia')
            ->whereIn('tipo', User::TIPOS_USUARIOS_COMUNS)
            ->where('empresa_id', auth()->user()->empresa_id);

        if (auth()->user()->empresa_id === User::MYBP_EMPRESA_ID) {
            $resultado = User::with('Papel:id,nome', 'Empresa')
                ->whereNotIn('tipo', [User::CANDIDATO, User::EMPRESA]);
        }

        if ($request->filled('campoBusca')) {
            $resultado->where('nome', 'like', '%' . $request->campoBusca . '%');
        }

        if ($request->filled('campoLogin')) {
            $resultado->where('login', 'like', '%' . $request->campoLogin . '%');
        }

        if ($request->filled('campoEmpresa')) {
            $resultado->whereEmpresaId($request->campoEmpresa);
        }

        if ($request->filled('campoGrupo')) {
            $resultado->whereGrupoId($request->campoGrupo);
        }

        if ($request->filled('campoTipo')) {
            $resultado->whereTipo($request->campoTipo);
        }

        if ($request->filled('campoStatus')) {
            $resultado->whereAtivo($request->campoStatus);
        }


//        dd($resultado->toSql(), $resultado->getBindings());
        $empresa = auth()->user()->empresa_id;


        $tipo_email = TipoRecebeEmail::all();

        $ids_form = array();
        foreach ($tipo_email as $f) {
            $ids_form[$f->id] = false;
        }

        $formulario_vazio = collect($ids_form);

        $resultado = $resultado->orderBy('nome')->paginate($porPagina);

        $lista_tipos = $empresa == User::MYBP_EMPRESA_ID ? User::TIPOS_USUARIOS_GERENCIAIS : User::TIPOS_USUARIOS_COMUNS;

        $lista_grupos = Papel::whereEmpresaId($empresa)->where('master', false)
            ->where('nome', 'not like', '% - Clinica Exame')->get();

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'resultado' => $resultado->items(),
                'empresa' => $empresa,
                'tipo_email' => $tipo_email,
                'formulario_vazio' => $formulario_vazio,
                'lista_tipos' => $lista_tipos,
                'lista_grupos' => $lista_grupos,
                'tipos_usuarios_gerenciais' => User::TIPOS_USUARIOS_GERENCIAIS,
            ],
        ]);

    }

    public function buscaGrupoEmpresa($empresa_id)
    {
        $papeis = Papel::whereEmpresaId($empresa_id)->where('master', false)
            ->where('nome', 'not like', '% - Clinica Exame')->get();
        $grupo_cloud = GrupoCloud::whereEmpresaId($empresa_id)->get();

        return response()->json(['papeis' => $papeis, 'cloud' => $grupo_cloud], 200);
    }

    public function perfilUsuario($id)
    {
        $user = User::find($id)->load('FotoPerfil');
        return response()->json(['user' => $user]);
    }

    public function atualizaPerfilUsuario(Request $request, $id)
    {
        $dados = $request->input();

        $usuario = User::find($id);

        $dadosValidados = \Validator::make($dados, [
            'nome' => 'required|min:3',
            'login' => 'required|min:3',
        ]);
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao atualizar os dados do usuário',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            $usuario->update($dados);

            if (isset($dados['foto_perfilDel'])) {
                foreach ($dados['foto_perfilDel'] as $id_anexo) {
                    $arquivo = Arquivo::find($id_anexo);
                    $arquivo->excluir();
                }
            }

            // inseri uma nova foto de anexo
            if (isset($dados['foto_perfil'])) {
                foreach ($dados['foto_perfil'] as $index => $anexo) {
                    $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                    if ($arquivo) {
                        $arquivo->temporario = false;
                        $arquivo->chave = '';
                        $arquivo->save();
                        $usuario->FotoPerfil()->attach($arquivo->id);
                    }
                }
            }

            return response()->json([], 201);
        }
    }

    public function solicitaRecuperaSenha(Request $request)
    {
        $usuario = User::whereLogin(trim(mb_strtolower($request->login)))
            ->whereAtivo(true)
            ->first();
        if ($usuario) {
            try {
                DB::beginTransaction();
                $exp = new DataHora();
                $exp->addHora(6);
                $recSenha = $usuario->RecuperacaoSenha()->create([
                    'token' => mb_strtoupper(\Str::random(6)),
                    'expiracao' => $exp->dataHoraInsert(),
                    'ip_solicitacao' => $request->ip(),
                    'solicitacao' => (new DataHora())->dataHoraInsert(),
                    'recuperado' => false
                ]);
                DB::commit();
                JobRecuperaSenha::dispatch([
                    'nome' => $usuario->nome,
                    'email' => $usuario->login,
                    'token' => $recSenha->token,
                    'empresa_id' => $usuario->empresa_id,
                    'expiracao' => $recSenha->expiracao
                ]);
                return response()->json(['msg' => 'Olá enviamos um e-mail pra você verifique sua caixa e-mail (ENTRADA e SPAM)'], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['msg' => 'Erro ao enviar solicitação de recuperação de senha'], 400);
            }

        } else {
            return response()->json(['msg' => 'Usuário não encontrado'], 404);
        }
    }

    public function recuperaSenhaPost(Request $request)
    {
        // Validar a nova senha antes de processar
        $dadosValidados = \Validator::make($request->all(), [
            'novaSenha' => [
                'required',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
            ],
        ], [
            'novaSenha.required' => 'A nova senha é obrigatória.',
            'novaSenha.min' => 'A senha deve ter no mínimo 8 caracteres.',
            'novaSenha.regex' => 'A senha deve conter pelo menos: 1 letra minúscula, 1 maiúscula, 1 número e 1 caractere especial (@$!%*?&).',
        ]);

        if ($dadosValidados->fails()) {
            return response()->json([
                'msg' => 'Erro na validação da senha',
                'erros' => $dadosValidados->errors()
            ], 400);
        }

        $recuperacao = RecuperacaoSenha::whereToken($request->token)
            ->where('recuperado', false)
            ->where('expiracao', '>=', (new DataHora())->dataHoraInsert())
            ->first();

        if ($recuperacao) {
            $recuperacao->update([
                'ip_recuperacao' => $request->ip(),
                'recuperacao' => (new DataHora())->dataInsert(),
                'recuperado' => true
            ]);

            User::find($recuperacao->user_id)->update([
                'password' => bcrypt($request->novaSenha),
                'password_changed_at' => now()
            ]);

            \Auth::login($recuperacao->user);

            return response()->json(['msg' => 'Senha recuperada com sucesso'], 201);
        } else {
            return response()->json(['msg' => 'Token inválido'], 404);
        }
    }

    public function recuperaSenha(Request $request, $token)
    {
        $recuperacao = RecuperacaoSenha::whereToken($token)->whereRecuperado(false)
            ->where('expiracao', '>=', (new DataHora())->dataHoraInsert())
            ->first();

        if ($recuperacao) {
            return view('recupera-senha', compact('recuperacao'));
        } else {
            abort(404);
        }
    }

    public function simularUsuario(Request $request)
    {
        if (auth()->user()->grupo_id == 1) {
            \Auth::loginUsingId($request->user_id);
            return response()->json(['simulacao' => true], 200);
        }
        return response()->json(['simulacao' => false], 400);
    }

    public function uploadAnexos(Request $request)
    {
        return Arquivo::uploadAnexos($request, Arquivo::MIMEAPENASIMAGENS, Arquivo::DISCO_PERFIL_USUARIO);
    }

    public function anexoShow(Request $request, $arquivo)
    {
        return Arquivo::anexoShow(Arquivo::DISCO_PERFIL_USUARIO, $arquivo);
    }

    /**
     * Retorna anexo de perfil em base64 (JSON). Usa o mesmo cache de anexoShow.
     */
    public function anexoShowBase64(Request $request, $arquivo)
    {
        $data = Arquivo::getPerfilAnexoContentAndMime($arquivo);
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
        return Arquivo::anexoDelete(Arquivo::DISCO_PERFIL_USUARIO, $arquivo);
    }

    //anexo ou foto
    public function download(Request $request, $arquivo)
    {
        return Arquivo::anexoDownload(Arquivo::DISCO_PERFIL_USUARIO, $arquivo);
    }

    public function telefoneDeveAtualizar()
    {
        $usuario = auth()->user();

        if (!$usuario->termos) {
            return response()->json(['mostrar' => false]);
        }

        $status = $this->statusTelefoneUsuario($usuario);

        if (!$status['precisa']) {
            return response()->json(['mostrar' => false]);
        }

        $mensagens = [
            'sem_telefone' => 'Você ainda não possui telefone cadastrado. Atualize seus dados para continuar utilizando a plataforma.',
            'sem_principal' => 'Nenhum telefone está marcado como principal. Marque um contato principal para continuar.',
        ];

        return response()->json([
            'mostrar' => true,
            'motivo' => $status['motivo'],
            'mensagem' => $mensagens[$status['motivo']] ?? '',
            'telefones' => $status['telefones']->values(),
        ]);
    }

    public function atualizarTelefoneUsuario(Request $request)
    {
        $usuario = auth()->user();
        $dados = $request->input();
        $telefones = $dados['telefones'] ?? [];

        $telefonesComNumero = array_values(array_filter($telefones, function ($telefone) {
            return trim((string) ($telefone['numero'] ?? '')) !== '';
        }));

        if (count($telefonesComNumero) === 0) {
            return response()->json(['msg' => 'Informe pelo menos um telefone'], 400);
        }

        $temPrincipal = collect($telefonesComNumero)->contains(function ($telefone) {
            return filter_var($telefone['principal'] ?? false, FILTER_VALIDATE_BOOLEAN);
        });

        if (!$temPrincipal) {
            return response()->json(['msg' => 'Marque um telefone como principal'], 400);
        }

        try {
            DB::beginTransaction();
            $this->excluirTelefones($usuario, $dados['telefonesDelete'] ?? []);
            $this->processarTelefones($usuario, $telefones);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['msg' => 'Erro ao atualizar telefone'], 400);
        }

        $status = $this->statusTelefoneUsuario($usuario->fresh());
        if ($status['precisa']) {
            return response()->json(['msg' => 'Não foi possível validar o telefone principal'], 400);
        }

        return response()->json([], 201);
    }

    public function whatsappPreferenciasModelo(Request $request, WhatsappConfigService $configService)
    {
        $this->authorize('usuario_usuarios');

        $empresaId = $this->resolverEmpresaIdParaPreferencias($request);

        return response()->json([
            'whatsapp_liberado' => $this->whatsappLiberadoParaEmpresa($empresaId),
            'preferencias' => $this->preferenciasPadraoParaEmpresa($configService, $empresaId),
        ]);
    }

    public function showWhatsappPreferencias(WhatsappConfigService $configService)
    {
        $user = auth()->user();

        if (!$user->can('preferencias_notificacao_whatsapp')) {
            abort(403, 'Sem permissão para configurar preferências de WhatsApp.');
        }

        return response()->json([
            'whatsapp_liberado' => (bool) ClienteConfig::query()
                ->where('cliente_id', $user->empresa_id)
                ->value('envia_whatsapp'),
            'preferencias' => $configService->listPreferenciasUsuarioForApi(
                (int) $user->id,
                (int) $user->empresa_id,
            ),
        ]);
    }

    public function updateWhatsappPreferencias(
        WhatsappPreferenciasUsuarioRequest $request,
        WhatsappConfigService $configService,
    ) {
        $user = auth()->user();
        $configService->savePreferenciasUsuario((int) $user->id, $request->input('preferencias', []));

        return response()->json([
            'success' => true,
            'preferencias' => $configService->listPreferenciasUsuarioForApi(
                (int) $user->id,
                (int) $user->empresa_id,
            ),
        ]);
    }

    private function statusTelefoneUsuario(User $usuario): array
    {
        $telefones = TelefoneCurriculo::where('curriculo_id', $usuario->id)->get();
        $telefonesValidos = $telefones->filter(function ($telefone) {
            return trim((string) $telefone->numero) !== '';
        });

        if ($telefonesValidos->isEmpty()) {
            return [
                'precisa' => true,
                'motivo' => 'sem_telefone',
                'telefones' => $telefones,
            ];
        }

        $temPrincipal = $telefonesValidos->contains(function ($telefone) {
            return (bool) $telefone->principal;
        });

        if (!$temPrincipal) {
            return [
                'precisa' => true,
                'motivo' => 'sem_principal',
                'telefones' => $telefones,
            ];
        }

        return [
            'precisa' => false,
            'motivo' => null,
            'telefones' => $telefones,
        ];
    }

    private function processarTelefones(User $usuario, array $telefones): void
    {
        $this->garantirCurriculoUsuario($usuario);

        foreach ($telefones as $telefone) {
            if (empty($telefone['numero'])) {
                continue;
            }

            $dados = [
                'tipo' => $telefone['tipo'] ?? TelefoneCurriculo::TIPO_CELULAR,
                'pais' => $telefone['pais'] ?? '55',
                'numero' => $telefone['numero'],
                'ramal' => $telefone['ramal'] ?? null,
                'detalhe' => $telefone['detalhe'] ?? null,
                'principal' => filter_var($telefone['principal'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'curriculo_id' => $usuario->id,
            ];

            if (!empty($telefone['id']) && (int) $telefone['id'] > 0) {
                $telefoneExistente = TelefoneCurriculo::where('id', $telefone['id'])
                    ->where('curriculo_id', $usuario->id)
                    ->first();

                if (!$telefoneExistente) {
                    continue;
                }

                $dadosAnteriores = $telefoneExistente->toArray();
                $telefoneExistente->update($dados);

                RecrutamentoHistorico::registrar(
                    $usuario->id,
                    RecrutamentoHistorico::ACAO_TELEFONE_ATUALIZADO,
                    RecrutamentoHistorico::MODULO_TELEFONE,
                    null,
                    "Telefone {$dados['numero']} foi atualizado",
                    $dadosAnteriores,
                    $telefoneExistente->fresh()->toArray()
                );
            } else {
                $novoTelefone = TelefoneCurriculo::create($dados);

                RecrutamentoHistorico::registrar(
                    $usuario->id,
                    RecrutamentoHistorico::ACAO_TELEFONE_ADICIONADO,
                    RecrutamentoHistorico::MODULO_TELEFONE,
                    null,
                    "Telefone {$dados['numero']} foi adicionado",
                    null,
                    $novoTelefone->toArray()
                );
            }
        }
    }

    private function excluirTelefones(User $usuario, array $telefonesDelete): void
    {
        foreach ($telefonesDelete as $telefoneId) {
            $telefone = TelefoneCurriculo::where('id', $telefoneId)
                ->where('curriculo_id', $usuario->id)
                ->first();

            if (!$telefone) {
                continue;
            }

            RecrutamentoHistorico::registrar(
                $usuario->id,
                RecrutamentoHistorico::ACAO_TELEFONE_REMOVIDO,
                RecrutamentoHistorico::MODULO_TELEFONE,
                null,
                "Telefone {$telefone->numero} foi removido",
                $telefone->toArray(),
                null
            );

            $telefone->delete();
        }
    }

    private function garantirCurriculoUsuario(User $usuario): void
    {
        $curriculoExiste = Curriculo::withoutGlobalScopes()
            ->where('id', $usuario->id)
            ->exists();

        if ($curriculoExiste) {
            return;
        }

        Curriculo::withoutGlobalScopes()->create([
            'id' => $usuario->id,
            'cpf' => $this->cpfPlaceholderUsuario($usuario->id),
            'nome' => $usuario->nome,
            'email' => $usuario->login,
            'nascimento' => '1900-01-01',
        ]);
    }

    private function resolverEmpresaIdParaPreferencias(Request $request): int
    {
        $empresaId = (int) ($request->input('empresa_id') ?: auth()->user()->empresa_id);

        if (
            auth()->user()->empresa_id !== User::MYBP_EMPRESA_ID
            && $empresaId !== (int) auth()->user()->empresa_id
        ) {
            $empresaId = (int) auth()->user()->empresa_id;
        }

        return $empresaId;
    }

    private function whatsappLiberadoParaEmpresa(int $empresaId): bool
    {
        return (bool) ClienteConfig::query()
            ->where('cliente_id', $empresaId)
            ->value('envia_whatsapp');
    }

    /** @return array<int, array<string, mixed>> */
    private function preferenciasPadraoParaEmpresa(WhatsappConfigService $configService, int $empresaId): array
    {
        return array_map(static function (array $item) {
            return [
                'modulo' => $item['modulo'],
                'receber' => true,
                'habilitado_empresa' => (bool) $item['habilitado'],
                'tipos' => $item['tipos'],
            ];
        }, $configService->listModulosHabilitadosForApi($empresaId));
    }

    private function salvarWhatsappPreferenciasAdmin(User $usuario, mixed $preferencias): void
    {
        if (!is_array($preferencias)) {
            return;
        }

        app(WhatsappConfigService::class)->savePreferenciasUsuario((int) $usuario->id, $preferencias);
    }

    private function cpfPlaceholderUsuario(int $userId): string
    {
        return '9' . str_pad((string) $userId, 10, '0', STR_PAD_LEFT);
    }
}
