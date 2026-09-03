<?php

namespace App\Jobs\Movimentacao\TransferenciaPrevista;

use App\Helpers\RHHelper;
use App\Jobs\Movimentacao\Concerns\EnviaWhatsappNotificacaoMovimentacao;
use App\Mail\Movimentacao\TransferenciaPrevista\NotificacaoAprovacaoMail;
use App\Models\AprovacaoExtraConfig;
use App\Models\CentroCusto;
use App\Models\Curriculo;
use App\Models\TransferenciaPrevista;
use App\Models\User;
use App\Services\TransferenciaPrevista\TransferenciaPrevistaFluxoAprovacaoService;
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

    private $transferenciaId;
    private $empresaId;
    private $transferencia;
    private $cacheConfig;
    private $cacheEmailsRH;
    private static $usuariosCarregados = [];

    public function __construct(int $transferenciaId, int $empresaId)
    {
        $this->transferenciaId = $transferenciaId;
        $this->empresaId = $empresaId;
    }

    public function handle()
    {
        try {
            $this->transferencia = TransferenciaPrevista::withoutGlobalScopes()
                ->where('id', $this->transferenciaId)
                ->where('empresa_id', $this->empresaId)
                ->first();

            if (!$this->transferencia) {
                Log::warning("Transferência não encontrada ou não pertence à empresa", [
                    'transferencia_id' => $this->transferenciaId,
                    'empresa_id' => $this->empresaId,
                ]);
                return;
            }

            $userIds = array_values(array_filter([
                $this->transferencia->user_id,
                $this->transferencia->gestor_id,
                $this->transferencia->gestor_destino_id,
                $this->transferencia->gestor_aprovacao_id,
                $this->transferencia->user_aprovacao_id,
                $this->transferencia->user_aprovacao_gestor_destino_id,
                $this->transferencia->user_aprovacao_gestor_unico_id,
                $this->transferencia->aprovacao_extra_id,
                $this->transferencia->user_rh_id,
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

            $this->transferencia->setRelation('Solicitante', $usuarios->get($this->transferencia->user_id));
            $this->transferencia->setRelation('GestorAprovacao', $usuarios->get($this->transferencia->gestor_id));
            $this->transferencia->setRelation('GestorOrigem', $usuarios->get($this->transferencia->gestor_id));
            $this->transferencia->setRelation('GestorDestino', $usuarios->get($this->transferencia->gestor_destino_id));
            $this->transferencia->setRelation('GestorAprovacaoUnico', $usuarios->get($this->transferencia->gestor_aprovacao_id));
            $this->transferencia->setRelation('UserAprovacao', $usuarios->get($this->transferencia->user_aprovacao_id));
            $this->transferencia->setRelation('QuemAprovouGestorDestino', $usuarios->get($this->transferencia->user_aprovacao_gestor_destino_id));
            $this->transferencia->setRelation('QuemAprovouGestorUnico', $usuarios->get($this->transferencia->user_aprovacao_gestor_unico_id));
            $this->transferencia->setRelation('UserAprovacaoExtra', $usuarios->get($this->transferencia->aprovacao_extra_id));
            $this->transferencia->setRelation('RhAprovacao', $usuarios->get($this->transferencia->user_rh_id));

            $centroIds = array_values(array_filter([
                $this->transferencia->centro_custo_origem_id,
                $this->transferencia->centro_custo_destino_id,
            ]));

            $centros = $centroIds
                ? CentroCusto::withoutGlobalScopes()
                ->select('id', 'label', 'empresa_id')
                ->whereIn('id', $centroIds)
                ->where('empresa_id', $this->empresaId)
                ->get()
                ->keyBy('id')
                : collect();

            $this->transferencia->setRelation('CentroCustoOrigem', $centros->get($this->transferencia->centro_custo_origem_id));
            $this->transferencia->setRelation('CentroCustoDestino', $centros->get($this->transferencia->centro_custo_destino_id));

            $colaborador = $this->transferencia->colaborador_id
                ? Curriculo::withoutGlobalScopes()
                ->select('id', 'nome')
                ->where('id', $this->transferencia->colaborador_id)
                ->first()
                : null;
            $this->transferencia->setRelation('Colaborador', $colaborador);

            $this->cacheConfig = AprovacaoExtraConfig::getConfigAtiva(
                $this->transferencia->empresa_id,
                'transferencia'
            );
            $this->cacheEmailsRH = $this->buscarEmailsRH();

            $tipo = $this->determinarTipoNotificacao();

            if (!$tipo) {
                Log::info("Nenhuma notificação necessária para transferencia_prevista #{$this->transferencia->id}");
                return;
            }

            $destinatarios = $this->buscarDestinatarios($tipo);

            if (empty($destinatarios)) {
                Log::warning("Nenhum destinatário encontrado para tipo: {$tipo}");
                return;
            }

            $this->enviarEmail($tipo, $destinatarios);

            Log::info("Notificação enviada - Tipo: {$tipo}, Transferência: #{$this->transferencia->id}");

            $this->dispararProximaNotificacao($tipo);
        } catch (\Exception $e) {
            Log::error("Erro ao enviar notificação transferencia_prevista #{$this->transferencia->id}: {$e->getMessage()}");
            throw $e;
        }
    }

    private function determinarTipoNotificacao(): ?string
    {
        if ($this->transferencia->modo_aprovacao === 'gestor_unico') {
            return $this->determinarTipoNotificacaoGestorUnico();
        }

        if ($this->transferencia->status_aprovacao === 'reprovado') {
            return 'reprovado_gestor_origem';
        }

        if ($this->transferencia->status_aprovacao_gestor_destino === 'reprovado') {
            return 'reprovado_gestor_destino';
        }

        if ($this->transferencia->status_aprovacao_extra === 'reprovado') {
            return 'reprovado_aprovacao_extra';
        }

        if ($this->transferencia->resposta_rh === 'reprovado') {
            return 'reprovado_rh';
        }

        if (!$this->transferencia->status_aprovacao && !$this->origemDispensada()) {
            return $this->transferencia->fluxo_gestores_automatico ? 'criacao_gestor_origem' : 'criacao';
        }

        if ($this->transferencia->fluxo_gestores_automatico
            && $this->transferencia->exige_aprovacao_gestor_destino
            && !$this->transferencia->status_aprovacao_gestor_destino
            && $this->origemAprovadaOuDispensada()) {
            return 'criacao_gestor_destino';
        }

        if ($this->origemAprovadaOuDispensada()
            && (!$this->transferencia->exige_aprovacao_gestor_destino || $this->transferencia->status_aprovacao_gestor_destino === 'aprovado')
            && !$this->transferencia->status_aprovacao_extra
            && !$this->transferencia->resposta_rh) {
            return $this->cacheConfig ? 'pendente_aprovacao_extra' : 'pendente_aprovacao_rh';
        }

        if ($this->cacheConfig && $this->transferencia->status_aprovacao_extra === 'aprovado' && !$this->transferencia->resposta_rh) {
            return 'pendente_aprovacao_rh';
        }

        if ($this->transferencia->resposta_rh === 'aprovado') {
            return 'aprovado_final';
        }

        return null;
    }

    private function determinarTipoNotificacaoGestorUnico(): ?string
    {
        if ($this->transferencia->status_aprovacao_gestor_unico === 'reprovado') {
            return 'reprovado_gestor_unico';
        }

        if ($this->transferencia->status_aprovacao_extra === 'reprovado') {
            return 'reprovado_aprovacao_extra';
        }

        if ($this->transferencia->resposta_rh === 'reprovado') {
            return 'reprovado_rh';
        }

        if (!$this->transferencia->status_aprovacao_gestor_unico && !$this->gestorUnicoDispensado()) {
            return 'criacao_gestor_unico';
        }

        if ($this->gestorUnicoAprovadoOuDispensado() && !$this->transferencia->status_aprovacao_extra && !$this->transferencia->resposta_rh) {
            return $this->cacheConfig ? 'pendente_aprovacao_extra' : 'pendente_aprovacao_rh';
        }

        if ($this->cacheConfig && $this->transferencia->status_aprovacao_extra === 'aprovado' && !$this->transferencia->resposta_rh) {
            return 'pendente_aprovacao_rh';
        }

        if ($this->transferencia->resposta_rh === 'aprovado') {
            return 'aprovado_final';
        }

        return null;
    }

    private function origemDispensada(): bool
    {
        return (bool) $this->transferencia->fluxo_gestores_automatico
            && !(int) ($this->transferencia->gestor_id ?? 0);
    }

    private function origemAprovadaOuDispensada(): bool
    {
        if ($this->origemDispensada()) {
            return true;
        }

        return $this->transferencia->status_aprovacao === 'aprovado';
    }

    /**
     * Quando o próprio solicitante é o gestor de aprovação designado, a etapa é
     * dispensada — não notifica pedindo autoaprovação, segue o fluxo direto.
     */
    private function gestorUnicoDispensado(): bool
    {
        if (!(int) ($this->transferencia->gestor_aprovacao_id ?? 0)) {
            return false;
        }

        return (int) $this->transferencia->gestor_aprovacao_id === (int) $this->transferencia->user_id;
    }

    private function gestorUnicoAprovadoOuDispensado(): bool
    {
        if ($this->gestorUnicoDispensado()) {
            return true;
        }

        return $this->transferencia->status_aprovacao_gestor_unico === 'aprovado';
    }

    private function buscarEmailsRH(): array
    {
        return RHHelper::buscarEmailsRH($this->transferencia->empresa_id);
    }

    private function buscarEmailUsuario(int $userId): ?string
    {
        if (isset(self::$usuariosCarregados[$userId])) {
            return self::$usuariosCarregados[$userId];
        }

        $email = User::query()
            ->withoutGlobalScopes()
            ->where('id', $userId)
            ->where('empresa_id', $this->transferencia->empresa_id)
            ->value('login');
        self::$usuariosCarregados[$userId] = $email ?: null;

        return self::$usuariosCarregados[$userId];
    }

    private function buscarDestinatarios(string $tipo): array
    {
        $destinatarios = [];

        switch ($tipo) {
            case 'criacao':
            case 'criacao_gestor_origem':
                if ($this->transferencia->GestorOrigem && $this->transferencia->GestorOrigem->login) {
                    $destinatarios[] = $this->transferencia->GestorOrigem->login;
                }
                break;

            case 'criacao_gestor_destino':
                if ($this->transferencia->GestorDestino && $this->transferencia->GestorDestino->login) {
                    $destinatarios[] = $this->transferencia->GestorDestino->login;
                }
                break;

            case 'criacao_gestor_unico':
                if ($this->transferencia->GestorAprovacaoUnico && $this->transferencia->GestorAprovacaoUnico->login) {
                    $destinatarios[] = $this->transferencia->GestorAprovacaoUnico->login;
                }
                break;

            case 'pendente_aprovacao_extra':
                if ($this->cacheConfig && $this->cacheConfig->usuarios_autorizados) {
                    $emails = User::withoutGlobalScopes()
                        ->select('login')
                        ->whereIn('id', $this->cacheConfig->usuarios_autorizados)
                        ->where('empresa_id', $this->transferencia->empresa_id)
                        ->where('ativo', true)
                        ->whereNotNull('login')
                        ->pluck('login')
                        ->toArray();
                    $destinatarios = array_merge($destinatarios, $emails);
                }
                if ($this->transferencia->Solicitante && $this->transferencia->Solicitante->login) {
                    $destinatarios[] = $this->transferencia->Solicitante->login;
                }
                $this->adicionarEmailUsuario($destinatarios, $this->transferencia->user_id);
                $this->adicionarEmailUsuario($destinatarios, $this->transferencia->user_aprovacao_id);
                $this->adicionarEmailUsuario($destinatarios, $this->transferencia->user_aprovacao_gestor_destino_id);
                $this->adicionarEmailUsuario($destinatarios, $this->transferencia->user_aprovacao_gestor_unico_id);
                break;

            case 'pendente_aprovacao_rh':
                $destinatarios = array_merge($destinatarios, $this->cacheEmailsRH);

                if ($this->transferencia->Solicitante && $this->transferencia->Solicitante->login) {
                    $destinatarios[] = $this->transferencia->Solicitante->login;
                }
                if ($this->transferencia->UserAprovacao && $this->transferencia->UserAprovacao->login) {
                    $destinatarios[] = $this->transferencia->UserAprovacao->login;
                }
                $this->adicionarEmailUsuario($destinatarios, $this->transferencia->user_aprovacao_gestor_destino_id);
                $this->adicionarEmailUsuario($destinatarios, $this->transferencia->user_aprovacao_gestor_unico_id);
                if ($this->transferencia->aprovacao_extra_id) {
                    $email = $this->buscarEmailUsuario($this->transferencia->aprovacao_extra_id);
                    if ($email) {
                        $destinatarios[] = $email;
                    }
                }
                break;

            case 'reprovado_gestor_origem':
            case 'reprovado_gestor':
                if ($this->transferencia->Solicitante && $this->transferencia->Solicitante->login) {
                    $destinatarios[] = $this->transferencia->Solicitante->login;
                }
                break;

            case 'reprovado_gestor_destino':
                if ($this->transferencia->Solicitante && $this->transferencia->Solicitante->login) {
                    $destinatarios[] = $this->transferencia->Solicitante->login;
                }
                if ($this->transferencia->UserAprovacao && $this->transferencia->UserAprovacao->login) {
                    $destinatarios[] = $this->transferencia->UserAprovacao->login;
                }
                break;

            case 'reprovado_gestor_unico':
                if ($this->transferencia->Solicitante && $this->transferencia->Solicitante->login) {
                    $destinatarios[] = $this->transferencia->Solicitante->login;
                }
                break;

            case 'reprovado_aprovacao_extra':
                if ($this->transferencia->Solicitante && $this->transferencia->Solicitante->login) {
                    $destinatarios[] = $this->transferencia->Solicitante->login;
                }
                if ($this->transferencia->UserAprovacao && $this->transferencia->UserAprovacao->login) {
                    $destinatarios[] = $this->transferencia->UserAprovacao->login;
                }
                $this->adicionarEmailUsuario($destinatarios, $this->transferencia->user_aprovacao_gestor_destino_id);
                $this->adicionarEmailUsuario($destinatarios, $this->transferencia->user_aprovacao_gestor_unico_id);
                break;

            case 'reprovado_rh':
            case 'cancelado':
                if ($this->transferencia->Solicitante && $this->transferencia->Solicitante->login) {
                    $destinatarios[] = $this->transferencia->Solicitante->login;
                }
                if ($this->transferencia->UserAprovacao && $this->transferencia->UserAprovacao->login) {
                    $destinatarios[] = $this->transferencia->UserAprovacao->login;
                }
                $this->adicionarEmailUsuario($destinatarios, $this->transferencia->user_aprovacao_gestor_destino_id);
                $this->adicionarEmailUsuario($destinatarios, $this->transferencia->user_aprovacao_gestor_unico_id);
                if ($this->transferencia->aprovacao_extra_id) {
                    $email = $this->buscarEmailUsuario($this->transferencia->aprovacao_extra_id);
                    if ($email) {
                        $destinatarios[] = $email;
                    }
                }
                break;

            case 'aprovado_final':
                if ($this->transferencia->Solicitante && $this->transferencia->Solicitante->login) {
                    $destinatarios[] = $this->transferencia->Solicitante->login;
                }
                if ($this->transferencia->UserAprovacao && $this->transferencia->UserAprovacao->login) {
                    $destinatarios[] = $this->transferencia->UserAprovacao->login;
                }
                $this->adicionarEmailUsuario($destinatarios, $this->transferencia->user_aprovacao_gestor_destino_id);
                $this->adicionarEmailUsuario($destinatarios, $this->transferencia->user_aprovacao_gestor_unico_id);
                if ($this->transferencia->aprovacao_extra_id) {
                    $email = $this->buscarEmailUsuario($this->transferencia->aprovacao_extra_id);
                    if ($email) {
                        $destinatarios[] = $email;
                    }
                }
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

    private function enviarEmail(string $tipo, array $destinatarios)
    {
        $dados = [
            'tipo' => $tipo,
            'transferencia' => $this->transferencia,
            'colaborador' => $this->transferencia->Colaborador ? $this->transferencia->Colaborador->nome : '',
            'centro_custo_origem' => $this->transferencia->CentroCustoOrigem ? $this->transferencia->CentroCustoOrigem->label : '',
            'centro_custo_destino' => $this->transferencia->CentroCustoDestino ? $this->transferencia->CentroCustoDestino->label : '',
            'solicitante' => $this->transferencia->Solicitante ? $this->transferencia->Solicitante->nome : '',
            'gestor_aprovador' => $this->transferencia->UserAprovacao ? $this->transferencia->UserAprovacao->nome : '',
            'gestor_selecionado' => $this->transferencia->modo_aprovacao === 'gestor_unico'
                ? ($this->transferencia->GestorAprovacaoUnico ? $this->transferencia->GestorAprovacaoUnico->nome : '')
                : ($this->transferencia->GestorOrigem ? $this->transferencia->GestorOrigem->nome : ''),
            'gestor_destino' => $this->transferencia->GestorDestino ? $this->transferencia->GestorDestino->nome : '',
            'gestor_destino_aprovador' => $this->transferencia->QuemAprovouGestorDestino ? $this->transferencia->QuemAprovouGestorDestino->nome : '',
            'aprovacao_extra' => $this->transferencia->UserAprovacaoExtra ? $this->transferencia->UserAprovacaoExtra->nome : '',
            'rh' => $this->transferencia->RhAprovacao ? $this->transferencia->RhAprovacao->nome : '',
            'nome_aprovacao_extra' => $this->cacheConfig ? $this->cacheConfig->nome_aprovacao : 'Aprovação Extra',
            'url' => route('g.movimentacao.index') . '?' . http_build_query([
                'aba_ativa' => 'transferencia',
                'token' => sha1($this->transferenciaId) . 'lpve' . $this->transferenciaId,
            ]),
            'empresa_id' => $this->transferencia->empresa_id,
            'has_aprovacao_extra' => (bool) $this->cacheConfig,
            'exige_gestor_destino' => (bool) $this->transferencia->exige_aprovacao_gestor_destino,
        ];

        $bcc = array_slice($destinatarios, 1);
        $mailHost = config('mail.mailers.smtp.host') ?? config('mail.host');
        $isMailtrap = $mailHost === 'smtp.mailtrap.io';

        $email = Mail::to($destinatarios[0]);
        if (!empty($bcc) && !$isMailtrap) {
            $email->bcc($bcc);
        }
        $email->send(new NotificacaoAprovacaoMail($dados));
        $this->enviarWhatsappAposEmail($dados, $destinatarios, 'Transferência');
    }

    private function dispararProximaNotificacao(string $tipoAtual)
    {
        $tiposQueNaoDisparam = [
            'reprovado_gestor_origem',
            'reprovado_gestor',
            'reprovado_gestor_destino',
            'reprovado_gestor_unico',
            'reprovado_aprovacao_extra',
            'reprovado_rh',
            'cancelado',
            'aprovado_final',
            'criacao_gestor_origem',
            'criacao',
            'criacao_gestor_destino',
            'criacao_gestor_unico',
            'pendente_aprovacao_extra',
            'pendente_aprovacao_rh',
        ];

        if (in_array($tipoAtual, $tiposQueNaoDisparam, true)) {
            Log::info("Tipo '{$tipoAtual}' aguarda ação ou encerrou fluxo");
        }
    }
}
