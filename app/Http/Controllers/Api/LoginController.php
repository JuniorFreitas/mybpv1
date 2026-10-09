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

        $credenciaisValidas = $usuario
            && $usuario->ativo
            && password_verify($request->senha, $usuario->password);

        if ($credenciaisValidas) {
            // Revoga tokens anteriores e não grava plain text em users.api_token
            $usuario->tokens()->delete();
            $habilidades = $usuario->Papel->Habilidades->pluck('nome')->toArray();
            $token = $usuario->createToken($usuario->tipo, $habilidades);

            return response()->json([
                "token" => $token->plainTextToken,
                "success" => true
            ]);
        }

        // Resposta uniforme (não diferenciar desativado vs senha inválida)
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
