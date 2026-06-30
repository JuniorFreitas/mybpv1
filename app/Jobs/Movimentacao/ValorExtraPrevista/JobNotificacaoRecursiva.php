<?php

namespace App\Jobs\Movimentacao\ValorExtraPrevista;

use App\Helpers\RHHelper;
use App\Jobs\Movimentacao\Concerns\EnviaWhatsappNotificacaoMovimentacao;
use App\Mail\Movimentacao\ValorExtraPrevista\NotificacaoAprovacaoMail;
use App\Models\AprovacaoExtraConfig;
use App\Models\CentroCusto;
use App\Models\User;
use App\Models\ValorExtraPrevista;
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

    private $valorExtraId;
    private $empresaId;
    private $valorExtra;
    private $cacheConfig;
    private $cacheEmailsRH;
    private static $usuariosCarregados = [];

    public function __construct(int $valorExtraId, int $empresaId)
    {
        $this->valorExtraId = $valorExtraId;
        $this->empresaId = $empresaId;
    }

    public function handle()
    {
        try {
            $this->valorExtra = ValorExtraPrevista::withoutGlobalScopes()
                ->where('id', $this->valorExtraId)
                ->where('empresa_id', $this->empresaId)
                ->first();

            if (!$this->valorExtra) {
                Log::warning("Valor extra não encontrado ou não pertence à empresa", [
                    'valor_extra_id' => $this->valorExtraId,
                    'empresa_id' => $this->empresaId,
                ]);
                return;
            }

            $userIds = array_values(array_filter([
                $this->valorExtra->user_id,
                $this->valorExtra->gestor_id,
                $this->valorExtra->user_aprovacao_id,
                $this->valorExtra->aprovacao_extra_id,
                $this->valorExtra->rh_aprovacao_id,
                $this->valorExtra->colaborador_id,
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

            $this->valorExtra->setRelation('UserCadastrou', $usuarios->get($this->valorExtra->user_id));
            $this->valorExtra->setRelation('GestorAprovacao', $usuarios->get($this->valorExtra->gestor_id));
            $this->valorExtra->setRelation('UserAprovacao', $usuarios->get($this->valorExtra->user_aprovacao_id));
            $this->valorExtra->setRelation('AprovacaoExtra', $usuarios->get($this->valorExtra->aprovacao_extra_id));
            $this->valorExtra->setRelation('RhAprovacao', $usuarios->get($this->valorExtra->rh_aprovacao_id));
            $this->valorExtra->setRelation('Colaborador', $usuarios->get($this->valorExtra->colaborador_id));

            $centroIds = array_values(array_filter([
                $this->valorExtra->centro_custo_id,
            ]));

            $centros = $centroIds
                ? CentroCusto::withoutGlobalScopes()
                ->select('id', 'label', 'empresa_id')
                ->whereIn('id', $centroIds)
                ->where('empresa_id', $this->empresaId)
                ->get()
                ->keyBy('id')
                : collect();

            $this->valorExtra->setRelation('CentroCusto', $centros->get($this->valorExtra->centro_custo_id));

            $this->cacheConfig = AprovacaoExtraConfig::getConfigAtiva(
                $this->valorExtra->empresa_id,
                'valor_extra'
            );
            $this->cacheEmailsRH = $this->buscarEmailsRH();

            $tipo = $this->determinarTipoNotificacao();

            if (!$tipo) {
                Log::info("Nenhuma notificação necessária para valor_extra_prevista #{$this->valorExtra->id}");
                return;
            }

            $destinatarios = $this->buscarDestinatarios($tipo);

            if (empty($destinatarios)) {
                Log::warning("Nenhum destinatário encontrado para tipo: {$tipo}");
                return;
            }

            $this->enviarEmail($tipo, $destinatarios);

            Log::info("Notificação enviada - Tipo: {$tipo}, Valor Extra: #{$this->valorExtra->id}");

            $this->dispararProximaNotificacao($tipo);
        } catch (\Exception $e) {
            Log::error("Erro ao enviar notificação valor_extra_prevista #{$this->valorExtra->id}: {$e->getMessage()}");
            throw $e;
        }
    }

    private function determinarTipoNotificacao(): ?string
    {
        if ($this->valorExtra->status_aprovacao === 'reprovado') {
            return 'reprovado_gestor';
        }

        if ($this->valorExtra->status_aprovacao_extra === 'reprovado') {
            return 'reprovado_aprovacao_extra';
        }

        if ($this->valorExtra->status_aprovacao_rh === 'reprovado') {
            return 'reprovado_rh';
        }

        if (!$this->valorExtra->status_aprovacao) {
            return 'criacao';
        }

        if (
            $this->valorExtra->status_aprovacao === 'aprovado'
            && !$this->valorExtra->status_aprovacao_extra
            && !$this->valorExtra->status_aprovacao_rh
        ) {
            return $this->cacheConfig ? 'pendente_aprovacao_extra' : 'pendente_aprovacao_rh';
        }

        if (
            $this->cacheConfig
            && $this->valorExtra->status_aprovacao_extra === 'aprovado'
            && !$this->valorExtra->status_aprovacao_rh
        ) {
            return 'pendente_aprovacao_rh';
        }

        if ($this->valorExtra->status_aprovacao_rh === 'aprovado') {
            return 'aprovado_final';
        }

        return null;
    }

    private function buscarEmailsRH(): array
    {
        return RHHelper::buscarEmailsRH($this->valorExtra->empresa_id);
    }

    private function buscarEmailUsuario(int $userId): ?string
    {
        if (isset(self::$usuariosCarregados[$userId])) {
            return self::$usuariosCarregados[$userId];
        }

        $email = User::query()
            ->withoutGlobalScopes()
            ->where('id', $userId)
            ->where('empresa_id', $this->valorExtra->empresa_id)
            ->value('login');
        self::$usuariosCarregados[$userId] = $email ?: null;

        return self::$usuariosCarregados[$userId];
    }

    private function buscarDestinatarios(string $tipo): array
    {
        $destinatarios = [];

        switch ($tipo) {
            case 'criacao':
                if ($this->valorExtra->GestorAprovacao && $this->valorExtra->GestorAprovacao->login) {
                    $destinatarios[] = $this->valorExtra->GestorAprovacao->login;
                }
                break;

            case 'pendente_aprovacao_extra':
                if ($this->cacheConfig && $this->cacheConfig->usuarios_autorizados) {
                    $emails = User::withoutGlobalScopes()
                        ->select('login')
                        ->whereIn('id', $this->cacheConfig->usuarios_autorizados)
                        ->where('empresa_id', $this->valorExtra->empresa_id)
                        ->where('ativo', true)
                        ->whereNotNull('login')
                        ->pluck('login')
                        ->toArray();
                    $destinatarios = array_merge($destinatarios, $emails);
                }
                if ($this->valorExtra->UserCadastrou && $this->valorExtra->UserCadastrou->login) {
                    $destinatarios[] = $this->valorExtra->UserCadastrou->login;
                }
                $this->adicionarEmailUsuario($destinatarios, $this->valorExtra->user_id);
                $this->adicionarEmailUsuario($destinatarios, $this->valorExtra->user_aprovacao_id);
                break;

            case 'pendente_aprovacao_rh':
                $destinatarios = array_merge($destinatarios, $this->cacheEmailsRH);

                if ($this->valorExtra->UserCadastrou && $this->valorExtra->UserCadastrou->login) {
                    $destinatarios[] = $this->valorExtra->UserCadastrou->login;
                }
                if ($this->valorExtra->UserAprovacao && $this->valorExtra->UserAprovacao->login) {
                    $destinatarios[] = $this->valorExtra->UserAprovacao->login;
                }
                if ($this->valorExtra->aprovacao_extra_id) {
                    $email = $this->buscarEmailUsuario($this->valorExtra->aprovacao_extra_id);
                    if ($email) {
                        $destinatarios[] = $email;
                    }
                }
                $this->adicionarEmailUsuario($destinatarios, $this->valorExtra->user_id);
                $this->adicionarEmailUsuario($destinatarios, $this->valorExtra->user_aprovacao_id);
                $this->adicionarEmailUsuario($destinatarios, $this->valorExtra->aprovacao_extra_id);
                break;

            case 'reprovado_gestor':
                if ($this->valorExtra->UserCadastrou && $this->valorExtra->UserCadastrou->login) {
                    $destinatarios[] = $this->valorExtra->UserCadastrou->login;
                }
                break;

            case 'reprovado_aprovacao_extra':
                if ($this->valorExtra->UserCadastrou && $this->valorExtra->UserCadastrou->login) {
                    $destinatarios[] = $this->valorExtra->UserCadastrou->login;
                }
                if ($this->valorExtra->UserAprovacao && $this->valorExtra->UserAprovacao->login) {
                    $destinatarios[] = $this->valorExtra->UserAprovacao->login;
                }
                break;

            case 'reprovado_rh':
            case 'cancelado':
                if ($this->valorExtra->UserCadastrou && $this->valorExtra->UserCadastrou->login) {
                    $destinatarios[] = $this->valorExtra->UserCadastrou->login;
                }
                if ($this->valorExtra->UserAprovacao && $this->valorExtra->UserAprovacao->login) {
                    $destinatarios[] = $this->valorExtra->UserAprovacao->login;
                }
                if ($this->valorExtra->aprovacao_extra_id) {
                    $email = $this->buscarEmailUsuario($this->valorExtra->aprovacao_extra_id);
                    if ($email) {
                        $destinatarios[] = $email;
                    }
                }
                break;

            case 'aprovado_final':
                if ($this->valorExtra->UserCadastrou && $this->valorExtra->UserCadastrou->login) {
                    $destinatarios[] = $this->valorExtra->UserCadastrou->login;
                }
                if ($this->valorExtra->UserAprovacao && $this->valorExtra->UserAprovacao->login) {
                    $destinatarios[] = $this->valorExtra->UserAprovacao->login;
                }
                if ($this->valorExtra->aprovacao_extra_id) {
                    $email = $this->buscarEmailUsuario($this->valorExtra->aprovacao_extra_id);
                    if ($email) {
                        $destinatarios[] = $email;
                    }
                }
                $this->adicionarEmailUsuario($destinatarios, $this->valorExtra->user_id);
                $this->adicionarEmailUsuario($destinatarios, $this->valorExtra->user_aprovacao_id);
                $this->adicionarEmailUsuario($destinatarios, $this->valorExtra->aprovacao_extra_id);
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
            'valor_extra' => $this->valorExtra,
            'colaborador' => $this->valorExtra->Colaborador ? $this->valorExtra->Colaborador->nome : '',
            'centro_custo' => $this->valorExtra->CentroCusto ? $this->valorExtra->CentroCusto->label : '',
            'tipo_valor' => $this->valorExtra->tipo,
            'periodo_dias' => $this->valorExtra->periodo_dias,
            'solicitante' => $this->valorExtra->UserCadastrou ? $this->valorExtra->UserCadastrou->nome : '',
            'gestor_aprovador' => $this->valorExtra->UserAprovacao ? $this->valorExtra->UserAprovacao->nome : '',
            'gestor_selecionado' => $this->valorExtra->GestorAprovacao ? $this->valorExtra->GestorAprovacao->nome : '',
            'aprovacao_extra' => $this->valorExtra->AprovacaoExtra ? $this->valorExtra->AprovacaoExtra->nome : '',
            'rh' => $this->valorExtra->RhAprovacao ? $this->valorExtra->RhAprovacao->nome : '',
            'nome_aprovacao_extra' => $this->cacheConfig ? $this->cacheConfig->nome_aprovacao : 'Aprovação Extra',
            'url' => route('g.movimentacao.index') . '?' . http_build_query([
                'aba_ativa' => 'valorextra',
                'token' => sha1($this->valorExtraId) . 'lpve' . $this->valorExtraId,
            ]),
            'empresa_id' => $this->valorExtra->empresa_id,
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
        $this->enviarWhatsappAposEmail($dados, $destinatarios, 'Valor extra');
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
