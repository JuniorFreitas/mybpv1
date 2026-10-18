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
    private function escopoEmpresa()
    {
        return User::where('empresa_id', auth()->user()->empresa_id);
    }

    private function filtro(Request $request)
    {
        $resultado = $this->escopoEmpresa()
            ->orderBy($request->ordemCampo ?: 'nome', $request->ordem ?: 'Asc');

        if ($request->filtroPeriodo) {
            $periodo = explode(' até ', $request->periodo);
            $dataInicio = new DataHora(trim($periodo[0]));
            $dataFim = new DataHora(trim($periodo[1]));
            $resultado->where('created_at', '>=', $dataInicio->dataInsert())
                ->where('created_at', '<=', $dataFim->dataInsert() . ' 23:59:59');
        }

        if ($request->filled('campoBusca')) {
            $resultado->where(function ($q) use ($request) {
                $q->where('nome', 'like', '%' . $request->campoBusca . '%')
                    ->orWhere('id', $request->campoBusca);
            });
        }

        if ($request->filled('campoAtivo')) {
            $resultado->whereAtivo($request->campoAtivo);
        }

        return $resultado;
    }

    private function garantirMesmaEmpresa(User $usuario)
    {
        if ((int) $usuario->empresa_id !== (int) auth()->user()->empresa_id) {
            abort(403, 'Acesso negado');
        }
    }

    public function index(Request $request)
    {
        $resultado = $this->filtro($request)->paginate($request->porPag ?: 20);

        return Sistema::pg($resultado);
    }

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
            $dados = $validator->validated();
            $dados['empresa_id'] = auth()->user()->empresa_id;
            $dados['grupo_id'] = $request->input('papel_id', $request->input('grupo_id'));
            unset($dados['password_confirmation']);
            if (isset($dados['password'])) {
                $dados['password'] = bcrypt($dados['password']);
            }
            $user = User::create($dados);

            return response()->json($user, 201);
        } catch (\Exception $e) {
            return response()->json(['msg' => $e->getMessage(), 'error' => true], 400);
        }
    }

    public function show(User $usuario)
    {
        $this->garantirMesmaEmpresa($usuario);

        return $usuario;
    }

    public function update(Request $request, User $usuario)
    {
        if (!auth()->user()->tokenCan('usuario_update')) {
            return response()->json([
                'msg' => 'Acesso negado',
                'error' => true
            ], 403);
        }

        $this->garantirMesmaEmpresa($usuario);

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
            $dados = $validator->validated();
            unset($dados['password_confirmation'], $dados['alterar_senha']);
            $dados['grupo_id'] = $request->input('papel_id');
            unset($dados['papel_id']);

            if (!$request->boolean('alterar_senha')) {
                unset($dados['password']);
            } elseif (isset($dados['password'])) {
                $dados['password'] = bcrypt($dados['password']);
            }

            // Nunca permitir mudança de tenant/token via API genérica
            unset($dados['empresa_id'], $dados['api_token']);

            $usuario->update($dados);

            return response()->json($usuario);
        } catch (\Exception $e) {
            return response()->json(['msg' => $e->getMessage(), 'error' => true], 400);
        }
    }

    public function ativaDesativa(User $usuario)
    {
        if (!auth()->user()->tokenCan('usuario_ativar_desativar')) {
            return response()->json([
                'msg' => 'Acesso negado',
                'error' => true
            ], 403);
        }

        $this->garantirMesmaEmpresa($usuario);

        return Sistema::ativaDesativa($usuario);
    }
}
