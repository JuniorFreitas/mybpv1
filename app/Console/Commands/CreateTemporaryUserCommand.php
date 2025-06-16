<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateTemporaryUserCommand extends Command
{
    protected $signature = 'demo:create-temporary-user 
                            {--email= : Email do usuário para criar}
                            {--name= : Nome do usuário}
                            {--temp : Criar usuário com senha temporária}
                            {--first-access : Simular primeiro acesso (sem password_changed_at)}
                            {--empresa-id=100 : ID da empresa}';

    protected $description = 'Cria um usuário para demonstrar o sistema de primeiro acesso e senha temporária';

    public function handle()
    {
        $email = $this->option('email') ?: $this->ask('Email do usuário');
        $name = $this->option('name') ?: $this->ask('Nome do usuário');
        $empresaId = $this->option('empresa-id');
        $isTemp = $this->option('temp');
        $isFirstAccess = $this->option('first-access');

        // Verifica se já existe usuário com esse email
        $existingUser = User::where('login', $email)->first();
        if ($existingUser) {
            $this->error("Usuário com email {$email} já existe!");
            return 1;
        }

        $password = Str::random(8);
        
        $userData = [
            'nome' => $name,
            'login' => $email,
            'password' => Hash::make($password),
            'tipo' => User::FUNCIONARIO,
            'empresa_id' => $empresaId,
            'ativo' => true,
            'temp' => $isTemp,
        ];

        // Se não for primeiro acesso, define a data de alteração
        if (!$isFirstAccess) {
            $userData['password_changed_at'] = now();
        }

        $user = User::create($userData);

        $this->info("Usuário criado com sucesso!");
        $this->table(
            ['Campo', 'Valor'],
            [
                ['ID', $user->id],
                ['Nome', $user->nome],
                ['Email', $user->login],
                ['Senha temporária', $password],
                ['Primeiro acesso', $isFirstAccess ? 'Sim' : 'Não'],
                ['Senha temporária', $isTemp ? 'Sim' : 'Não'],
                ['Empresa ID', $user->empresa_id],
            ]
        );

        $this->warn("IMPORTANTE: Anote a senha temporária: {$password}");
        
        if ($isFirstAccess) {
            $this->comment("Este usuário será obrigado a alterar a senha no primeiro login.");
        }
        
        if ($isTemp) {
            $this->comment("Este usuário tem senha temporária e será obrigado a alterá-la.");
        }

        return 0;
    }
} 