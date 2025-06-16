<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ConfigurePasswordReset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:configure-password-reset 
                            {--user-id= : ID específico do usuário para configurar}
                            {--empresa-id= : ID da empresa para configurar todos os usuários}
                            {--enable : Habilita o reset forçado de senha}
                            {--disable : Desabilita o reset forçado de senha}
                            {--days=90 : Número de dias para forçar reset (padrão: 90)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configura o reset forçado de senha para usuários';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $userId = $this->option('user-id');
        $empresaId = $this->option('empresa-id');
        $enable = $this->option('enable');
        $disable = $this->option('disable');
        $days = (int) $this->option('days');

        if (!$enable && !$disable) {
            $this->error('Você deve especificar --enable ou --disable');
            return 1;
        }

        if ($enable && $disable) {
            $this->error('Não é possível usar --enable e --disable ao mesmo tempo');
            return 1;
        }

        $requireReset = $enable ? true : false;
        $resetDays = $requireReset ? $days : null;

        // Construir a query base
        $query = User::query();

        if ($userId) {
            $query->where('id', $userId);
            $this->info("Configurando usuário ID: {$userId}");
        } elseif ($empresaId) {
            $query->where('empresa_id', $empresaId);
            $this->info("Configurando usuários da empresa ID: {$empresaId}");
        } else {
            if (!$this->confirm('Deseja configurar TODOS os usuários do sistema?')) {
                $this->info('Operação cancelada.');
                return 0;
            }
            $this->info("Configurando todos os usuários do sistema");
        }

        $users = $query->get();

        if ($users->isEmpty()) {
            $this->warn('Nenhum usuário encontrado com os critérios especificados.');
            return 0;
        }

        $this->info("Encontrados {$users->count()} usuários para configurar.");

        $progressBar = $this->output->createProgressBar($users->count());
        $progressBar->start();

        $updated = 0;
        foreach ($users as $user) {
            try {
                $user->update([
                    'require_password_reset' => $requireReset,
                    'password_reset_days' => $resetDays,
                    'password_changed_at' => $user->password_changed_at ?? now(),
                ]);
                $updated++;
            } catch (\Exception $e) {
                $this->error("\nErro ao atualizar usuário ID {$user->id}: " . $e->getMessage());
            }
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();

        $action = $requireReset ? 'habilitado' : 'desabilitado';
        $this->info("Reset de senha {$action} para {$updated} usuários com sucesso!");
        
        if ($requireReset) {
            $this->info("Usuários precisarão alterar a senha a cada {$days} dias.");
        }

        return 0;
    }
}
