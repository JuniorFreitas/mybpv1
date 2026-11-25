<?php

namespace App\Http\Controllers;

use App\Http\Requests\Usuarios\UpdateAlterarSenhaRequest;

class AlterarSenhaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('g.usuarios.alterar-senha.index');
    }


    public function update(UpdateAlterarSenhaRequest $request)
    {
        /** @var \App\Models\User $usuario */
        $usuario = auth()->user();

        // Verificar se a nova senha não é igual à atual
        if (\Hash::check($request->password, $usuario->password)) {
            return response()->json([
                'msg' => 'Nova senha inválida',
                'erros' => ['password' => ['A nova senha deve ser diferente da senha atual.']]
            ], 400);
        }

        $usuario->update([
            'password' => bcrypt($request->password),
            'password_changed_at' => now(),
            'temp' => false // Remove a flag de senha temporária
        ]);

        return response()->json([
            'msg' => 'Senha alterada com sucesso!'
        ], 201);
    }
}
