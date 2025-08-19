<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EnviarEmailTreinamentoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutos
    public $tries = 3;     // 3 tentativas
    public $backoff = [30, 60, 120]; // Backoff progressivo

    private array $empresa;
    private array $loteUsuarios;
    private array $dadosEmail;
    private int $numeroLote;

    public function __construct(array $empresa, array $loteUsuarios, array $dadosEmail, int $numeroLote)
    {
        $this->empresa = $empresa;
        $this->loteUsuarios = $loteUsuarios;
        $this->dadosEmail = $dadosEmail;
        $this->numeroLote = $numeroLote;

        // Configurar queue e delay
        $this->onQueue('emails');
    }

    public function handle(): void
    {
        try {
            Log::info("Iniciando envio de email - Lote {$this->numeroLote}", [
                'empresa_id' => $this->empresa['id'],
                'total_usuarios' => count($this->loteUsuarios)
            ]);

            // Preparar destinatários
            $usuarioPrincipal = $this->loteUsuarios[0];
            $usuariosCopia = array_slice($this->loteUsuarios, 1);

            // Verificar template de email
            $viewTemplate = $this->obterTemplateEmail();

            // Enviar email
            Mail::send(
                ['html' => $viewTemplate],
                $this->dadosEmail,
                function ($message) use ($usuarioPrincipal, $usuariosCopia) {
                    $message->from('naoresponda@mybp.com.br', 'Sistema MyBP');

                    $assunto = "[MyBP] Relatório de Vencimentos de Treinamentos (Excel) - {$this->empresa['razao_social']}";
                    $message->subject($assunto);

                    $message->to($usuarioPrincipal['email'], $usuarioPrincipal['nome']);

                    // Headers otimizados
                    $this->adicionarHeaders($message);

                    // Adicionar cópias ocultas
                    foreach ($usuariosCopia as $usuario) {
                        $message->bcc($usuario['email'], $usuario['nome']);
                    }
                }
            );

            Log::info("Email enviado com sucesso - Lote {$this->numeroLote}", [
                'empresa_id' => $this->empresa['id'],
                'destinatarios' => count($this->loteUsuarios)
            ]);

        } catch (\Exception $e) {
            Log::error("Erro ao enviar email - Lote {$this->numeroLote}", [
                'empresa_id' => $this->empresa['id'],
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Re-throw para trigger retry
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Falha definitiva no envio de email - Lote {$this->numeroLote}", [
            'empresa_id' => $this->empresa['id'],
            'total_usuarios' => count($this->loteUsuarios),
            'erro' => $exception->getMessage(),
            'tentativas' => $this->attempts()
        ]);
    }

    private function obterTemplateEmail(): string
    {
        $templates = [
            'email.treinamento.vencendo_excel_s3',
            'email.treinamento.vencendo_s3',
            'email.treinamento.vencendo',
        ];

        foreach ($templates as $template) {
            if (view()->exists($template)) {
                return $template;
            }
        }

        return 'email.treinamento.vencendo_s3';
    }

    private function adicionarHeaders($message): void
    {
        try {
            if (method_exists($message, 'getSwiftMessage') && $message->getSwiftMessage()) {
                $headers = $message->getSwiftMessage()->getHeaders();
                if ($headers) {
                    $headers->addTextHeader('X-Mailer', 'MyBP Sistema v2.0 Async');
                    $headers->addTextHeader('X-Batch-Number', (string)$this->numeroLote);
                    $headers->addTextHeader('X-Priority', '3');
                    $headers->addTextHeader('X-Report-Type', 'Excel-Async');
                    $headers->addTextHeader('X-Job-ID', $this->job->getJobId() ?? 'unknown');
                }
            }
        } catch (\Exception $e) {
            // Ignorar erros de headers
            Log::warning("Erro ao adicionar headers", ['erro' => $e->getMessage()]);
        }
    }
}
