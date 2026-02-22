<?php

namespace App\Console\Commands;

use App\Jobs\EnviarEmailAvaliacaoNoventaDiasJob;
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
    protected $signature = 'mybp:avaliacao90dias {--empresa_id= : ID da empresa a processar} {--destinatario=usuarios : Tipo de destinatário: usuarios|gestores|ambos} {--usuario= : ID do usuário específico para enviar (ignora destinatario)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia notificações de vencimento de avaliações de 90 dias';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        try {
            $empresaId = $this->option('empresa_id') ?? 73473;
            $dataAtual = (new DataHora())->dataCompleta();
            $usuarioId = $this->option('usuario');

            // Se especificou um usuário, envia apenas para ele
            if ($usuarioId) {
                return $this->processarParaUsuarioEspecifico($usuarioId, $empresaId);
            }

            $destinatario = strtolower($this->option('destinatario') ?? 'usuarios');
            if (!in_array($destinatario, ['usuarios', 'gestores', 'ambos'])) {
                $this->warn("⚠️  Opção --destinatario inválida. Usando 'usuarios'.");
                $destinatario = 'usuarios';
            }

            $this->info("🔍 Processando avaliações de 90 dias para empresa: {$empresaId} (destinatário: {$destinatario})");

            Auth::loginUsingId($empresaId);

            $service = new AvaliacaoNoventaService();

            // Buscar avaliações vencidas ou próximas do vencimento (mesma regra do relatório: somente < 180 dias de admissão)
            $avaliacoes = $service->buscarAvaliacoesVencendoOuVencidas($empresaId, AvaliacaoNoventaService::DIAS_ANTECEDENCIA);
            
            if ($avaliacoes->isEmpty()) {
                $this->info("✅ Nenhuma avaliação vencida ou vencendo encontrada.");
                return Command::SUCCESS;
            }

            $this->info("📋 Encontradas {$avaliacoes->count()} avaliações para processar");

            $jobsEnviados = 0;

            // Envio para usuários (RH) com habilidade privilegio_gestao_rh
            if (in_array($destinatario, ['usuarios', 'ambos'])) {
                $usuariosNotificacao = $service->buscarUsuariosParaNotificacao($empresaId);
                if ($usuariosNotificacao->isEmpty()) {
                    $this->warn("⚠️  Nenhum usuário (RH) com privilégio para receber notificações");
                } else {
                    $this->info("👥 Usuários RH a notificar: {$usuariosNotificacao->count()}");
                    $jobsEnviados += $this->processarNotificacoes($usuariosNotificacao, $avaliacoes, $dataAtual, $empresaId, $service);
                }
            }

            // Envio para gestores (um e-mail por gestor com seus colaboradores)
            if (in_array($destinatario, ['gestores', 'ambos'])) {
                $grupos = $service->montarVencimentosPorGestor($avaliacoes, $dataAtual);
                if ($grupos->isEmpty()) {
                    $this->warn('⚠️  Nenhum gestor com vencimentos para notificar');
                } else {
                    $this->info("👥 Gestores a notificar: {$grupos->count()}");
                    $jobsEnviados += $this->processarNotificacoesGestores($grupos, $empresaId, $service);
                }
            }

            $this->info("✅ Processo concluído! {$jobsEnviados} job(s) enfileirado(s) para envio");

            return Command::SUCCESS;

        } catch (Throwable $e) {
            Log::error('Erro ao processar avaliações de 90 dias', [
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

                    Log::info('Job de notificação de avaliação 90 dias enfileirado', [
                        'usuario_id' => $usuario->id,
                        'usuario_nome' => $usuario->nome,
                        'usuario_email' => $usuario->login,
                        'empresa_id' => $empresaId,
                        'total_vencimentos' => $vencimentos->count()
                    ]);

                    $emailsEnviados++;
                    
                } catch (Throwable $e) {
                    Log::error('Erro ao enfileirar job de avaliação 90 dias', [
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
                    Log::error('Erro ao enfileirar job de avaliação 90 dias (gestores)', [
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
        $this->info("🔍 Processando avaliações de 90 dias para usuário: {$usuarioId}");

        Auth::loginUsingId($empresaId);

        $service = new AvaliacaoNoventaService();
        $dataAtual = (new DataHora())->dataCompleta();

        // Buscar o usuário
        $usuario = User::find($usuarioId);
        if (!$usuario) {
            $this->error("❌ Usuário {$usuarioId} não encontrado");
            return Command::FAILURE;
        }

        // Buscar avaliações vencidas ou próximas do vencimento (mesma regra: somente < 180 dias de admissão)
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

            Log::info('Job de notificação de avaliação 90 dias enfileirado (usuário específico)', [
                'usuario_id' => $usuario->id,
                'usuario_nome' => $usuario->nome,
                'usuario_email' => $usuario->login,
                'empresa_id' => $empresaId,
                'total_vencimentos' => $vencimentos->count()
            ]);

            return Command::SUCCESS;
            
        } catch (Throwable $e) {
            Log::error('Erro ao enfileirar job de avaliação 90 dias (usuário específico)', [
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
