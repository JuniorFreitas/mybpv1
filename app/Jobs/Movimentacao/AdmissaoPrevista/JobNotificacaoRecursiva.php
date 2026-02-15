<?php

namespace App\Jobs\Movimentacao\AdmissaoPrevista;

use App\Helpers\RHHelper;
use App\Mail\Movimentacao\AdmissaoPrevista\NotificacaoAprovacaoMail;
use App\Models\AdmissoesPrevista;
use App\Models\AprovacaoExtraConfig;
use App\Models\CentroCusto;
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

    private $admissaoId;
    private $empresaId;
    private $admissao;
    private $cacheConfig;
    private $cacheEmailsRH;
    private static $usuariosCarregados = [];

    public function __construct(int $admissaoId, int $empresaId)
    {
        $this->admissaoId = $admissaoId;
        $this->empresaId = $empresaId;
    }

    public function handle()
    {
        try {
            $this->admissao = AdmissoesPrevista::withoutGlobalScopes()
                ->where('id', $this->admissaoId)
                ->where('empresa_id', $this->empresaId)
                ->first();

            if (!$this->admissao) {
                Log::warning("Admissão não encontrada ou não pertence à empresa", [
                    'admissao_id' => $this->admissaoId,
                    'empresa_id' => $this->empresaId,
                ]);
                return;
            }

            $userIds = array_values(array_filter([
                $this->admissao->user_id,
                $this->admissao->gestor_id,
                $this->admissao->user_aprovacao_id,
                $this->admissao->aprovacao_extra_id,
                $this->admissao->rh_aprovacao_id,
                $this->admissao->colaborador_id,
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

            $this->admissao->setRelation('UserCadastrou', $usuarios->get($this->admissao->user_id));
            $this->admissao->setRelation('GestorAprovacao', $usuarios->get($this->admissao->gestor_id));
            $this->admissao->setRelation('UserAprovacao', $usuarios->get($this->admissao->user_aprovacao_id));
            $this->admissao->setRelation('UserAprovacaoExtra', $usuarios->get($this->admissao->aprovacao_extra_id));
            $this->admissao->setRelation('RhAprovacao', $usuarios->get($this->admissao->rh_aprovacao_id));
            $this->admissao->setRelation('Colaborador', $usuarios->get($this->admissao->colaborador_id));

            $centroIds = array_values(array_filter([
                $this->admissao->centro_custo_id,
            ]));

            $centros = $centroIds
                ? CentroCusto::withoutGlobalScopes()
                ->select('id', 'label', 'empresa_id')
                ->whereIn('id', $centroIds)
                ->where('empresa_id', $this->empresaId)
                ->get()
                ->keyBy('id')
                : collect();

            $this->admissao->setRelation('CentroCusto', $centros->get($this->admissao->centro_custo_id));

            $cargo = $this->admissao->cargo_id
                ? Vaga::withoutGlobalScopes()
                ->select('id', 'nome', 'empresa_id')
                ->where('id', $this->admissao->cargo_id)
                ->where('empresa_id', $this->empresaId)
                ->first()
                : null;
            $this->admissao->setRelation('Cargo', $cargo);

            $this->cacheConfig = AprovacaoExtraConfig::getConfigAtiva(
                $this->admissao->empresa_id,
                'admissao'
            );
            $this->cacheEmailsRH = $this->buscarEmailsRH();

            $tipo = $this->determinarTipoNotificacao();

            if (!$tipo) {
                Log::info("Nenhuma notificação necessária para admissao_prevista #{$this->admissao->id}");
                return;
            }

            $destinatarios = $this->buscarDestinatarios($tipo);

            if (empty($destinatarios)) {
                Log::warning("Nenhum destinatário encontrado para tipo: {$tipo}");
                return;
            }

            $this->enviarEmail($tipo, $destinatarios);

            Log::info("Notificação enviada - Tipo: {$tipo}, Admissão: #{$this->admissao->id}");

            $this->dispararProximaNotificacao($tipo);
        } catch (\Exception $e) {
            Log::error("Erro ao enviar notificação admissao_prevista #{$this->admissao->id}: {$e->getMessage()}");
            throw $e;
        }
    }

    private function determinarTipoNotificacao(): ?string
    {
        if ($this->admissao->status_aprovacao === 'reprovado') {
            return 'reprovado_gestor';
        }

        if ($this->admissao->status_aprovacao_extra === 'reprovado') {
            return 'reprovado_aprovacao_extra';
        }

        if ($this->admissao->status_aprovacao_rh === 'reprovado') {
            return 'reprovado_rh';
        }

        if (!$this->admissao->status_aprovacao) {
            return 'criacao';
        }

        if (
            $this->admissao->status_aprovacao === 'aprovado'
            && !$this->admissao->status_aprovacao_extra
            && !$this->admissao->status_aprovacao_rh
        ) {
            return $this->cacheConfig ? 'pendente_aprovacao_extra' : 'pendente_aprovacao_rh';
        }

        if (
            $this->cacheConfig
            && $this->admissao->status_aprovacao_extra === 'aprovado'
            && !$this->admissao->status_aprovacao_rh
        ) {
            return 'pendente_aprovacao_rh';
        }

        if ($this->admissao->status_aprovacao_rh === 'aprovado') {
            return 'aprovado_final';
        }

        return null;
    }

    private function buscarEmailsRH(): array
    {
        return RHHelper::buscarEmailsRH($this->admissao->empresa_id);
    }

    private function buscarEmailUsuario(int $userId): ?string
    {
        if (isset(self::$usuariosCarregados[$userId])) {
            return self::$usuariosCarregados[$userId];
        }

        $email = User::query()
            ->withoutGlobalScopes()
            ->where('id', $userId)
            ->where('empresa_id', $this->admissao->empresa_id)
            ->value('login');
        self::$usuariosCarregados[$userId] = $email ?: null;

        return self::$usuariosCarregados[$userId];
    }

    private function buscarDestinatarios(string $tipo): array
    {
        $destinatarios = [];

        switch ($tipo) {
            case 'criacao':
                if ($this->admissao->GestorAprovacao && $this->admissao->GestorAprovacao->login) {
                    $destinatarios[] = $this->admissao->GestorAprovacao->login;
                }
                break;

            case 'pendente_aprovacao_extra':
                if ($this->cacheConfig && $this->cacheConfig->usuarios_autorizados) {
                    $emails = User::withoutGlobalScopes()
                        ->select('login')
                        ->whereIn('id', $this->cacheConfig->usuarios_autorizados)
                        ->where('empresa_id', $this->admissao->empresa_id)
                        ->where('ativo', true)
                        ->whereNotNull('login')
                        ->pluck('login')
                        ->toArray();
                    $destinatarios = array_merge($destinatarios, $emails);
                }
                if ($this->admissao->UserCadastrou && $this->admissao->UserCadastrou->login) {
                    $destinatarios[] = $this->admissao->UserCadastrou->login;
                }
                $this->adicionarEmailUsuario($destinatarios, $this->admissao->user_id);
                $this->adicionarEmailUsuario($destinatarios, $this->admissao->user_aprovacao_id);
                break;

            case 'pendente_aprovacao_rh':
                $destinatarios = array_merge($destinatarios, $this->cacheEmailsRH);

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
                $this->adicionarEmailUsuario($destinatarios, $this->admissao->user_id);
                $this->adicionarEmailUsuario($destinatarios, $this->admissao->user_aprovacao_id);
                $this->adicionarEmailUsuario($destinatarios, $this->admissao->aprovacao_extra_id);
                break;

            case 'reprovado_gestor':
                if ($this->admissao->UserCadastrou && $this->admissao->UserCadastrou->login) {
                    $destinatarios[] = $this->admissao->UserCadastrou->login;
                }
                break;

            case 'reprovado_aprovacao_extra':
                if ($this->admissao->UserCadastrou && $this->admissao->UserCadastrou->login) {
                    $destinatarios[] = $this->admissao->UserCadastrou->login;
                }
                if ($this->admissao->UserAprovacao && $this->admissao->UserAprovacao->login) {
                    $destinatarios[] = $this->admissao->UserAprovacao->login;
                }
                break;

            case 'reprovado_rh':
            case 'cancelado':
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
                break;

            case 'aprovado_final':
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
                $this->adicionarEmailUsuario($destinatarios, $this->admissao->user_id);
                $this->adicionarEmailUsuario($destinatarios, $this->admissao->user_aprovacao_id);
                $this->adicionarEmailUsuario($destinatarios, $this->admissao->aprovacao_extra_id);
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
            'admissao' => $this->admissao,
            'nome_pessoa' => $this->admissao->nome_pessoa ?? ($this->admissao->Colaborador ? $this->admissao->Colaborador->nome : ''),
            'cargo' => $this->admissao->Cargo ? $this->admissao->Cargo->nome : '',
            'centro_custo' => $this->admissao->CentroCusto ? $this->admissao->CentroCusto->label : '',
            'tipo_contrato' => $this->admissao->tipo_contrato,
            'data_admissao' => $this->admissao->data_admissao,
            'salario' => $this->admissao->salario_format ?? $this->admissao->salario,
            'solicitante' => $this->admissao->UserCadastrou ? $this->admissao->UserCadastrou->nome : '',
            'gestor_aprovador' => $this->admissao->UserAprovacao ? $this->admissao->UserAprovacao->nome : '',
            'gestor_selecionado' => $this->admissao->GestorAprovacao ? $this->admissao->GestorAprovacao->nome : '',
            'aprovacao_extra' => $this->admissao->UserAprovacaoExtra ? $this->admissao->UserAprovacaoExtra->nome : '',
            'rh' => $this->admissao->RhAprovacao ? $this->admissao->RhAprovacao->nome : '',
            'nome_aprovacao_extra' => $this->cacheConfig ? $this->cacheConfig->nome_aprovacao : 'Aprovação Extra',
            'url' => route('g.movimentacao.index') . '?' . http_build_query([
                'aba_ativa' => 'admissao',
                'token' => sha1($this->admissaoId) . 'lpve' . $this->admissaoId,
            ]),
            'empresa_id' => $this->admissao->empresa_id,
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
