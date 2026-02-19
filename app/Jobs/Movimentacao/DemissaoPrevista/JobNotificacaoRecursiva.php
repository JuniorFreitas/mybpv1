<?php

namespace App\Jobs\Movimentacao\DemissaoPrevista;

use App\Helpers\RHHelper;
use App\Mail\Movimentacao\DemissaoPrevista\NotificacaoAprovacaoMail;
use App\Models\AprovacaoExtraConfig;
use App\Models\CentroCusto;
use App\Models\DemissaoPrevista;
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

    public $tries = 3;
    public $timeout = 300;

    private $demissaoId;
    private $empresaId;
    private $demissao;
    private $cacheConfig;
    private $cacheEmailsRH;
    private static $usuariosCarregados = [];

    public function __construct(int $demissaoId, int $empresaId)
    {
        $this->demissaoId = $demissaoId;
        $this->empresaId = $empresaId;
    }

    public function handle()
    {
        try {
            $this->demissao = DemissaoPrevista::withoutGlobalScopes()
                ->where('id', $this->demissaoId)
                ->where('empresa_id', $this->empresaId)
                ->first();

            if (!$this->demissao) {
                Log::warning("Demissão não encontrada ou não pertence à empresa", [
                    'demissao_id' => $this->demissaoId,
                    'empresa_id' => $this->empresaId,
                ]);
                return;
            }

            $userIds = array_values(array_filter([
                $this->demissao->user_id,
                $this->demissao->gestor_id,
                $this->demissao->user_aprovacao_id,
                $this->demissao->aprovacao_extra_id,
                $this->demissao->rh_aprovacao_id,
                $this->demissao->colaborador_id,
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

            $this->demissao->setRelation('UserCadastrou', $usuarios->get($this->demissao->user_id));
            $this->demissao->setRelation('GestorAprovacao', $usuarios->get($this->demissao->gestor_id));
            $this->demissao->setRelation('UserAprovacao', $usuarios->get($this->demissao->user_aprovacao_id));
            $this->demissao->setRelation('AprovacaoExtra', $usuarios->get($this->demissao->aprovacao_extra_id));
            $this->demissao->setRelation('RhAprovacao', $usuarios->get($this->demissao->rh_aprovacao_id));
            $this->demissao->setRelation('Colaborador', $usuarios->get($this->demissao->colaborador_id));

            $centro = $this->demissao->centro_custo_id
                ? CentroCusto::withoutGlobalScopes()
                ->select('id', 'label', 'empresa_id')
                ->where('id', $this->demissao->centro_custo_id)
                ->where('empresa_id', $this->empresaId)
                ->first()
                : null;

            $this->cacheConfig = AprovacaoExtraConfig::getConfigAtiva(
                $this->demissao->empresa_id,
                'demissao'
            );
            $this->cacheEmailsRH = $this->buscarEmailsRH();

            $tipo = $this->determinarTipoNotificacao();

            if (!$tipo) {
                Log::info("Nenhuma notificação necessária para demissao_prevista #{$this->demissao->id}");
                return;
            }

            $destinatarios = $this->buscarDestinatarios($tipo);

            if (empty($destinatarios)) {
                Log::warning("Nenhum destinatário encontrado para tipo: {$tipo}");
                return;
            }

            $this->enviarEmail($tipo, $destinatarios, $centro);

            Log::info("Notificação enviada - Tipo: {$tipo}, Demissão: #{$this->demissao->id}");

            $this->dispararProximaNotificacao($tipo);
        } catch (\Exception $e) {
            Log::error("Erro ao enviar notificação demissao_prevista #{$this->demissao->id}: {$e->getMessage()}");
            throw $e;
        }
    }

    private function determinarTipoNotificacao(): ?string
    {
        if ($this->demissao->status_aprovacao === 'reprovado') {
            return 'reprovado_gestor';
        }

        if ($this->demissao->status_aprovacao_extra === 'reprovado') {
            return 'reprovado_aprovacao_extra';
        }

        if ($this->demissao->status_aprovacao_rh === 'reprovado') {
            return 'reprovado_rh';
        }

        if ($this->demissao->status === 'cancelado') {
            return 'cancelado';
        }

        if (!$this->demissao->status_aprovacao) {
            return 'criacao';
        }

        if (
            $this->demissao->status_aprovacao === 'aprovado'
            && !$this->demissao->status_aprovacao_extra
            && !$this->demissao->status_aprovacao_rh
        ) {
            return $this->cacheConfig ? 'pendente_aprovacao_extra' : 'pendente_aprovacao_rh';
        }

        if (
            $this->cacheConfig
            && $this->demissao->status_aprovacao_extra === 'aprovado'
            && !$this->demissao->status_aprovacao_rh
        ) {
            return 'pendente_aprovacao_rh';
        }

        if ($this->demissao->status_aprovacao_rh === 'aprovado') {
            return 'aprovado_final';
        }

        return null;
    }

    private function buscarEmailsRH(): array
    {
        return RHHelper::buscarEmailsRH($this->demissao->empresa_id);
    }

    private function buscarEmailUsuario(int $userId): ?string
    {
        if (isset(self::$usuariosCarregados[$userId])) {
            return self::$usuariosCarregados[$userId];
        }

        $email = User::query()
            ->withoutGlobalScopes()
            ->where('id', $userId)
            ->where('empresa_id', $this->demissao->empresa_id)
            ->value('login');
        self::$usuariosCarregados[$userId] = $email ?: null;

        return self::$usuariosCarregados[$userId];
    }

    private function buscarDestinatarios(string $tipo): array
    {
        $destinatarios = [];

        switch ($tipo) {
            case 'criacao':
                if ($this->demissao->GestorAprovacao && $this->demissao->GestorAprovacao->login) {
                    $destinatarios[] = $this->demissao->GestorAprovacao->login;
                }
                break;

            case 'pendente_aprovacao_extra':
                if ($this->cacheConfig && $this->cacheConfig->usuarios_autorizados) {
                    $emails = User::withoutGlobalScopes()
                        ->select('login')
                        ->whereIn('id', $this->cacheConfig->usuarios_autorizados)
                        ->where('empresa_id', $this->demissao->empresa_id)
                        ->where('ativo', true)
                        ->whereNotNull('login')
                        ->pluck('login')
                        ->toArray();
                    $destinatarios = array_merge($destinatarios, $emails);
                }
                if ($this->demissao->UserCadastrou && $this->demissao->UserCadastrou->login) {
                    $destinatarios[] = $this->demissao->UserCadastrou->login;
                }
                $this->adicionarEmailUsuario($destinatarios, $this->demissao->user_id);
                $this->adicionarEmailUsuario($destinatarios, $this->demissao->user_aprovacao_id);
                break;

            case 'pendente_aprovacao_rh':
                $destinatarios = array_merge($destinatarios, $this->cacheEmailsRH);

                if ($this->demissao->UserCadastrou && $this->demissao->UserCadastrou->login) {
                    $destinatarios[] = $this->demissao->UserCadastrou->login;
                }
                if ($this->demissao->UserAprovacao && $this->demissao->UserAprovacao->login) {
                    $destinatarios[] = $this->demissao->UserAprovacao->login;
                }
                if ($this->demissao->aprovacao_extra_id) {
                    $email = $this->buscarEmailUsuario($this->demissao->aprovacao_extra_id);
                    if ($email) {
                        $destinatarios[] = $email;
                    }
                }
                $this->adicionarEmailUsuario($destinatarios, $this->demissao->user_id);
                $this->adicionarEmailUsuario($destinatarios, $this->demissao->user_aprovacao_id);
                $this->adicionarEmailUsuario($destinatarios, $this->demissao->aprovacao_extra_id);
                break;

            case 'reprovado_gestor':
                if ($this->demissao->UserCadastrou && $this->demissao->UserCadastrou->login) {
                    $destinatarios[] = $this->demissao->UserCadastrou->login;
                }
                break;

            case 'reprovado_aprovacao_extra':
                if ($this->demissao->UserCadastrou && $this->demissao->UserCadastrou->login) {
                    $destinatarios[] = $this->demissao->UserCadastrou->login;
                }
                if ($this->demissao->UserAprovacao && $this->demissao->UserAprovacao->login) {
                    $destinatarios[] = $this->demissao->UserAprovacao->login;
                }
                break;

            case 'reprovado_rh':
            case 'cancelado':
                if ($this->demissao->UserCadastrou && $this->demissao->UserCadastrou->login) {
                    $destinatarios[] = $this->demissao->UserCadastrou->login;
                }
                if ($this->demissao->UserAprovacao && $this->demissao->UserAprovacao->login) {
                    $destinatarios[] = $this->demissao->UserAprovacao->login;
                }
                if ($this->demissao->aprovacao_extra_id) {
                    $email = $this->buscarEmailUsuario($this->demissao->aprovacao_extra_id);
                    if ($email) {
                        $destinatarios[] = $email;
                    }
                }
                break;

            case 'aprovado_final':
                if ($this->demissao->UserCadastrou && $this->demissao->UserCadastrou->login) {
                    $destinatarios[] = $this->demissao->UserCadastrou->login;
                }
                if ($this->demissao->UserAprovacao && $this->demissao->UserAprovacao->login) {
                    $destinatarios[] = $this->demissao->UserAprovacao->login;
                }
                if ($this->demissao->aprovacao_extra_id) {
                    $email = $this->buscarEmailUsuario($this->demissao->aprovacao_extra_id);
                    if ($email) {
                        $destinatarios[] = $email;
                    }
                }
                $this->adicionarEmailUsuario($destinatarios, $this->demissao->user_id);
                $this->adicionarEmailUsuario($destinatarios, $this->demissao->user_aprovacao_id);
                $this->adicionarEmailUsuario($destinatarios, $this->demissao->aprovacao_extra_id);
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

    private function enviarEmail(string $tipo, array $destinatarios, $centro)
    {
        $dados = [
            'tipo' => $tipo,
            'demissao' => $this->demissao,
            'colaborador' => $this->demissao->Colaborador ? $this->demissao->Colaborador->nome : '',
            'centro_custo' => $centro ? $centro->label : '',
            'data_demissao' => $this->demissao->data_demissao,
            'tipo_aviso' => $this->demissao->tipo_aviso,
            'valor' => $this->demissao->valor_format,
            'solicitante' => $this->demissao->UserCadastrou ? $this->demissao->UserCadastrou->nome : '',
            'gestor_aprovador' => $this->demissao->UserAprovacao ? $this->demissao->UserAprovacao->nome : '',
            'gestor_selecionado' => $this->demissao->GestorAprovacao ? $this->demissao->GestorAprovacao->nome : '',
            'aprovacao_extra' => $this->demissao->AprovacaoExtra ? $this->demissao->AprovacaoExtra->nome : '',
            'rh' => $this->demissao->RhAprovacao ? $this->demissao->RhAprovacao->nome : '',
            'nome_aprovacao_extra' => $this->cacheConfig ? $this->cacheConfig->nome_aprovacao : 'Aprovação Extra',
            'url' => route('g.movimentacao.index') . '?' . http_build_query([
                'aba_ativa' => 'demissao',
                'token' => sha1($this->demissaoId) . 'lpve' . $this->demissaoId,
            ]),
            'empresa_id' => $this->demissao->empresa_id,
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
