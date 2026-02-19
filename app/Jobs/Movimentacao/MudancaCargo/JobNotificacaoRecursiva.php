<?php

namespace App\Jobs\Movimentacao\MudancaCargo;

use App\Helpers\RHHelper;
use App\Mail\Movimentacao\MudancaCargo\NotificacaoAprovacaoMail;
use App\Models\AprovacaoExtraConfig;
use App\Models\CentroCusto;
use App\Models\MudancaCargo;
use App\Models\User;
use App\Models\Vaga;
use App\Models\VagasAbertas;
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

    public $tries = 3;
    public $timeout = 300;

    private $mudancaCargoId;
    private $empresaId;
    private $mudancaCargo;
    private $cacheConfig;
    private $cacheEmailsRH;
    private static $usuariosCarregados = [];

    public function __construct(int $mudancaCargoId, int $empresaId)
    {
        $this->mudancaCargoId = $mudancaCargoId;
        $this->empresaId = $empresaId;
    }

    public function handle()
    {
        try {
            $this->mudancaCargo = MudancaCargo::withoutGlobalScopes()
                ->where('id', $this->mudancaCargoId)
                ->where('empresa_id', $this->empresaId)
                ->first();

            if (!$this->mudancaCargo) {
                Log::warning("Mudança de cargo não encontrada ou não pertence à empresa", [
                    'mudanca_cargo_id' => $this->mudancaCargoId,
                    'empresa_id' => $this->empresaId,
                ]);
                return;
            }

            $userIds = array_values(array_filter([
                $this->mudancaCargo->solicitante_id,
                $this->mudancaCargo->gestor_id,
                $this->mudancaCargo->gestor_aprovacao_id,
                $this->mudancaCargo->aprovacao_extra_id,
                $this->mudancaCargo->rh_aprovacao_id,
                $this->mudancaCargo->colaborador_id,
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

            $this->mudancaCargo->setRelation('Solicitante', $usuarios->get($this->mudancaCargo->solicitante_id));
            $this->mudancaCargo->setRelation('Gestor', $usuarios->get($this->mudancaCargo->gestor_id));
            $this->mudancaCargo->setRelation('GestorAprovacao', $usuarios->get($this->mudancaCargo->gestor_aprovacao_id));
            $this->mudancaCargo->setRelation('AprovacaoExtra', $usuarios->get($this->mudancaCargo->aprovacao_extra_id));
            $this->mudancaCargo->setRelation('RhAprovacao', $usuarios->get($this->mudancaCargo->rh_aprovacao_id));
            $this->mudancaCargo->setRelation('Colaborador', $usuarios->get($this->mudancaCargo->colaborador_id));

            $centroIds = array_values(array_filter([
                $this->mudancaCargo->anterior_centro_custo_id,
                $this->mudancaCargo->novo_centro_custo_id,
            ]));

            $centros = $centroIds
                ? CentroCusto::withoutGlobalScopes()
                ->select('id', 'label', 'empresa_id')
                ->whereIn('id', $centroIds)
                ->where('empresa_id', $this->empresaId)
                ->get()
                ->keyBy('id')
                : collect();

            $this->mudancaCargo->setRelation('CentroCustoAnterior', $centros->get($this->mudancaCargo->anterior_centro_custo_id));
            $this->mudancaCargo->setRelation('CentroCustoNovo', $centros->get($this->mudancaCargo->novo_centro_custo_id));

            $vagaAbertaIds = array_values(array_filter([
                $this->mudancaCargo->anterior_vaga_aberta_id,
                $this->mudancaCargo->nova_vaga_aberta_id,
            ]));

            $vagasAbertas = $vagaAbertaIds
                ? VagasAbertas::withoutGlobalScopes()
                ->select('id', 'vaga_id', 'empresa_id')
                ->whereIn('id', $vagaAbertaIds)
                ->where('empresa_id', $this->empresaId)
                ->get()
                ->keyBy('id')
                : collect();

            $vagaIds = $vagasAbertas->pluck('vaga_id')->filter()->values()->all();

            $vagas = $vagaIds
                ? Vaga::withoutGlobalScopes()
                ->select('id', 'nome', 'empresa_id')
                ->whereIn('id', $vagaIds)
                ->where('empresa_id', $this->empresaId)
                ->get()
                ->keyBy('id')
                : collect();

            $vagaAnterior = $vagasAbertas->get($this->mudancaCargo->anterior_vaga_aberta_id);
            $vagaNova = $vagasAbertas->get($this->mudancaCargo->nova_vaga_aberta_id);

            $cargoAnterior = $vagaAnterior && $vagaAnterior->vaga_id ? $vagas->get($vagaAnterior->vaga_id) : null;
            $cargoNovo = $vagaNova && $vagaNova->vaga_id ? $vagas->get($vagaNova->vaga_id) : null;

            $this->cacheConfig = AprovacaoExtraConfig::getConfigAtiva(
                $this->mudancaCargo->empresa_id,
                'mudanca_cargo'
            );
            $this->cacheEmailsRH = $this->buscarEmailsRH();

            $tipo = $this->determinarTipoNotificacao();

            if (!$tipo) {
                Log::info("Nenhuma notificação necessária para mudanca_cargo #{$this->mudancaCargo->id}");
                return;
            }

            $destinatarios = $this->buscarDestinatarios($tipo);

            if (empty($destinatarios)) {
                Log::warning("Nenhum destinatário encontrado para tipo: {$tipo}");
                return;
            }

            $this->enviarEmail($tipo, $destinatarios, $cargoAnterior, $cargoNovo);

            Log::info("Notificação enviada - Tipo: {$tipo}, Mudança Cargo: #{$this->mudancaCargo->id}");

            $this->dispararProximaNotificacao($tipo);
        } catch (\Exception $e) {
            Log::error("Erro ao enviar notificação mudanca_cargo #{$this->mudancaCargo->id}: {$e->getMessage()}");
            throw $e;
        }
    }

    private function determinarTipoNotificacao(): ?string
    {
        if ($this->mudancaCargo->status_aprovacao_gestor === 'reprovado') {
            return 'reprovado_gestor';
        }

        if ($this->mudancaCargo->status_aprovacao_extra === 'reprovado') {
            return 'reprovado_aprovacao_extra';
        }

        if ($this->mudancaCargo->status_aprovacao_rh === 'reprovado') {
            return 'reprovado_rh';
        }

        if (!$this->mudancaCargo->status_aprovacao_gestor) {
            return 'criacao';
        }

        if (
            $this->mudancaCargo->status_aprovacao_gestor === 'aprovado'
            && !$this->mudancaCargo->status_aprovacao_extra
            && !$this->mudancaCargo->status_aprovacao_rh
        ) {
            return $this->cacheConfig ? 'pendente_aprovacao_extra' : 'pendente_aprovacao_rh';
        }

        if (
            $this->cacheConfig
            && $this->mudancaCargo->status_aprovacao_extra === 'aprovado'
            && !$this->mudancaCargo->status_aprovacao_rh
        ) {
            return 'pendente_aprovacao_rh';
        }

        if ($this->mudancaCargo->status_aprovacao_rh === 'aprovado') {
            return 'aprovado_final';
        }

        return null;
    }

    private function buscarEmailsRH(): array
    {
        return RHHelper::buscarEmailsRH($this->mudancaCargo->empresa_id);
    }

    private function buscarEmailUsuario(int $userId): ?string
    {
        if (isset(self::$usuariosCarregados[$userId])) {
            return self::$usuariosCarregados[$userId];
        }

        $email = User::query()
            ->select('login')
            ->withoutGlobalScopes()
            ->where('id', $userId)
            ->where('empresa_id', $this->mudancaCargo->empresa_id)
            ->value('login');
        self::$usuariosCarregados[$userId] = $email ?: null;

        return self::$usuariosCarregados[$userId];
    }

    private function buscarDestinatarios(string $tipo): array
    {
        $destinatarios = [];

        switch ($tipo) {
            case 'criacao':
                if ($this->mudancaCargo->Gestor && $this->mudancaCargo->Gestor->login) {
                    $destinatarios[] = $this->mudancaCargo->Gestor->login;
                }
                break;

            case 'pendente_aprovacao_extra':
                if ($this->cacheConfig && $this->cacheConfig->usuarios_autorizados) {
                    $emails = User::withoutGlobalScopes()
                        ->select('login')
                        ->whereIn('id', $this->cacheConfig->usuarios_autorizados)
                        ->where('empresa_id', $this->mudancaCargo->empresa_id)
                        ->where('ativo', true)
                        ->whereNotNull('login')
                        ->pluck('login')
                        ->toArray();
                    $destinatarios = array_merge($destinatarios, $emails);
                }
                if ($this->mudancaCargo->Solicitante && $this->mudancaCargo->Solicitante->login) {
                    $destinatarios[] = $this->mudancaCargo->Solicitante->login;
                }
                $this->adicionarEmailUsuario($destinatarios, $this->mudancaCargo->solicitante_id);
                $this->adicionarEmailUsuario($destinatarios, $this->mudancaCargo->gestor_aprovacao_id);
                break;

            case 'pendente_aprovacao_rh':
                $destinatarios = array_merge($destinatarios, $this->cacheEmailsRH);

                if ($this->mudancaCargo->Solicitante && $this->mudancaCargo->Solicitante->login) {
                    $destinatarios[] = $this->mudancaCargo->Solicitante->login;
                }
                if ($this->mudancaCargo->GestorAprovacao && $this->mudancaCargo->GestorAprovacao->login) {
                    $destinatarios[] = $this->mudancaCargo->GestorAprovacao->login;
                }
                if ($this->mudancaCargo->aprovacao_extra_id) {
                    $email = $this->buscarEmailUsuario($this->mudancaCargo->aprovacao_extra_id);
                    if ($email) {
                        $destinatarios[] = $email;
                    }
                }
                $this->adicionarEmailUsuario($destinatarios, $this->mudancaCargo->solicitante_id);
                $this->adicionarEmailUsuario($destinatarios, $this->mudancaCargo->gestor_aprovacao_id);
                $this->adicionarEmailUsuario($destinatarios, $this->mudancaCargo->aprovacao_extra_id);
                break;

            case 'reprovado_gestor':
                if ($this->mudancaCargo->Solicitante && $this->mudancaCargo->Solicitante->login) {
                    $destinatarios[] = $this->mudancaCargo->Solicitante->login;
                }
                break;

            case 'reprovado_aprovacao_extra':
                if ($this->mudancaCargo->Solicitante && $this->mudancaCargo->Solicitante->login) {
                    $destinatarios[] = $this->mudancaCargo->Solicitante->login;
                }
                if ($this->mudancaCargo->GestorAprovacao && $this->mudancaCargo->GestorAprovacao->login) {
                    $destinatarios[] = $this->mudancaCargo->GestorAprovacao->login;
                }
                break;

            case 'reprovado_rh':
            case 'cancelado':
                if ($this->mudancaCargo->Solicitante && $this->mudancaCargo->Solicitante->login) {
                    $destinatarios[] = $this->mudancaCargo->Solicitante->login;
                }
                if ($this->mudancaCargo->GestorAprovacao && $this->mudancaCargo->GestorAprovacao->login) {
                    $destinatarios[] = $this->mudancaCargo->GestorAprovacao->login;
                }
                if ($this->mudancaCargo->aprovacao_extra_id) {
                    $email = $this->buscarEmailUsuario($this->mudancaCargo->aprovacao_extra_id);
                    if ($email) {
                        $destinatarios[] = $email;
                    }
                }
                break;

            case 'aprovado_final':
                if ($this->mudancaCargo->Solicitante && $this->mudancaCargo->Solicitante->login) {
                    $destinatarios[] = $this->mudancaCargo->Solicitante->login;
                }
                if ($this->mudancaCargo->GestorAprovacao && $this->mudancaCargo->GestorAprovacao->login) {
                    $destinatarios[] = $this->mudancaCargo->GestorAprovacao->login;
                }
                if ($this->mudancaCargo->aprovacao_extra_id) {
                    $email = $this->buscarEmailUsuario($this->mudancaCargo->aprovacao_extra_id);
                    if ($email) {
                        $destinatarios[] = $email;
                    }
                }
                $this->adicionarEmailUsuario($destinatarios, $this->mudancaCargo->solicitante_id);
                $this->adicionarEmailUsuario($destinatarios, $this->mudancaCargo->gestor_aprovacao_id);
                $this->adicionarEmailUsuario($destinatarios, $this->mudancaCargo->aprovacao_extra_id);
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

    private function enviarEmail(string $tipo, array $destinatarios, $cargoAnterior, $cargoNovo)
    {
        $dados = [
            'tipo' => $tipo,
            'mudanca_cargo' => $this->mudancaCargo,
            'colaborador' => $this->mudancaCargo->Colaborador ? $this->mudancaCargo->Colaborador->nome : '',
            'centro_custo_anterior' => $this->mudancaCargo->CentroCustoAnterior ? $this->mudancaCargo->CentroCustoAnterior->label : '',
            'centro_custo_novo' => $this->mudancaCargo->CentroCustoNovo ? $this->mudancaCargo->CentroCustoNovo->label : '',
            'cargo_anterior' => $cargoAnterior ? $cargoAnterior->nome : '',
            'cargo_novo' => $cargoNovo ? $cargoNovo->nome : '',
            'funcao_anterior' => $this->mudancaCargo->anterior_funcao ?? '',
            'funcao_nova' => $this->mudancaCargo->nova_funcao ?? '',
            'solicitante' => $this->mudancaCargo->Solicitante ? $this->mudancaCargo->Solicitante->nome : '',
            'gestor_aprovador' => $this->mudancaCargo->GestorAprovacao ? $this->mudancaCargo->GestorAprovacao->nome : '',
            'gestor_selecionado' => $this->mudancaCargo->Gestor ? $this->mudancaCargo->Gestor->nome : '',
            'aprovacao_extra' => $this->mudancaCargo->AprovacaoExtra ? $this->mudancaCargo->AprovacaoExtra->nome : '',
            'rh' => $this->mudancaCargo->RhAprovacao ? $this->mudancaCargo->RhAprovacao->nome : '',
            'nome_aprovacao_extra' => $this->cacheConfig ? $this->cacheConfig->nome_aprovacao : 'Aprovação Extra',
            'url' => route('g.movimentacao.index') . '?' . http_build_query([
                'aba_ativa' => 'mudacargo',
                'token' => sha1($this->mudancaCargoId) . 'lpve' . $this->mudancaCargoId,
            ]),
            'empresa_id' => $this->mudancaCargo->empresa_id,
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
            'reprovado_aprovacao_extra',
            'reprovado_rh',
            'cancelado',
            'aprovado_final'
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
            Log::info("Tipo 'pendente_aprovacao_rh' aguarda aprovação do RH");
            return;
        }

        Log::info("Nenhuma próxima notificação para tipo '{$tipoAtual}'");
    }
}
