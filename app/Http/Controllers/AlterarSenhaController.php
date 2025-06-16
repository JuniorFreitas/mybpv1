<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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


    public function update(Request $request)
    {
        $this->authorize('usuario_alterar-senha');

        $dadosValidados = \Validator::make($request->only('password','password_confirmation'), [
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
            ],
        ], [
            'password.required' => 'A nova senha é obrigatória.',
            'password.confirmed' => 'A confirmação da senha não confere.',
            'password.min' => 'A senha deve ter no mínimo 8 caracteres.',
            'password.regex' => 'A senha deve conter pelo menos: 1 letra minúscula, 1 maiúscula, 1 número e 1 caractere especial (@$!%*?&).',
        ]);
        
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao atualizar a senha',
                'erros' => $dadosValidados->errors()
            ], 400);
        }

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
