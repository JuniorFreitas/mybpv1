<?php

namespace App\Jobs\Movimentacao\FeriasPrevista;

use App\Helpers\RHHelper;
use App\Jobs\Movimentacao\Concerns\EnviaWhatsappNotificacaoMovimentacao;
use App\Mail\Movimentacao\FeriasPrevista\NotificacaoAprovacaoMail;
use App\Models\Admissao;
use App\Models\AprovacaoExtraConfig;
use App\Models\CentroCusto;
use App\Models\Curriculo;
use App\Models\FeedbackCurriculo;
use App\Models\Ferias;
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
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, EnviaWhatsappNotificacaoMovimentacao;

    /** Número de tentativas antes de marcar o job como falho. */
    public $tries = 3;

    /** Tempo máximo de execução em segundos (5 min). */
    public $timeout = 300;

    private $feriasId;
    private $empresaId;
    private $ferias;
    private $cacheConfig;
    private $cacheEmailsRH;
    private static $usuariosCarregados = [];

    public function __construct(int $feriasId, int $empresaId)
    {
        $this->feriasId = $feriasId;
        $this->empresaId = $empresaId;
    }

    public function handle()
    {
        try {
            $this->ferias = Ferias::withoutGlobalScopes()
                ->where('id', $this->feriasId)
                ->where('empresa_id', $this->empresaId)
                ->first();

            if (!$this->ferias) {
                Log::warning("Férias não encontrada ou não pertence à empresa", [
                    'ferias_id' => $this->feriasId,
                    'empresa_id' => $this->empresaId,
                ]);
                return;
            }

            $userIds = array_values(array_filter([
                $this->ferias->solicitante_id,
                $this->ferias->gestor_id,
                $this->ferias->gestor_aprovacao_id,
                $this->ferias->aprovacao_extra_id,
                $this->ferias->rh_aprovacao_id,
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

            $this->ferias->setRelation('Solicitante', $usuarios->get($this->ferias->solicitante_id));
            $this->ferias->setRelation('Gestor', $usuarios->get($this->ferias->gestor_id));
            $this->ferias->setRelation('GestorAprovacao', $usuarios->get($this->ferias->gestor_aprovacao_id));
            $this->ferias->setRelation('AprovacaoExtra', $usuarios->get($this->ferias->aprovacao_extra_id));
            $this->ferias->setRelation('RhAprovacao', $usuarios->get($this->ferias->rh_aprovacao_id));

            $admissao = $this->ferias->admissao_id
                ? Admissao::withoutGlobalScopes()
                ->select('id', 'centro_custo_id', 'feedback_id', 'data_admissao')
                ->where('id', $this->ferias->admissao_id)
                ->first()
                : null;

            $centro = $admissao && $admissao->centro_custo_id
                ? CentroCusto::withoutGlobalScopes()
                ->select('id', 'label', 'empresa_id')
                ->where('id', $admissao->centro_custo_id)
                ->where('empresa_id', $this->empresaId)
                ->first()
                : null;

            $feedback = $admissao && $admissao->feedback_id
                ? FeedbackCurriculo::withoutGlobalScopes()
                ->select('id', 'curriculo_id')
                ->where('id', $admissao->feedback_id)
                ->first()
                : null;

            $curriculo = $feedback && $feedback->curriculo_id
                ? Curriculo::withoutGlobalScopes()
                ->select('id', 'nome')
                ->where('id', $feedback->curriculo_id)
                ->first()
                : null;

            $this->ferias->setRelation('Admissao', $admissao);

            $this->cacheConfig = AprovacaoExtraConfig::getConfigAtiva(
                $this->ferias->empresa_id,
                'ferias'
            );
            $this->cacheEmailsRH = $this->buscarEmailsRH();

            $tipo = $this->determinarTipoNotificacao();

            if (!$tipo) {
                Log::info("Nenhuma notificação necessária para ferias_prevista #{$this->ferias->id}");
                return;
            }

            $destinatarios = $this->buscarDestinatarios($tipo);

            if (empty($destinatarios)) {
                Log::warning("Nenhum destinatário encontrado para tipo: {$tipo}");
                return;
            }

            $this->enviarEmail($tipo, $destinatarios, $centro, $curriculo, $admissao);

            Log::info("Notificação enviada - Tipo: {$tipo}, Férias: #{$this->ferias->id}");

            $this->dispararProximaNotificacao($tipo);
        } catch (\Exception $e) {
            Log::error("Erro ao enviar notificação ferias_prevista #{$this->ferias->id}: {$e->getMessage()}");
            throw $e;
        }
    }

    private function determinarTipoNotificacao(): ?string
    {
        if ($this->ferias->status_aprovacao_gestor === 'reprovado') {
            return 'reprovado_gestor';
        }

        if ($this->ferias->status_aprovacao_extra === 'reprovado') {
            return 'reprovado_aprovacao_extra';
        }

        if ($this->ferias->status_aprovacao_rh === 'reprovado') {
            return 'reprovado_rh';
        }

        if (!$this->ferias->status_aprovacao_gestor) {
            return 'criacao';
        }

        if (
            $this->ferias->status_aprovacao_gestor === 'aprovado'
            && !$this->ferias->status_aprovacao_extra
            && !$this->ferias->status_aprovacao_rh
        ) {
            return $this->cacheConfig ? 'pendente_aprovacao_extra' : 'pendente_aprovacao_rh';
        }

        if (
            $this->cacheConfig
            && $this->ferias->status_aprovacao_extra === 'aprovado'
            && !$this->ferias->status_aprovacao_rh
        ) {
            return 'pendente_aprovacao_rh';
        }

        if ($this->ferias->status_aprovacao_rh === 'aprovado') {
            return 'aprovado_final';
        }

        return null;
    }

    private function buscarEmailsRH(): array
    {
        return RHHelper::buscarEmailsRH($this->ferias->empresa_id);
    }

    private function buscarEmailUsuario(int $userId): ?string
    {
        if (isset(self::$usuariosCarregados[$userId])) {
            return self::$usuariosCarregados[$userId];
        }

        $email = User::query()
            ->withoutGlobalScopes()
            ->where('id', $userId)
            ->where('empresa_id', $this->ferias->empresa_id)
            ->value('login');
        self::$usuariosCarregados[$userId] = $email ?: null;

        return self::$usuariosCarregados[$userId];
    }

    private function buscarDestinatarios(string $tipo): array
    {
        $destinatarios = [];

        switch ($tipo) {
            case 'criacao':
                if ($this->ferias->Gestor && $this->ferias->Gestor->login) {
                    $destinatarios[] = $this->ferias->Gestor->login;
                }
                break;

            case 'pendente_aprovacao_extra':
                if ($this->cacheConfig && $this->cacheConfig->usuarios_autorizados) {
                    $emails = User::withoutGlobalScopes()
                        ->select('login')
                        ->whereIn('id', $this->cacheConfig->usuarios_autorizados)
                        ->where('empresa_id', $this->ferias->empresa_id)
                        ->where('ativo', true)
                        ->whereNotNull('login')
                        ->pluck('login')
                        ->toArray();
                    $destinatarios = array_merge($destinatarios, $emails);
                }
                if ($this->ferias->Solicitante && $this->ferias->Solicitante->login) {
                    $destinatarios[] = $this->ferias->Solicitante->login;
                }
                $this->adicionarEmailUsuario($destinatarios, $this->ferias->solicitante_id);
                $this->adicionarEmailUsuario($destinatarios, $this->ferias->gestor_aprovacao_id);
                break;

            case 'pendente_aprovacao_rh':
                $destinatarios = array_merge($destinatarios, $this->cacheEmailsRH);

                if ($this->ferias->Solicitante && $this->ferias->Solicitante->login) {
                    $destinatarios[] = $this->ferias->Solicitante->login;
                }
                if ($this->ferias->GestorAprovacao && $this->ferias->GestorAprovacao->login) {
                    $destinatarios[] = $this->ferias->GestorAprovacao->login;
                }
                if ($this->ferias->aprovacao_extra_id) {
                    $email = $this->buscarEmailUsuario($this->ferias->aprovacao_extra_id);
                    if ($email) {
                        $destinatarios[] = $email;
                    }
                }
                $this->adicionarEmailUsuario($destinatarios, $this->ferias->solicitante_id);
                $this->adicionarEmailUsuario($destinatarios, $this->ferias->gestor_aprovacao_id);
                $this->adicionarEmailUsuario($destinatarios, $this->ferias->aprovacao_extra_id);
                break;

            case 'reprovado_gestor':
                if ($this->ferias->Solicitante && $this->ferias->Solicitante->login) {
                    $destinatarios[] = $this->ferias->Solicitante->login;
                }
                break;

            case 'reprovado_aprovacao_extra':
                if ($this->ferias->Solicitante && $this->ferias->Solicitante->login) {
                    $destinatarios[] = $this->ferias->Solicitante->login;
                }
                if ($this->ferias->GestorAprovacao && $this->ferias->GestorAprovacao->login) {
                    $destinatarios[] = $this->ferias->GestorAprovacao->login;
                }
                break;

            case 'reprovado_rh':
            case 'cancelado':
                if ($this->ferias->Solicitante && $this->ferias->Solicitante->login) {
                    $destinatarios[] = $this->ferias->Solicitante->login;
                }
                if ($this->ferias->GestorAprovacao && $this->ferias->GestorAprovacao->login) {
                    $destinatarios[] = $this->ferias->GestorAprovacao->login;
                }
                if ($this->ferias->aprovacao_extra_id) {
                    $email = $this->buscarEmailUsuario($this->ferias->aprovacao_extra_id);
                    if ($email) {
                        $destinatarios[] = $email;
                    }
                }
                break;

            case 'aprovado_final':
                if ($this->ferias->Solicitante && $this->ferias->Solicitante->login) {
                    $destinatarios[] = $this->ferias->Solicitante->login;
                }
                if ($this->ferias->GestorAprovacao && $this->ferias->GestorAprovacao->login) {
                    $destinatarios[] = $this->ferias->GestorAprovacao->login;
                }
                if ($this->ferias->aprovacao_extra_id) {
                    $email = $this->buscarEmailUsuario($this->ferias->aprovacao_extra_id);
                    if ($email) {
                        $destinatarios[] = $email;
                    }
                }
                $this->adicionarEmailUsuario($destinatarios, $this->ferias->solicitante_id);
                $this->adicionarEmailUsuario($destinatarios, $this->ferias->gestor_aprovacao_id);
                $this->adicionarEmailUsuario($destinatarios, $this->ferias->aprovacao_extra_id);
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

    private function enviarEmail(string $tipo, array $destinatarios, $centro, $curriculo, $admissao)
    {
        $dados = [
            'tipo' => $tipo,
            'ferias' => $this->ferias,
            'colaborador' => $curriculo ? $curriculo->nome : '',
            'centro_custo' => $centro ? $centro->label : '',
            'periodo' => $this->ferias->PeriodoAquisitivo ? $this->ferias->PeriodoAquisitivo->label : '',
            'data_saida' => $this->ferias->data_saida,
            'data_retorno' => $this->ferias->data_retorno,
            'ultima_data' => $this->ferias->ultima_data,
            'qnt_dias' => $this->ferias->qnt_dias,
            'dias_saldo' => $this->ferias->dias_saldo,
            'solicitante' => $this->ferias->Solicitante ? $this->ferias->Solicitante->nome : '',
            'gestor_aprovador' => $this->ferias->GestorAprovacao ? $this->ferias->GestorAprovacao->nome : '',
            'gestor_selecionado' => $this->ferias->Gestor ? $this->ferias->Gestor->nome : '',
            'aprovacao_extra' => $this->ferias->AprovacaoExtra ? $this->ferias->AprovacaoExtra->nome : '',
            'rh' => $this->ferias->RhAprovacao ? $this->ferias->RhAprovacao->nome : '',
            'nome_aprovacao_extra' => $this->cacheConfig ? $this->cacheConfig->nome_aprovacao : 'Aprovação Extra',
            'url' => route('g.movimentacao.index') . '?' . http_build_query([
                'aba_ativa' => 'ferias',
                'token' => sha1($this->feriasId) . 'lpve' . $this->feriasId,
            ]),
            'empresa_id' => $this->ferias->empresa_id,
            'has_aprovacao_extra' => (bool) $this->cacheConfig,
            'data_admissao' => $admissao ? $admissao->data_admissao : null,
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
        $this->enviarWhatsappAposEmail($dados, $destinatarios, 'Férias');
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
