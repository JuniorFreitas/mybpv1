<?php

namespace App\Jobs\RequisicaoVaga;

use App\Helpers\RHHelper;
use App\Mail\RequisicaoVagas\NotificacaoAprovacaoMail;
use App\Models\AprovacaoExtraConfig;
use App\Models\AreaEtiqueta;
use App\Models\CentroCusto;
use App\Models\RequisicaoVagaMovimentacao;
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
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $requisicaoId;
    private $empresaId;
    private $requisicao;
    private $cacheConfig;
    private $cacheEmailsRH;
    private static $usuariosCarregados = [];

    public function __construct(int $requisicaoId, int $empresaId)
    {
        $this->requisicaoId = $requisicaoId;
        $this->empresaId = $empresaId;
    }

    public function handle()
    {
        try {
            $this->requisicao = RequisicaoVagaMovimentacao::withoutGlobalScopes()
                ->where('id', $this->requisicaoId)
                ->where('empresa_id', $this->empresaId)
                ->first();

            if (!$this->requisicao) {
                Log::warning("Requisição de vaga não encontrada ou não pertence à empresa", [
                    'requisicao_id' => $this->requisicaoId,
                    'empresa_id' => $this->empresaId,
                ]);
                return;
            }

            $userIds = array_values(array_filter([
                $this->requisicao->user_id,
                $this->requisicao->gestor_id,
                $this->requisicao->user_aprovacao_id,
                $this->requisicao->aprovacao_extra_id,
                $this->requisicao->rh_aprovacao_id,
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

            $this->requisicao->setRelation('UserCadastrou', $usuarios->get($this->requisicao->user_id));
            $this->requisicao->setRelation('GestorContratacao', $usuarios->get($this->requisicao->gestor_id));
            $this->requisicao->setRelation('UserAprovacao', $usuarios->get($this->requisicao->user_aprovacao_id));
            $this->requisicao->setRelation('AprovacaoExtra', $usuarios->get($this->requisicao->aprovacao_extra_id));
            $this->requisicao->setRelation('AprovacaoRh', $usuarios->get($this->requisicao->rh_aprovacao_id));

            $centro = $this->requisicao->centro_custo_id
                ? CentroCusto::withoutGlobalScopes()
                ->select('id', 'label', 'empresa_id')
                ->where('id', $this->requisicao->centro_custo_id)
                ->where('empresa_id', $this->empresaId)
                ->first()
                : null;

            $cargo = $this->requisicao->cargo_id
                ? Vaga::withoutGlobalScopes()
                ->select('id', 'nome')
                ->where('id', $this->requisicao->cargo_id)
                ->first()
                : null;

            $area = $this->requisicao->area_id
                ? AreaEtiqueta::withoutGlobalScopes()
                ->select('id', 'label')
                ->where('id', $this->requisicao->area_id)
                ->first()
                : null;

            $this->cacheConfig = AprovacaoExtraConfig::getConfigAtiva(
                $this->requisicao->empresa_id,
                'requisicao_vaga'
            );
            $this->cacheEmailsRH = $this->buscarEmailsRH();

            $tipo = $this->determinarTipoNotificacao();

            if (!$tipo) {
                Log::info("Nenhuma notificação necessária para requisicao_vaga #{$this->requisicao->id}");
                return;
            }

            $destinatarios = $this->buscarDestinatarios($tipo);

            if (empty($destinatarios)) {
                Log::warning("Nenhum destinatário encontrado para tipo: {$tipo}");
                return;
            }

            $this->enviarEmail($tipo, $destinatarios, $centro, $cargo, $area);

            Log::info("Notificação enviada - Tipo: {$tipo}, Requisição: #{$this->requisicao->id}");

            $this->dispararProximaNotificacao($tipo);
        } catch (\Exception $e) {
            Log::error("Erro ao enviar notificação requisicao_vaga #{$this->requisicaoId}: {$e->getMessage()}");
            throw $e;
        }
    }

    private function determinarTipoNotificacao(): ?string
    {
        if ($this->requisicao->status_aprovacao === 'reprovado') {
            return 'reprovado_gestor';
        }

        if ($this->requisicao->status_aprovacao_extra === 'reprovado') {
            return 'reprovado_aprovacao_extra';
        }

        if ($this->requisicao->status_aprovacao_rh === 'reprovado') {
            return 'reprovado_rh';
        }

        // RH aprovou → envia e-mail "aprovado_final" para as etapas anteriores (solicitante, gestor, aprovação extra)
        if ($this->requisicao->status_aprovacao_rh === 'aprovado') {
            return 'aprovado_final';
        }

        if (!$this->requisicao->status_aprovacao) {
            return 'criacao';
        }

        if (
            $this->requisicao->status_aprovacao === 'aprovado'
            && !$this->requisicao->status_aprovacao_extra
        ) {
            return $this->cacheConfig ? 'pendente_aprovacao_extra' : 'pendente_aprovacao_rh';
        }

        if (
            $this->cacheConfig
            && $this->requisicao->status_aprovacao_extra === 'aprovado'
            && !$this->requisicao->status_aprovacao_rh
        ) {
            return 'pendente_aprovacao_rh';
        }

        return null;
    }

    private function buscarEmailsRH(): array
    {
        return RHHelper::buscarEmailsRH($this->requisicao->empresa_id);
    }

    private function buscarEmailsGestores(): array
    {
        return User::withoutGlobalScopes()
            ->where('empresa_id', $this->requisicao->empresa_id)
            ->where('ativo', true)
            ->where('tipo', '!=', 'Empresa')
            ->get()
            ->filter(function ($user) {
                return $user->can('privilegio_aprovar_por_gestor');
            })
            ->pluck('login')
            ->filter()
            ->values()
            ->toArray();
    }

    private function buscarEmailUsuario(int $userId): ?string
    {
        if (isset(self::$usuariosCarregados[$userId])) {
            return self::$usuariosCarregados[$userId];
        }

        $email = User::query()
            ->withoutGlobalScopes()
            ->where('id', $userId)
            ->where('empresa_id', $this->requisicao->empresa_id)
            ->value('login');
        self::$usuariosCarregados[$userId] = $email ?: null;

        return self::$usuariosCarregados[$userId];
    }

    /**
     * Busca destinatários baseado no tipo de notificação (fluxo igual Transferência).
     */
    private function buscarDestinatarios(string $tipo): array
    {
        $destinatarios = [];

        switch ($tipo) {
            case 'criacao':
                // Notifica o gestor da requisição para aprovação (igual Transferência)
                if ($this->requisicao->GestorContratacao && $this->requisicao->GestorContratacao->login) {
                    $destinatarios[] = $this->requisicao->GestorContratacao->login;
                }
                if (empty($destinatarios)) {
                    $destinatarios = array_merge($destinatarios, $this->buscarEmailsGestores());
                }
                break;

            case 'pendente_aprovacao_extra':
                if ($this->cacheConfig && $this->cacheConfig->usuarios_autorizados) {
                    $emails = User::withoutGlobalScopes()
                        ->select('login')
                        ->whereIn('id', $this->cacheConfig->usuarios_autorizados)
                        ->where('empresa_id', $this->requisicao->empresa_id)
                        ->where('ativo', true)
                        ->whereNotNull('login')
                        ->pluck('login')
                        ->toArray();
                    $destinatarios = array_merge($destinatarios, $emails);
                }
                if ($this->requisicao->UserCadastrou && $this->requisicao->UserCadastrou->login) {
                    $destinatarios[] = $this->requisicao->UserCadastrou->login;
                }
                $this->adicionarEmailUsuario($destinatarios, $this->requisicao->user_id);
                $this->adicionarEmailUsuario($destinatarios, $this->requisicao->user_aprovacao_id);
                break;

            case 'pendente_aprovacao_rh':
                $destinatarios = array_merge($destinatarios, $this->cacheEmailsRH);
                if ($this->requisicao->UserCadastrou && $this->requisicao->UserCadastrou->login) {
                    $destinatarios[] = $this->requisicao->UserCadastrou->login;
                }
                if ($this->requisicao->UserAprovacao && $this->requisicao->UserAprovacao->login) {
                    $destinatarios[] = $this->requisicao->UserAprovacao->login;
                }
                if ($this->requisicao->aprovacao_extra_id) {
                    $email = $this->buscarEmailUsuario($this->requisicao->aprovacao_extra_id);
                    if ($email) {
                        $destinatarios[] = $email;
                    }
                }
                $this->adicionarEmailUsuario($destinatarios, $this->requisicao->user_id);
                $this->adicionarEmailUsuario($destinatarios, $this->requisicao->user_aprovacao_id);
                $this->adicionarEmailUsuario($destinatarios, $this->requisicao->aprovacao_extra_id);
                break;

            case 'reprovado_gestor':
                if ($this->requisicao->UserCadastrou && $this->requisicao->UserCadastrou->login) {
                    $destinatarios[] = $this->requisicao->UserCadastrou->login;
                }
                break;

            case 'reprovado_aprovacao_extra':
                if ($this->requisicao->UserCadastrou && $this->requisicao->UserCadastrou->login) {
                    $destinatarios[] = $this->requisicao->UserCadastrou->login;
                }
                if ($this->requisicao->UserAprovacao && $this->requisicao->UserAprovacao->login) {
                    $destinatarios[] = $this->requisicao->UserAprovacao->login;
                }
                break;

            case 'reprovado_rh':
                if ($this->requisicao->UserCadastrou && $this->requisicao->UserCadastrou->login) {
                    $destinatarios[] = $this->requisicao->UserCadastrou->login;
                }
                if ($this->requisicao->UserAprovacao && $this->requisicao->UserAprovacao->login) {
                    $destinatarios[] = $this->requisicao->UserAprovacao->login;
                }
                if ($this->requisicao->AprovacaoExtra && $this->requisicao->AprovacaoExtra->login) {
                    $destinatarios[] = $this->requisicao->AprovacaoExtra->login;
                }
                break;

            case 'aprovado_final':
                // Aprovado pelo RH: notifica solicitante, gestor e aprovação extra (etapas anteriores)
                if ($this->requisicao->UserCadastrou && $this->requisicao->UserCadastrou->login) {
                    $destinatarios[] = $this->requisicao->UserCadastrou->login;
                }
                $this->adicionarEmailUsuario($destinatarios, $this->requisicao->user_id);
                if ($this->requisicao->UserAprovacao && $this->requisicao->UserAprovacao->login) {
                    $destinatarios[] = $this->requisicao->UserAprovacao->login;
                }
                $this->adicionarEmailUsuario($destinatarios, $this->requisicao->user_aprovacao_id);
                if ($this->requisicao->AprovacaoExtra && $this->requisicao->AprovacaoExtra->login) {
                    $destinatarios[] = $this->requisicao->AprovacaoExtra->login;
                }
                $this->adicionarEmailUsuario($destinatarios, $this->requisicao->aprovacao_extra_id);
                break;
        }

        return array_unique(array_filter($destinatarios));
    }

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

    private function enviarEmail(string $tipo, array $destinatarios, $centro, $cargo, $area)
    {
        $dados = [
            'tipo' => $tipo,
            'aprovacao_rh' => $this->requisicao->AprovacaoRh ? $this->requisicao->AprovacaoRh->nome : '',
            'requisicao' => $this->requisicao,
            'cargo' => $cargo ? $cargo->nome : '',
            'centro_custo' => $centro ? $centro->label : '',
            'area' => $area ? $area->label : '',
            'quantidade' => $this->requisicao->quantidade,
            'tipo_contratacao' => $this->requisicao->tipo_contratacao,
            'prioridade' => $this->requisicao->prioridade,
            'previsao_inicio' => $this->requisicao->previsao_inicio,
            'imediata' => $this->requisicao->imediata,
            'solicitante' => $this->requisicao->solicitante ?: ($this->requisicao->UserCadastrou ? $this->requisicao->UserCadastrou->nome : ''),
            'gestor_aprovador' => $this->requisicao->UserAprovacao ? $this->requisicao->UserAprovacao->nome : '',
            'aprovacao_extra' => $this->requisicao->AprovacaoExtra ? $this->requisicao->AprovacaoExtra->nome : '',
            'nome_aprovacao_extra' => $this->cacheConfig ? $this->cacheConfig->nome_aprovacao : 'Aprovação Extra',
            'url' => config('app.url') . '/planejamento/requisicao-vaga',
            'empresa_id' => $this->requisicao->empresa_id,
            'has_aprovacao_extra' => (bool) $this->cacheConfig,
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
    }

    private function dispararProximaNotificacao(string $tipoAtual)
    {
        $tiposQueNaoDisparam = [
            'reprovado_gestor',
            'reprovado_aprovacao_extra'
        ];

        if (in_array($tipoAtual, $tiposQueNaoDisparam)) {
            Log::info("⛔ Tipo '{$tipoAtual}' NÃO dispara próxima etapa - fluxo encerrado");
            return;
        }

        if ($tipoAtual === 'criacao') {
            Log::info("Tipo 'criacao' aguarda aprovação do gestor");
            return;
        }

        if ($tipoAtual === 'pendente_aprovacao_extra') {
            Log::info("Tipo 'pendente_aprovacao_extra' aguarda aprovação extra");
            return;
        }

        if ($tipoAtual === 'pendente_aprovacao_rh') {
            Log::info("Tipo 'pendente_aprovacao_rh' aguarda processamento do RH");
            return;
        }

        if ($tipoAtual === 'aprovado_final') {
            Log::info("Tipo 'aprovado_final' - fluxo encerrado (RH aprovou)");
            return;
        }

        Log::info("Nenhuma próxima notificação para tipo '{$tipoAtual}'");
    }
}
