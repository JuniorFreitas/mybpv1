<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sistema;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use MasterTag\DataHora;

class UsuarioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    private function filtro(Request $request)
    {
        $resultado = User::orderBy($request->ordemCampo ?: 'nome', $request->ordem ?: 'Asc');

        if ($request->filtroPeriodo) {
            $periodo = explode(' até ', $request->periodo);
            $dataInicio = new DataHora(trim($periodo[0]));
            $dataFim = new DataHora(trim($periodo[1]));
            $resultado->where('created_at', '>=', $dataInicio->dataInsert())
                ->where('created_at', '<=', $dataFim->dataInsert() . ' 23:59:59');
        }

        if ($request->filled('campoBusca')) {
            $resultado->where('nome', 'like', '%' . $request->campoBusca . '%')
                ->orWhere('id', $request->campoBusca);
        }

        if ($request->filled('campoAtivo')) {
            $resultado->whereAtivo($request->campoAtivo);
        }

        return $resultado;
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $resultado = $this->filtro($request)->paginate($request->porPag ?: 20);
        return Sistema::pg($resultado);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if (!auth()->user()->tokenCan('usuario_create')) {
            return response()->json([
                'msg' => 'Acesso negado',
                'error' => true
            ], 403);
        }

        $validator = Validator::make($request->input(), [
            'nome' => 'required|max:255',
            'email' => 'required|email|unique:users',
            'tipo' => 'required',
            'papel_id' => 'required',
            'telefone' => 'required|unique:users|max:14',
            'password' => 'min:6|required_with:password_confirmation|same:password_confirmation',
            'password_confirmation' => 'min:6'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->messages(), 400);
        }

        try {
            $user = User::create($request->input());
            return response()->json($user, 201);
        } catch (\Exception $e) {
            return response()->json(['msg' => $e->getMessage(), 'error' => true], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\User $usuario
     * @return User|\Illuminate\Http\Response
     */
    public function show(User $usuario)
    {
        return $usuario;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User $usuario
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function update(Request $request, User $usuario)
    {
        if (!auth()->user()->tokenCan('usuario_update')) {
            return response()->json([
                'msg' => 'Acesso negado',
                'error' => true
            ], 403);
        }

        $validator = Validator::make($request->input(), [
            'nome' => 'required|max:255',
            'email' => 'required|email|unique:users,email,' . $usuario->id,
            'tipo' => 'required',
            'papel_id' => 'required',
            'telefone' => 'required|max:14|unique:users,telefone,' . $usuario->id,
            'alterar_senha' => 'boolean',
            'password' => 'sometimes|min:6|required_with:password_confirmation|same:password_confirmation',
            'password_confirmation' => 'sometimes|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 400);
        }

        try {
            $dados = $request->input();

            if (!$dados['alterar_senha']) {
                unset($dados['password']);
            }

            $usuario->update($dados);
            return response()->json($usuario);
        } catch (\Exception $e) {
            return response()->json(['msg' => $e->getMessage(), 'error' => true], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\User $usuario
     * @return \Illuminate\Http\JsonResponse
     */
    public function ativaDesativa(User $usuario)
    {
        if (!auth()->user()->tokenCan('usuario_ativar_desativar')) {
            return response()->json([
                'msg' => 'Acesso negado',
                'error' => true
            ], 403);
        }

        return Sistema::ativaDesativa($usuario);
    }

}
