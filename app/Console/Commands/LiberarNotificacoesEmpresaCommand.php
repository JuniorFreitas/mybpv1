<?php

namespace App\Console\Commands;

use App\Services\Notificacao\LiberarNotificacaoEmpresaService;
use Illuminate\Console\Command;

class LiberarNotificacoesEmpresaCommand extends Command
{
    protected $signature = 'mybp:notificacoes-liberar
                            {empresa_id : ID da empresa (cliente)}
                            {--papel=* : IDs de papéis (opcional; senão detecta por âncoras config/RH)}
                            {--sem-grupo : Não cria/atualiza o grupo RH Geral}';

    protected $description = 'Libera Configurações › Notificações na empresa e opcionalmente cria o grupo RH Geral';

    public function handle(LiberarNotificacaoEmpresaService $service): int
    {
        $empresaId = (int) $this->argument('empresa_id');
        if ($empresaId <= 0) {
            $this->error('empresa_id inválido.');

            return self::FAILURE;
        }

        $papeisOpt = $this->option('papel');
        $papelIds = is_array($papeisOpt) && $papeisOpt !== []
            ? array_map('intval', $papeisOpt)
            : null;

        $resultado = $service->liberar(
            $empresaId,
            $papelIds,
            !(bool) $this->option('sem-grupo')
        );

        $this->info("Empresa {$empresaId}: habilidade #{$resultado['habilidade_id']} ok.");
        $this->line('Papéis liberados agora: ' . ( $resultado['papeis_liberados'] === []
            ? '(nenhum novo — já tinham a habilidade ou nenhum âncora)'
            : implode(', ', $resultado['papeis_liberados'])));

        if ($resultado['grupo_id']) {
            $acao = $resultado['grupo_criado'] ? 'criado' : 'atualizado';
            $this->info("Grupo RH Geral #{$resultado['grupo_id']} {$acao} com {$resultado['destinatarios']} destinatário(s).");
        } elseif (!(bool) $this->option('sem-grupo')) {
            $this->warn('Grupo RH Geral não foi criado (tipo aprovacao_rh ausente?).');
        }

        return self::SUCCESS;
    }
}
