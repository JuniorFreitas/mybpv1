<?php

namespace App\Http\Controllers;

use App\Jobs\JobBoasVindas;
use App\Jobs\JobRecuperaSenha;
use App\Models\Arquivo;
use App\Models\Cliente;
use App\Models\ClienteConfig;
use App\Models\ClienteFilial;
use App\Models\GrupoCloud;
use App\Models\Papel;
use App\Models\RecuperacaoSenha;
use App\Models\TipoRecebeEmail;
use App\Models\User;
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
        $listaDePapeis = Papel::all();
        return view('g.usuarios.usuarios.index', compact('listaDePapeis'));
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

        $usuario = User::create($dados);

        if (isset($dados['user_recebe_email'])) {
            unset($dados['user_recebe_email'][0]);
            foreach ($dados['user_recebe_email'] as $index => $email) {
                $usuario->UserRecebeEmail()->attach($index, ['ativo' => $email == null ? false : true]);
            }
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

        $ids_form = array();
        foreach ($usuario->UserRecebeEmail as $f) {
            $ids_form[$f->pivot->tipo_email_id] = $f->pivot->ativo;
        }

        $formulario_vazio = collect($ids_form);

        $papeis = Papel::whereEmpresaId($usuario->empresa_id)->NotClinica()->orderBy('nome');
        $cloud = GrupoCloud::whereEmpresaId($usuario->empresa_id)->orderBy('nome')->get();
        return response()->json(['usuario' => $usuario, 'papeis' => $papeis->get(), 'cloud' => $cloud, 'formulario_vazio' => $formulario_vazio], 200);
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
            $usuario->update($dados);
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
                'whatsappLiberado' => $whatsappLiberado ? $whatsappLiberado->envia_whatsapp : false,
                'temFilial' => (bool)$temfilial,
                'apelido' => Cliente::select('apelido')->whereId(auth()->user()->empresa_id)->first()->apelido
            ];

        } else {
//            $usuario = auth()->user()->ClientesEmpresa()->select(['id'])->with('Cliente:id,area_id')->get();
            $usuario = [
                'cliente_id' => auth()->user()->empresa_id,
                'area_id' => 0,
                'config_empresa' => auth()->user()->EmpresaPontoConfiguracoes,
                'empresa_id' => auth()->user()->empresa_id,
                'user_id' => auth()->id(),
                'whatsappLiberado' => $whatsappLiberado ? $whatsappLiberado->envia_whatsapp : false,
                'temFilial' => (bool)$temfilial,
                'apelido' => Cliente::select('apelido')->whereId(auth()->user()->empresa_id)->first()->apelido
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
        $user = User::select('id', 'ativo')->find($request->id);
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
                    'token' => \Str::random(8),
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

    public function recuperaSenha(Request $request, $token)
    {
        $recuperacao = RecuperacaoSenha::whereToken($token)->whereRecuperado(false)->where('expiracao', '>=', (new DataHora())->dataHoraInsert())->first();

        if ($recuperacao) {
            $recuperacao->update([
                'ip_recuperacao' => $request->ip(),
                'recuperacao' => (new DataHora())->dataInsert(),
                'recuperado' => true
            ]);

            $recuperacao->user()->update([
                'password' => bcrypt($recuperacao->token)
            ]);

            \Auth::login($recuperacao->user);

            return redirect()->route('g.usuarios.alterar-senha.index');
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

    public function anexoDelete(Request $request, $arquivo)
    {
        return Arquivo::anexoDelete(Arquivo::DISCO_PERFIL_USUARIO, $arquivo);
    }

    //anexo ou foto
    public function download(Request $request, $arquivo)
    {
        return Arquivo::anexoDownload(Arquivo::DISCO_PERFIL_USUARIO, $arquivo);
    }
}
