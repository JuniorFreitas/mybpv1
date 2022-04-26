<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class LoginController extends Controller
{

    public function login(Request $request)
    {
        $usuario = User::whereLogin($request->login)->whereEmpresaId($request->empresa_id)->first();

        if ($usuario && $usuario->ativo && password_verify($request->senha, $usuario->password)) {
            $habilidades = $usuario->Papel->Habilidades->pluck('nome')->toArray();
            $token = $usuario->createToken($usuario->tipo, $habilidades);

            $usuario->update(['api_token' => $token->plainTextToken]);

            return response()->json([
                "token" => $token->plainTextToken,
                "success" => true
            ]);
        }

        if ($usuario && !$usuario->ativo && password_verify($request->senha, $usuario->password)) {
            $usuario->tokens()->delete();
            return response()->json([
                'msg' => 'Usuário desativado',
                'success' => false
            ], 401);
        }

        return response()->json([
            'msg' => 'Usuário ou senha inválidos',
            'success' => false
        ], 403);
    }

    public function logout(Request $request)
    {
        auth()->user()->tokens()->delete();
        return response()->json([]);
    }
}
