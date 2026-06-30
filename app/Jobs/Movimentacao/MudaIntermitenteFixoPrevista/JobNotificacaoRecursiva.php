<?php

namespace App\Jobs\Movimentacao\MudaIntermitenteFixoPrevista;

use App\Jobs\Movimentacao\Concerns\EnviaWhatsappNotificacaoMovimentacao;
use App\Mail\Movimentacao\MudaIntermitenteFixoPrevista\NotificacaoAprovacaoMail;
use App\Models\AprovacaoExtraConfig;
use App\Models\CentroCusto;
use App\Models\IntermitenteFixoPrevista;
use App\Models\User;
use App\Models\Vaga;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class JobNotificacaoRecursiva implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, EnviaWhatsappNotificacaoMovimentacao;

    public $tries = 3;
    public $timeout = 300;

    private $intermitenteId;
    private $empresaId;
    private $intermitente;
    private $cacheConfig;
    private $cacheEmailsRH;
    private static $usuariosCarregados = [];

    public function __construct(int $intermitenteId, int $empresaId)
    {
        $this->intermitenteId = $intermitenteId;
        $this->empresaId = $empresaId;
    }

    public function handle()
    {
        try {
            $this->intermitente = IntermitenteFixoPrevista::withoutGlobalScopes()
                ->where('id', $this->intermitenteId)
                ->where('empresa_id', $this->empresaId)
                ->first();

            if (!$this->intermitente) {
                Log::warning("Intermitente fixo não encontrado ou não pertence à empresa", [
                    'intermitente_id' => $this->intermitenteId,
                    'empresa_id' => $this->empresaId,
                ]);
                return;
            }

            // Carregar dados necessários com poucas queries (responsabilidade do JOB)
            $userIds = array_values(array_filter([
                $this->intermitente->user_id,
                $this->intermitente->gestor_id,
                $this->intermitente->user_aprovacao_id,
                $this->intermitente->aprovacao_extra_id,
                $this->intermitente->rh_aprovacao_id,
                $this->intermitente->colaborador_id,
            ]));

            $usuarios = $userIds
                ? User::withoutGlobalScopes()
                ->select('id', 'nome', 'login', 'empresa_id')
                ->whereIn('id', $userIds)
                ->where('empresa_id', $this->empresaId)
                ->where('ativo', true)
                ->where('tipo', '!=', 'Empresa')
                ->where('login', '!=', 'sistema@mybp.com.br')
                ->get()
                ->keyBy('id')
                : collect();

            $this->intermitente->setRelation('Solicitante', $usuarios->get($this->intermitente->user_id));
            $this->intermitente->setRelation('GestorAprovacao', $usuarios->get($this->intermitente->gestor_id));
            $this->intermitente->setRelation('UserAprovacao', $usuarios->get($this->intermitente->user_aprovacao_id));
            $this->intermitente->setRelation('UserAprovacaoExtra', $usuarios->get($this->intermitente->aprovacao_extra_id));
            $this->intermitente->setRelation('RhAprovacao', $usuarios->get($this->intermitente->rh_aprovacao_id));
            $this->intermitente->setRelation('Colaborador', $usuarios->get($this->intermitente->colaborador_id));

            $centroIds = array_values(array_filter([
                $this->intermitente->centro_custo_id,
            ]));

            $centros = $centroIds
                ? CentroCusto::withoutGlobalScopes()
                ->select('id', 'label', 'empresa_id')
                ->whereIn('id', $centroIds)
                ->where('empresa_id', $this->empresaId)
                ->get()
                ->keyBy('id')
                : collect();

            $this->intermitente->setRelation('CentroCusto', $centros->get($this->intermitente->centro_custo_id));

            $cargoIds = array_values(array_filter([
                $this->intermitente->cargo_anterior_id,
                $this->intermitente->novo_cargo_id,
            ]));

            $cargos = $cargoIds
                ? Vaga::withoutGlobalScopes()
                ->select('id', 'nome', 'empresa_id')
                ->whereIn('id', $cargoIds)
                ->where('empresa_id', $this->empresaId)
                ->get()
                ->keyBy('id')
                : collect();

            $this->intermitente->setRelation('CargoAnterior', $cargos->get($this->intermitente->cargo_anterior_id));
            $this->intermitente->setRelation('NovoCargo', $cargos->get($this->intermitente->novo_cargo_id));

            // Cache de configuração e RH (busca uma única vez)
            $this->cacheConfig = AprovacaoExtraConfig::getConfigAtiva(
                $this->intermitente->empresa_id,
                'intermitente_fixo'
            );
            $this->cacheEmailsRH = $this->buscarEmailsRH();

            $tipo = $this->determinarTipoNotificacao();

            if (!$tipo) {
                Log::info("Nenhuma notificação necessária para intermitente_fixo #{$this->intermitente->id}");
                return;
            }

            $destinatarios = $this->buscarDestinatarios($tipo);

            if (empty($destinatarios)) {
                Log::warning("Nenhum destinatário encontrado para tipo: {$tipo}");
                return;
            }

            $this->enviarEmail($tipo, $destinatarios);

            Log::info("Notificação enviada - Tipo: {$tipo}, Intermitente: #{$this->intermitente->id}");

            // ✅ ENVIO RECURSIVO: Verifica se há próxima etapa e dispara automaticamente
            $this->dispararProximaNotificacao($tipo);
        } catch (\Exception $e) {
            Log::error("Erro ao enviar notificação intermitente_fixo #{$this->intermitente->id}: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Determina o tipo de notificação baseado no status
     */
    private function determinarTipoNotificacao(): ?string
    {
        // 1. Reprovações (verificar PRIMEIRO para evitar notificar próximas etapas)
        if ($this->intermitente->status_aprovacao === 'reprovado') {
            return 'reprovado_gestor';
        }

        if ($this->intermitente->status_aprovacao_extra === 'reprovado') {
            return 'reprovado_aprovacao_extra';
        }

        if ($this->intermitente->status_aprovacao_rh === 'reprovado') {
            return 'reprovado_rh';
        }

        // RH aprovou → envia e-mail "aprovado_final" para as etapas anteriores
        if ($this->intermitente->status_aprovacao_rh === 'aprovado') {
            return 'aprovado_final';
        }

        // 2. Criação inicial
        if (!$this->intermitente->status_aprovacao) {
            return 'criacao';
        }

        // 3. Aprovado pelo gestor
        if ($this->intermitente->status_aprovacao === 'aprovado' && !$this->intermitente->status_aprovacao_extra && !$this->intermitente->status_aprovacao_rh) {
            return $this->cacheConfig ? 'pendente_aprovacao_extra' : 'pendente_aprovacao_rh';
        }

        // 4. Aprovado pela aprovação extra
        if ($this->cacheConfig && $this->intermitente->status_aprovacao_extra === 'aprovado' && !$this->intermitente->status_aprovacao_rh) {
            return 'pendente_aprovacao_rh';
        }

        return null;
    }

    /**
     * Busca destinatários baseado no tipo de notificação.
     */
    private function buscarDestinatarios(string $tipo): array
    {
        $destinatarios = [];

        switch ($tipo) {
            case 'criacao':
                // Notifica gestor para aprovação (próxima etapa)
                if ($this->intermitente->GestorAprovacao && $this->intermitente->GestorAprovacao->login) {
                    $destinatarios[] = $this->intermitente->GestorAprovacao->login;
                }
                break;

            case 'pendente_aprovacao_extra':
                // ✅ GESTOR APROVOU: Notifica Aprovação Extra (próxima) + Solicitante (anterior)
                // Próxima etapa: Aprovação Extra
                if ($this->cacheConfig && $this->cacheConfig->usuarios_autorizados) {
                    $emails = User::withoutGlobalScopes()
                        ->select('login')
                        ->whereIn('id', $this->cacheConfig->usuarios_autorizados)
                        ->where('empresa_id', $this->intermitente->empresa_id)
                        ->where('ativo', true)
                        ->whereNotNull('login')
                        ->pluck('login')
                        ->toArray();
                    $destinatarios = array_merge($destinatarios, $emails);
                }
                // Etapa anterior: Solicitante
                if ($this->intermitente->Solicitante && $this->intermitente->Solicitante->login) {
                    $destinatarios[] = $this->intermitente->Solicitante->login;
                }
                $this->adicionarEmailUsuario($destinatarios, $this->intermitente->user_id);
                $this->adicionarEmailUsuario($destinatarios, $this->intermitente->user_aprovacao_id);
                break;

            case 'pendente_aprovacao_rh':
                // ✅ APROVAÇÃO EXTRA APROVOU (ou Gestor se não tem Extra): Notifica RH (próxima) + anteriores
                // Próxima etapa: RH
                $destinatarios = array_merge($destinatarios, $this->cacheEmailsRH);

                // Etapas anteriores: Solicitante + Gestor (+ Aprovação Extra se existir)
                if ($this->intermitente->Solicitante && $this->intermitente->Solicitante->login) {
                    $destinatarios[] = $this->intermitente->Solicitante->login;
                }
                if ($this->intermitente->UserAprovacao && $this->intermitente->UserAprovacao->login) {
                    $destinatarios[] = $this->intermitente->UserAprovacao->login;
                }
                if ($this->intermitente->aprovacao_extra_id) {
                    $email = $this->buscarEmailUsuario($this->intermitente->aprovacao_extra_id);
                    if ($email) {
                        $destinatarios[] = $email;
                    }
                }
                $this->adicionarEmailUsuario($destinatarios, $this->intermitente->user_id);
                $this->adicionarEmailUsuario($destinatarios, $this->intermitente->user_aprovacao_id);
                $this->adicionarEmailUsuario($destinatarios, $this->intermitente->aprovacao_extra_id);
                break;

            case 'reprovado_gestor':
                // ⚠️ GESTOR REPROVOU: Notifica apenas SOLICITANTE (não notifica aprovação extra nem RH)
                if ($this->intermitente->Solicitante && $this->intermitente->Solicitante->login) {
                    $destinatarios[] = $this->intermitente->Solicitante->login;
                }
                Log::info("🔙 Gestor reprovou - Notificando apenas SOLICITANTE (etapas anteriores)");
                break;

            case 'reprovado_aprovacao_extra':
                // ⚠️ APROVAÇÃO EXTRA REPROVOU: Notifica SOLICITANTE + GESTOR (não notifica RH - próxima etapa)
                if ($this->intermitente->Solicitante && $this->intermitente->Solicitante->login) {
                    $destinatarios[] = $this->intermitente->Solicitante->login;
                }
                if ($this->intermitente->UserAprovacao && $this->intermitente->UserAprovacao->login) {
                    $destinatarios[] = $this->intermitente->UserAprovacao->login;
                }
                Log::info("🔙 Aprovação Extra reprovou - Notificando SOLICITANTE + GESTOR (etapas anteriores)");
                break;

            case 'reprovado_rh':
            case 'cancelado':
                // ⚠️ RH REPROVOU: Notifica TODAS as etapas anteriores (SOLICITANTE + GESTOR + APROVAÇÃO EXTRA)
                if ($this->intermitente->Solicitante && $this->intermitente->Solicitante->login) {
                    $destinatarios[] = $this->intermitente->Solicitante->login;
                }
                if ($this->intermitente->UserAprovacao && $this->intermitente->UserAprovacao->login) {
                    $destinatarios[] = $this->intermitente->UserAprovacao->login;
                }
                if ($this->intermitente->aprovacao_extra_id) {
                    $email = $this->buscarEmailUsuario($this->intermitente->aprovacao_extra_id);
                    if ($email) {
                        $destinatarios[] = $email;
                    }
                }
                Log::info("🔙 RH reprovou - Notificando TODAS as etapas anteriores (SOLICITANTE + GESTOR + EXTRA)");
                break;

            case 'aprovado_final':
                // Notifica TODOS os envolvidos EXCETO RH (que já aprovou)
                if ($this->intermitente->Solicitante && $this->intermitente->Solicitante->login) {
                    $destinatarios[] = $this->intermitente->Solicitante->login;
                }
                if ($this->intermitente->UserAprovacao && $this->intermitente->UserAprovacao->login) {
                    $destinatarios[] = $this->intermitente->UserAprovacao->login;
                }
                if ($this->intermitente->aprovacao_extra_id) {
                    $email = $this->buscarEmailUsuario($this->intermitente->aprovacao_extra_id);
                    if ($email) {
                        $destinatarios[] = $email;
                    }
                }
                $this->adicionarEmailUsuario($destinatarios, $this->intermitente->user_id);
                $this->adicionarEmailUsuario($destinatarios, $this->intermitente->user_aprovacao_id);
                $this->adicionarEmailUsuario($destinatarios, $this->intermitente->aprovacao_extra_id);
                break;
        }

        // Remove duplicados e emails vazios
        return array_unique(array_filter($destinatarios));
    }

    /**
     * Adiciona email de usuário por ID (fallback para etapas anteriores)
     */
    private function adicionarEmailUsuario(array &$destinatarios, ?int $userId): void
    {
        if (!$userId) {
            return;
        }

        $email = $this->buscarEmailUsuario($userId);
        if ($email) {
            $destinatarios[] = $email;
        }
    }

    /**
     * Envia email usando Mailable reutilizável
     */
    private function enviarEmail(string $tipo, array $destinatarios)
    {
        $dados = [
            'tipo' => $tipo,
            'intermitente' => $this->intermitente,
            'colaborador' => $this->intermitente->Colaborador ? $this->intermitente->Colaborador->nome : '',
            'cargo_anterior' => $this->intermitente->CargoAnterior ? $this->intermitente->CargoAnterior->nome : '',
            'novo_cargo' => $this->intermitente->NovoCargo ? $this->intermitente->NovoCargo->nome : '',
            'centro_custo' => $this->intermitente->CentroCusto ? $this->intermitente->CentroCusto->label : '',
            'solicitante' => $this->intermitente->Solicitante ? $this->intermitente->Solicitante->nome : '',
            'gestor' => $this->intermitente->UserAprovacao ? $this->intermitente->UserAprovacao->nome : '',
            'aprovacao_extra' => $this->intermitente->UserAprovacaoExtra ? $this->intermitente->UserAprovacaoExtra->nome : '',
            'rh' => $this->intermitente->RhAprovacao ? $this->intermitente->RhAprovacao->nome : '',
            'nome_aprovacao_extra' => $this->cacheConfig ? $this->cacheConfig->nome_aprovacao : 'Aprovação Extra',
            'url' => route('g.movimentacao.index') . '?' . http_build_query([
                'aba_ativa' => 'intermitente',
                'token' => sha1($this->intermitenteId) . 'lpve' . $this->intermitenteId,
            ]),
            'empresa_id' => $this->intermitente->empresa_id, // ⚠️ Necessário para o layout de email
            'has_aprovacao_extra' => (bool) $this->cacheConfig, // ✅ Flag para condicionar exibição no template
        ];

        $bcc = array_slice($destinatarios, 1);
        $mailHost = config('mail.mailers.smtp.host') ?? config('mail.host');
        $isMailtrap = $mailHost === 'smtp.mailtrap.io';

        Log::info("Preparando email - Tipo: {$tipo}, Destinatários: " . implode(', ', $destinatarios));
        Log::info("Enviando email para: {$destinatarios[0]} - Tipo: {$tipo}");
        if (!empty($bcc) && $isMailtrap) {
            Log::info("BCC (não enviado no Mailtrap): " . implode(', ', $bcc));
        }

        $email = Mail::to($destinatarios[0]);
        if (!empty($bcc) && !$isMailtrap) {
            $email->bcc($bcc);
        }
        $email->send(new NotificacaoAprovacaoMail($dados));
        $this->enviarWhatsappAposEmail($dados, $destinatarios, 'Intermitente fixo');
    }

    /**
     * Dispara próxima notificação automaticamente (RECURSIVO)
     * Notifica próxima etapa do fluxo de aprovação
     *
     * ⚠️ IMPORTANTE: Reprovações NÃO disparam próxima etapa (fluxo para)
     */
    private function dispararProximaNotificacao(string $tipoAtual)
    {
        // ⚠️ Reprovações e aprovação final NÃO disparam próxima etapa
        // Reprovações notificam recursivamente todas as etapas anteriores e PARAM o fluxo
        $tiposQueNaoDisparam = [
            'reprovado_gestor',        // Fluxo para - notifica todos recursivamente
            'reprovado_aprovacao_extra', // Fluxo para - notifica todos recursivamente
            'reprovado_rh',            // Fluxo para - notifica todos recursivamente
            'cancelado',               // Fluxo para - notifica todos recursivamente
            'aprovado_final'           // Fim do fluxo - já notificou todos
        ];

        if (in_array($tipoAtual, $tiposQueNaoDisparam)) {
            Log::info("⛔ Tipo '{$tipoAtual}' NÃO dispara próxima etapa - fluxo encerrado");
            return;
        }

        // ✅ LÓGICA CORRIGIDA: Determina próximo tipo baseado no tipo atual
        $proximoTipo = null;

        // Criação → notifica próxima etapa (gestor)
        // Não dispara próxima porque o gestor precisa aprovar primeiro
        if ($tipoAtual === 'criacao') {
            // Gestor receberá notificação quando aprovar (controller dispara job)
            Log::info("Tipo 'criacao' aguarda aprovação do gestor");
            return;
        }

        // Pendente Aprovação Extra → notifica RH quando aprovação extra aprovar
        // Não dispara próxima porque aprovação extra precisa aprovar primeiro
        if ($tipoAtual === 'pendente_aprovacao_extra') {
            // RH receberá notificação quando aprovação extra aprovar (controller dispara job)
            Log::info("Tipo 'pendente_aprovacao_extra' aguarda aprovação extra");
            return;
        }

        // Pendente Aprovação RH → notifica todos quando RH aprovar
        // Não dispara próxima porque RH precisa aprovar primeiro
        if ($tipoAtual === 'pendente_aprovacao_rh') {
            // Todos receberão notificação quando RH aprovar (controller dispara job)
            Log::info("Tipo 'pendente_aprovacao_rh' aguarda aprovação do RH");
            return;
        }

        // Se chegou aqui, não há próxima etapa
        Log::info("Nenhuma próxima notificação para tipo '{$tipoAtual}'");
    }
}
