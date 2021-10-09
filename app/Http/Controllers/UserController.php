<?php

namespace App\Http\Controllers;

use App\Models\Arquivo;
use App\Models\GrupoCloud;
use App\Models\Papel;
use App\Models\User;
use Illuminate\Http\Request;

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
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('usuarios_insert');
        $dados = $request->input();
        $dados['ativo'] = $dados['ativo'] == 'true' ? true : false;


        $dadosValidados = \Validator::make($dados, [
            'nome' => 'required|min:3',
            'login' => 'unique:users,login',
            'password' => 'required|confirmed|min:3',
            'grupo_id' => 'required|numeric',
            'tipo' => 'required',
            'grupo_cloud_id' => 'required|numeric',
            'ativo' => 'required|boolean',
            'empresa_id' => 'required'
        ]);
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao cadastrar usuário',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {

//            $dados['tipo'] = Papel::find($dados['grupo_id'])->nome;
            $dados['password'] = bcrypt($dados['password']);
            $dados['cadastrou'] = auth()->id();


            User::create($dados);
            return response()->json([], 201);
        }
    }


    public function show(User $user)
    {
        //
    }


    public function edit(User $usuario)
    {
        $this->authorize('usuarios_update');
        $usuario->load('papel:id,nome', 'Empresa');

        $papeis = Papel::whereEmpresaId($usuario->empresa_id)->orderBy('nome')->get();
        $cloud = GrupoCloud::whereEmpresaId($usuario->empresa_id)->orderBy('nome')->get();

        return response()->json(['usuario' => $usuario, 'papeis' => $papeis,'cloud' => $cloud], 200);
    }


    public function update(Request $request, User $usuario)
    {
        $this->authorize('usuarios_update');
        $dados = $request->input();
        $dados['ativo'] = $dados['ativo'] == 'true' ? true : false;
        $dados['alterarSenha'] = $dados['alterarSenha'] == 'true' ? true : false;

        if ($dados['alterarSenha']) {
            if ($dados['password'] !== $dados['password_confirmation']) {
                return response()->json([
                    'msg' => 'Senhas não conscidem',
                ], 400);
            }
        } else {
            unset($dados['password']);
        }

        // Validacao para ajax sem dar erro de HTTP (402)
        $dadosValidados = \Validator::make($dados, [
            'nome' => 'required|min:3',
            'login' => 'unique:users,login,' . $usuario->id,
            'grupo_id' => 'required|numeric',
            'grupo_cloud_id' => 'required|numeric',
            'ativo' => 'required|boolean',
            'tipo' => 'required',
        ]);
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao atualizar os dados do usuário',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            if ($dados['alterarSenha']) {
                $dados['password'] = bcrypt($dados['password']);
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
        $this->authorize('usuarios_delete');
        $usuario->delete();
    }

    public function getUsuario()
    {
//        $usuario = User::find(auth()->id(), ['id', 'cliente_id'])->load('Cliente:id,area_id');
        $cliente = auth()->user()->ClienteFuncionarios->first();
        //$cliente = auth()->user()->ClienteFuncionarios()->where('cliente_id',auth()->id())->first();

        if ($cliente) {
            $usuario = [
                'cliente_id' => $cliente->Cliente->id,
                'area_id' => $cliente->Cliente->area_id,
                'config_empresa' => auth()->user()->ConfigEmpresa,
                'empresa_id' => auth()->user()->empresa_id,
                'user_id' => auth()->id()
            ];

        } else {
//            $usuario = auth()->user()->ClientesEmpresa()->select(['id'])->with('Cliente:id,area_id')->get();
            $usuario = [
                'cliente_id' => 0,
                'area_id' => 0,
                'config_empresa' => auth()->user()->ConfigEmpresa,
                'empresa_id' => auth()->user()->empresa_id,
                'user_id' => auth()->id(),
                'papeis'
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

        $this->authorize('usuarios_update');
        $user = User::select('id', 'ativo')->find($request->id);
        $user->ativo = !$user->ativo;
        $user->save();
        $user->refresh();
        return response()->json(['ativo' => $user->ativo], 201);
    }

    public function atualizar(Request $request)
    {
        $this->authorize('usuarios');
        $porPagina = $request->get('porPagina');

        if (auth()->user()->empresa_id === 104) {
            $resultado = User::with('Papel:id,nome', 'Empresa')
                ->where('tipo', '!=', 'Pessoa')
                ->where('tipo', '!=', 'Empresa');
        } else {
            $resultado = User::with('Papel:id,nome')
                ->where('empresa_id', auth()->user()->empresa_id);
        }

        if ($request->filled('campoBusca')) {
            $resultado->where('nome', 'like', '%' . $request->campoBusca . '%')
                ->orWhere('id', $request->campoBusca);
        }

        $empresa = auth()->user()->empresa_id;

        $resultado = $resultado->orderBy('nome')->paginate($porPagina);
        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'resultado' => $resultado->items(),
                'empresa' => $empresa
            ],
        ]);

    }

    public function buscaGrupoEmpresa($empresa_id)
    {
        $papeis = Papel::whereEmpresaId($empresa_id)->get();
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
        return Arquivo::anexoDelete([Arquivo::DISCO_PERFIL_USUARIO], $arquivo);
    }

    //anexo ou foto
    public function download(Request $request, $arquivo)
    {
        return Arquivo::anexoDownload(Arquivo::DISCO_PERFIL_USUARIO, $arquivo);
    }
}
