<?php

namespace App\Jobs\AdmissoesPrevista;

use App\Helpers\RHHelper;
use App\Mail\AdmissoesPrevista\NotificacaoAprovacaoMail;
use App\Models\AdmissoesPrevista;
use App\Models\AprovacaoExtraConfig;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class JobNotificacaoRecursiva implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $admissao;
    protected $config;
    protected $emailsRH;
    protected $usuariosCarregados;

    public $timeout = 300;
    public $tries = 3;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(AdmissoesPrevista $admissao)
    {
        // Eager load relationships para evitar N+1
        $this->admissao = $admissao->load([
            'UserCadastrou:id,nome,login',
            'UserAprovacao:id,nome,login',
            'Cargo:id,nome',
            'CentroCusto:id,label'
        ]);

        $this->usuariosCarregados = [];
    }

    /**
     * Execute the job - Lógica recursiva de notificações
     *
     * @return void
     */
    public function handle()
    {
        Log::info("=== JOB NOTIFICAÇÃO RECURSIVA - INICIADO ===");
        Log::info("Admissão ID: {$this->admissao->id}");
        Log::info("Status Aprovação: {$this->admissao->status_aprovacao}");
        Log::info("Status Aprovação Extra: {$this->admissao->status_aprovacao_extra}");
        Log::info("Status RH: {$this->admissao->status_aprovacao_rh}");

        // Cache config e emails RH (queries executadas 1x)
        $this->config = AprovacaoExtraConfig::getConfigAtiva($this->admissao->empresa_id, 'admissao');
        $this->emailsRH = $this->buscarEmailsRH();

        // Determina tipo de notificação baseado no status atual
        $tipo = $this->determinarTipoNotificacao();

        // Busca destinatários baseado no tipo
        $destinatarios = $this->buscarDestinatarios($tipo);

        Log::info("Tipo de notificação: {$tipo}");
        Log::info("Destinatários encontrados: " . count($destinatarios));

        if (empty($destinatarios)) {
            Log::warning("Nenhum destinatário encontrado - abortando");
            return;
        }

        // Envia email com todos os destinatários
        $this->enviarEmail($destinatarios, $tipo);

        Log::info("=== JOB NOTIFICAÇÃO RECURSIVA - CONCLUÍDO ===");
    }

    /**
     * Determina o tipo de notificação baseado no status atual da admissão
     */
    private function determinarTipoNotificacao(): string
    {
        // Status do Gestor
        if ($this->admissao->status_aprovacao === 'reprovado') {
            return 'reprovado_gestor';
        }

        if ($this->admissao->status_aprovacao === 'cancelado') {
            return 'cancelado';
        }

        // Status da Aprovação Extra
        if ($this->admissao->status_aprovacao_extra === 'reprovado') {
            return 'reprovado_aprovacao_extra';
        }

        // Determina próxima etapa se aprovado
        if ($this->admissao->status_aprovacao === 'aprovado') {
            // Usa config cacheada (evita query duplicada)
            if ($this->config && !$this->admissao->status_aprovacao_extra) {
                // Próxima etapa: Aprovação Extra
                return 'pendente_aprovacao_extra';
            }

            if ($this->admissao->status_aprovacao_extra === 'aprovado') {
                // Próxima etapa: RH
                return 'pendente_aprovacao_rh';
            }

            // Sem aprovação extra configurada, vai direto para RH
            return 'pendente_aprovacao_rh';
        }

        // Status aprovado final
        if ($this->admissao->status_aprovacao_rh === 'aprovado') {
            return 'aprovado_final';
        }

        if ($this->admissao->status_aprovacao_rh === 'reprovado') {
            return 'reprovado_rh';
        }

        return 'criacao';
    }

    /**
     * Busca emails do RH uma única vez (cache)
     * Usa RHHelper centralizado
     */
    private function buscarEmailsRH(): array
    {
        return RHHelper::buscarEmailsRH($this->admissao->empresa_id);
    }

    /**
     * Busca email de usuário com cache (evita queries repetidas)
     */
    private function buscarEmailUsuario(int $userId): ?string
    {
        if (!isset($this->usuariosCarregados[$userId])) {
            $user = User::select('id', 'login')->find($userId);
            $this->usuariosCarregados[$userId] = $user ? $user->login : null;
        }
        return $this->usuariosCarregados[$userId];
    }

    /**
     * Busca destinatários baseado no tipo de notificação
     */
    private function buscarDestinatarios(string $tipo): array
    {
        $destinatarios = [];

        switch ($tipo) {
            case 'criacao':
                // Notifica solicitante
                if ($this->admissao->UserCadastrou && $this->admissao->UserCadastrou->login) {
                    $destinatarios[] = $this->admissao->UserCadastrou->login;
                }
                break;

            case 'pendente_aprovacao_extra':
                // Notifica equipe de aprovação extra (usa config cacheada)
                if ($this->config) {
                    $emails = User::whereIn('id', $this->config->usuarios_autorizados)
                        ->whereNotNull('login')
                        ->pluck('login')
                        ->toArray();
                    $destinatarios = array_merge($destinatarios, $emails);
                }

                // Notifica também o solicitante
                if ($this->admissao->UserCadastrou && $this->admissao->UserCadastrou->login) {
                    $destinatarios[] = $this->admissao->UserCadastrou->login;
                }
                break;

            case 'pendente_aprovacao_rh':
                // Notifica RH (usa cache)
                $destinatarios = array_merge($destinatarios, $this->emailsRH);

                // Notifica solicitante e gestor (já carregados via eager loading)
                if ($this->admissao->UserCadastrou && $this->admissao->UserCadastrou->login) {
                    $destinatarios[] = $this->admissao->UserCadastrou->login;
                }
                if ($this->admissao->UserAprovacao && $this->admissao->UserAprovacao->login) {
                    $destinatarios[] = $this->admissao->UserAprovacao->login;
                }
                break;

            case 'reprovado_gestor':
            case 'reprovado_aprovacao_extra':
            case 'reprovado_rh':
            case 'cancelado':
                // Notifica todos os envolvidos (usa eager loading e cache)
                if ($this->admissao->UserCadastrou && $this->admissao->UserCadastrou->login) {
                    $destinatarios[] = $this->admissao->UserCadastrou->login;
                }
                if ($this->admissao->UserAprovacao && $this->admissao->UserAprovacao->login) {
                    $destinatarios[] = $this->admissao->UserAprovacao->login;
                }
                if ($this->admissao->aprovacao_extra_id) {
                    $email = $this->buscarEmailUsuario($this->admissao->aprovacao_extra_id);
                    if ($email) {
                        $destinatarios[] = $email;
                    }
                }

                // Notifica RH (usa cache)
                $destinatarios = array_merge($destinatarios, $this->emailsRH);
                break;

            case 'aprovado_final':
                // Notifica todos os envolvidos (usa eager loading e cache)
                if ($this->admissao->UserCadastrou && $this->admissao->UserCadastrou->login) {
                    $destinatarios[] = $this->admissao->UserCadastrou->login;
                }
                if ($this->admissao->UserAprovacao && $this->admissao->UserAprovacao->login) {
                    $destinatarios[] = $this->admissao->UserAprovacao->login;
                }
                if ($this->admissao->aprovacao_extra_id) {
                    $email = $this->buscarEmailUsuario($this->admissao->aprovacao_extra_id);
                    if ($email) {
                        $destinatarios[] = $email;
                    }
                }

                // RH (usa cache)
                $destinatarios = array_merge($destinatarios, $this->emailsRH);
                break;
        }

        // Remove duplicados e emails vazios
        return array_unique(array_filter($destinatarios));
    }

    /**
     * Envia email para os destinatários
     */
    private function enviarEmail(array $destinatarios, string $tipo)
    {
        if (empty($destinatarios)) {
            return;
        }

        $primeiroEmail = $destinatarios[0];
        $mailable = new NotificacaoAprovacaoMail($this->admissao, $tipo);

        // Se houver mais destinatários, adiciona em BCC
        if (count($destinatarios) > 1) {
            $mailable->bcc(array_slice($destinatarios, 1));
            Log::info("Email principal: {$primeiroEmail}");
            Log::info("BCC: " . implode(', ', array_slice($destinatarios, 1)));
        }

        Mail::to($primeiroEmail)->send($mailable);
        Log::info("Email enviado com sucesso");
    }
}
