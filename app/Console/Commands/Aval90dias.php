<?php

namespace App\Console\Commands;

use App\Jobs\EnviarEmailAvaliacaoNoventaDiasJob;
use App\Models\Sistema;
use App\Models\User;
use App\Services\AvaliacaoNoventaService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use MasterTag\DataHora;
use Throwable;

class Aval90dias extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mybp:avaliacao-experiencia {--empresa_id= : ID da empresa a processar} {--destinatario=usuarios : Tipo de destinatário: usuarios|gestores|ambos} {--usuario= : ID do usuário específico para enviar (ignora destinatario)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia notificações de vencimento de Avaliação de Experiência (colaboradores com até 180 dias de admissão)';

    /**
     * Alias para compatibilidade (cron/schedule que ainda usam o nome antigo).
     */
    protected function configure(): void
    {
        parent::configure();
        $this->setAliases(['mybp:avaliacao90dias']);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        try {
            $empresaIdOption = $this->option('empresa_id');
            $dataAtual = (new DataHora())->dataCompleta();
            $usuarioId = $this->option('usuario');

            $destinatario = strtolower($this->option('destinatario') ?? 'usuarios');
            if (!in_array($destinatario, ['usuarios', 'gestores', 'ambos'])) {
                $this->warn("⚠️  Opção --destinatario inválida. Usando 'usuarios'.");
                $destinatario = 'usuarios';
            }

            // Uma empresa específica (manual ou schedule com --empresa_id)
            if ($empresaIdOption !== null && $empresaIdOption !== '') {
                $empresaId = (int) $empresaIdOption;
                if ($usuarioId) {
                    return $this->processarParaUsuarioEspecifico($usuarioId, $empresaId);
                }
                return $this->processarUmaEmpresa($empresaId, $dataAtual, $destinatario);
            }

            // Schedule: todas as empresas habilitadas (lista configurável em cliente_configs)
            $empresasIds = Sistema::listaEmpresasParaScheduleAvaliacaoExperiencia();
            if (empty($empresasIds)) {
                $this->info('✅ Nenhuma empresa habilitada no schedule de Avaliação de Experiência.');
                return Command::SUCCESS;
            }

            $this->info('🔍 Processando Avaliação de Experiência para ' . count($empresasIds) . ' empresa(s) (máx. 180 dias de admissão)');
            $totalJobs = 0;
            foreach ($empresasIds as $empresaId) {
                $this->info("--- Empresa ID: {$empresaId} ---");
                $totalJobs += $this->processarUmaEmpresa($empresaId, $dataAtual, $destinatario);
            }
            $this->info("✅ Processo concluído! {$totalJobs} job(s) enfileirado(s) no total");
            return Command::SUCCESS;

        } catch (Throwable $e) {
            Log::error('Erro ao processar Avaliação de Experiência', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->error("❌ Erro: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    /**
     * Processa uma única empresa (RH e/ou gestores conforme destinatario).
     *
     * @return int Número de jobs enfileirados
     */
    private function processarUmaEmpresa(int $empresaId, string $dataAtual, string $destinatario): int
    {
        $this->info("🔍 Empresa {$empresaId} (destinatário: {$destinatario})");

        Auth::loginUsingId($empresaId);

        $service = new AvaliacaoNoventaService();
        $avaliacoes = $service->buscarAvaliacoesVencendoOuVencidas($empresaId, AvaliacaoNoventaService::DIAS_ANTECEDENCIA);

        if ($avaliacoes->isEmpty()) {
            $this->info("✅ Nenhuma avaliação vencida ou vencendo.");
            return 0;
        }

        $this->info("📋 {$avaliacoes->count()} avaliações para processar");
        $jobsEnviados = 0;

        if (in_array($destinatario, ['usuarios', 'ambos'])) {
            $usuariosNotificacao = $service->buscarUsuariosParaNotificacao($empresaId);
            if ($usuariosNotificacao->isEmpty()) {
                $this->warn("⚠️  Nenhum usuário (RH) com privilégio para receber notificações");
            } else {
                $jobsEnviados += $this->processarNotificacoes($usuariosNotificacao, $avaliacoes, $dataAtual, $empresaId, $service);
            }
        }

        if (in_array($destinatario, ['gestores', 'ambos'])) {
            $grupos = $service->montarVencimentosPorGestor($avaliacoes, $dataAtual);
            if ($grupos->isEmpty()) {
                $this->warn('⚠️  Nenhum gestor com vencimentos para notificar');
            } else {
                $jobsEnviados += $this->processarNotificacoesGestores($grupos, $empresaId, $service);
            }
        }

        return $jobsEnviados;
    }

    /**
     * Processa notificações e envia e-mails diretamente
     *
     * @param Collection $usuarios
     * @param Collection $avaliacoes
     * @param string $dataAtual
     * @param int $empresaId
     * @return int Número de e-mails enviados
     */
    private function processarNotificacoes(
        Collection $usuarios,
        Collection $avaliacoes,
        string $dataAtual,
        int $empresaId,
        AvaliacaoNoventaService $service
    ): int {
        $emailsEnviados = 0;

        foreach ($usuarios as $usuario) {
            $vencimentos = $service->montarVencimentos($avaliacoes, $dataAtual);

            $this->info("Debug - Usuário: {$usuario->nome} ({$usuario->login})");
            $this->info("Debug - Vencimentos encontrados: {$vencimentos->count()}");

            if ($vencimentos->isNotEmpty()) {
                try {
                    $this->info("Enfileirando job para: {$usuario->login}");
                    
                    // Gera o arquivo Excel e faz upload para S3
                    $arquivoS3 = $service->gerarExcelS3($vencimentos->toArray(), $empresaId);
                    
                    if (!$arquivoS3) {
                        throw new \Exception("Falha ao gerar arquivo Excel no S3");
                    }
                    
                    // Despacha o job para a fila
                    EnviarEmailAvaliacaoNoventaDiasJob::dispatch($usuario, $vencimentos->toArray(), $empresaId, $arquivoS3);

                    $this->info("✅ Job enfileirado com sucesso para {$usuario->nome}");

                    Log::info('Job de notificação de Avaliação de Experiência enfileirado', [
                        'usuario_id' => $usuario->id,
                        'usuario_nome' => $usuario->nome,
                        'usuario_email' => $usuario->login,
                        'empresa_id' => $empresaId,
                        'total_vencimentos' => $vencimentos->count()
                    ]);

                    $emailsEnviados++;
                    
                } catch (Throwable $e) {
                    Log::error('Erro ao enfileirar job de Avaliação de Experiência', [
                        'usuario_id' => $usuario->id,
                        'usuario_nome' => $usuario->nome,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'empresa_id' => $empresaId
                    ]);
                    
                    $this->error("❌ Erro ao enfileirar job para {$usuario->nome}: {$e->getMessage()}");
                }
            } else {
                $this->warn("⚠️  Nenhum vencimento encontrado para {$usuario->nome}");
            }
        }

        return $emailsEnviados;
    }

    /**
     * Processa notificações agrupadas por gestor (um e-mail por gestor, com seus colaboradores)
     */
    private function processarNotificacoesGestores(
        Collection $grupos,
        int $empresaId,
        AvaliacaoNoventaService $service
    ): int {
        $emailsEnviados = 0;

        foreach ($grupos as $grupo) {
            $usuario = $grupo['gestor'];
            $vencimentosArray = $grupo['vencimentos'];

            $this->info("Debug - Gestor: {$usuario->nome} ({$usuario->login})");
            $this->info("Debug - Vencimentos encontrados: " . count($vencimentosArray));

            if (!empty($vencimentosArray)) {
                try {
                    $this->info("Enfileirando job para: {$usuario->login}");

                    // Gera o arquivo Excel e faz upload para S3
                    $arquivoS3 = $service->gerarExcelS3($vencimentosArray, $empresaId);

                    if (!$arquivoS3) {
                        throw new \Exception('Falha ao gerar arquivo Excel no S3');
                    }

                    // Despacha o job para a fila
                    EnviarEmailAvaliacaoNoventaDiasJob::dispatch($usuario, $vencimentosArray, $empresaId, $arquivoS3);

                    $this->info("✅ Job enfileirado com sucesso para {$usuario->nome}");

                    $emailsEnviados++;
                } catch (\Throwable $e) {
                    Log::error('Erro ao enfileirar job de Avaliação de Experiência (gestores)', [
                        'usuario_id' => $usuario->id ?? null,
                        'usuario_nome' => $usuario->nome ?? null,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'empresa_id' => $empresaId
                    ]);

                    $this->error("❌ Erro ao enfileirar job para {$usuario->nome}: {$e->getMessage()}");
                }
            } else {
                $this->warn("⚠️  Nenhum vencimento encontrado para {$usuario->nome}");
            }
        }

        return $emailsEnviados;
    }

    /**
     * Processa notificação para um usuário específico
     */
    private function processarParaUsuarioEspecifico(int $usuarioId, int $empresaId): int
    {
        $this->info("🔍 Processando Avaliação de Experiência para usuário: {$usuarioId} (máx. 180 dias de admissão)");

        Auth::loginUsingId($empresaId);

        $service = new AvaliacaoNoventaService();
        $dataAtual = (new DataHora())->dataCompleta();

        // Buscar o usuário
        $usuario = User::find($usuarioId);
        if (!$usuario) {
            $this->error("❌ Usuário {$usuarioId} não encontrado");
            return Command::FAILURE;
        }

        // Regra dos 180 dias: somente colaboradores com até 180 dias de admissão
        $avaliacoes = $service->buscarAvaliacoesVencendoOuVencidas($empresaId, AvaliacaoNoventaService::DIAS_ANTECEDENCIA);
        
        if ($avaliacoes->isEmpty()) {
            $this->info("✅ Nenhuma avaliação vencida ou vencendo encontrada.");
            return Command::SUCCESS;
        }

        $this->info("📋 Encontradas {$avaliacoes->count()} avaliações para processar");

        // Montar vencimentos
        $vencimentos = $service->montarVencimentos($avaliacoes, $dataAtual);

        if ($vencimentos->isEmpty()) {
            $this->info("✅ Nenhum vencimento encontrado para enviar.");
            return Command::SUCCESS;
        }

        try {
            $this->info("Enfileirando job para: {$usuario->login}");
            
            // Gera o arquivo Excel e faz upload para S3
            $arquivoS3 = $service->gerarExcelS3($vencimentos->toArray(), $empresaId);
            
            if (!$arquivoS3) {
                throw new \Exception("Falha ao gerar arquivo Excel no S3");
            }
            
            // Despacha o job para a fila (e-mail com anexo)
            EnviarEmailAvaliacaoNoventaDiasJob::dispatch($usuario, $vencimentos->toArray(), $empresaId, $arquivoS3);

            $this->info("✅ Job enfileirado com sucesso para {$usuario->nome}");

            Log::info('Job de notificação de Avaliação de Experiência enfileirado (usuário específico)', [
                'usuario_id' => $usuario->id,
                'usuario_nome' => $usuario->nome,
                'usuario_email' => $usuario->login,
                'empresa_id' => $empresaId,
                'total_vencimentos' => $vencimentos->count()
            ]);

            return Command::SUCCESS;
            
        } catch (Throwable $e) {
            Log::error('Erro ao enfileirar job de Avaliação de Experiência (usuário específico)', [
                'usuario_id' => $usuario->id,
                'usuario_nome' => $usuario->nome,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'empresa_id' => $empresaId
            ]);
            
            $this->error("❌ Erro ao enfileirar job para {$usuario->nome}: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
   
}
