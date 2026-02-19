<?php

namespace App\Jobs\Movimentacao\TransferenciaPrevista;

use App\Helpers\RHHelper;
use App\Mail\Movimentacao\TransferenciaPrevista\NotificacaoAprovacaoMail;
use App\Models\AprovacaoExtraConfig;
use App\Models\CentroCusto;
use App\Models\Curriculo;
use App\Models\TransferenciaPrevista;
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

            // Eager loading para evitar N+1
            // Carregar dados necessários com poucas queries
            $userIds = array_values(array_filter([
                $this->transferencia->user_id,
                $this->transferencia->gestor_id,
                $this->transferencia->user_aprovacao_id,
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
            $this->transferencia->setRelation('UserAprovacao', $usuarios->get($this->transferencia->user_aprovacao_id));
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

            // Cache de configuração e RH (busca uma única vez)
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

    /**
     * Determina o tipo de notificação baseado no status
     */
    private function determinarTipoNotificacao(): ?string
    {
        // 1. Reprovações (verificar PRIMEIRO para evitar notificar próximas etapas)
        if ($this->transferencia->status_aprovacao === 'reprovado') {
            return 'reprovado_gestor';
        }

        if ($this->transferencia->status_aprovacao_extra === 'reprovado') {
            return 'reprovado_aprovacao_extra';
        }

        if ($this->transferencia->resposta_rh === 'reprovado') {
            return 'reprovado_rh';
        }

        // 2. Criação inicial
        if (!$this->transferencia->status_aprovacao) {
            return 'criacao';
        }

        // 3. Aprovado pelo gestor
        if ($this->transferencia->status_aprovacao === 'aprovado' && !$this->transferencia->status_aprovacao_extra && !$this->transferencia->resposta_rh) {
            return $this->cacheConfig ? 'pendente_aprovacao_extra' : 'pendente_aprovacao_rh';
        }

        // 4. Aprovado pela aprovação extra
        if ($this->cacheConfig && $this->transferencia->status_aprovacao_extra === 'aprovado' && !$this->transferencia->resposta_rh) {
            return 'pendente_aprovacao_rh';
        }

        // 5. Aprovação final pelo RH
        if ($this->transferencia->resposta_rh === 'aprovado') {
            return 'aprovado_final';
        }

        return null;
    }

    /**
     * Busca emails do RH uma única vez (cache)
     */
    private function buscarEmailsRH(): array
    {
        return RHHelper::buscarEmailsRH($this->transferencia->empresa_id);
    }

    /**
     * Busca email de usuário com cache (evita queries repetidas)
     */
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

    /**
     * Busca destinatários baseado no tipo de notificação
     */
    private function buscarDestinatarios(string $tipo): array
    {
        $destinatarios = [];

        switch ($tipo) {
            case 'criacao':
                // Notifica gestor para aprovação (próxima etapa)
                if ($this->transferencia->GestorAprovacao && $this->transferencia->GestorAprovacao->login) {
                    $destinatarios[] = $this->transferencia->GestorAprovacao->login;
                }
                break;

            case 'pendente_aprovacao_extra':
                // ✅ GESTOR APROVOU: Notifica Aprovação Extra (próxima) + Solicitante (anterior)
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
                break;

            case 'pendente_aprovacao_rh':
                // ✅ APROVAÇÃO EXTRA APROVOU (ou Gestor se não tem Extra): Notifica RH (próxima) + anteriores
                $destinatarios = array_merge($destinatarios, $this->cacheEmailsRH);

                if ($this->transferencia->Solicitante && $this->transferencia->Solicitante->login) {
                    $destinatarios[] = $this->transferencia->Solicitante->login;
                }
                if ($this->transferencia->UserAprovacao && $this->transferencia->UserAprovacao->login) {
                    $destinatarios[] = $this->transferencia->UserAprovacao->login;
                }
                if ($this->transferencia->aprovacao_extra_id) {
                    $email = $this->buscarEmailUsuario($this->transferencia->aprovacao_extra_id);
                    if ($email) {
                        $destinatarios[] = $email;
                    }
                }
                $this->adicionarEmailUsuario($destinatarios, $this->transferencia->user_id);
                $this->adicionarEmailUsuario($destinatarios, $this->transferencia->user_aprovacao_id);
                $this->adicionarEmailUsuario($destinatarios, $this->transferencia->aprovacao_extra_id);
                break;

            case 'reprovado_gestor':
                // ⚠️ GESTOR REPROVOU: Notifica apenas SOLICITANTE
                if ($this->transferencia->Solicitante && $this->transferencia->Solicitante->login) {
                    $destinatarios[] = $this->transferencia->Solicitante->login;
                }
                Log::info("🔙 Gestor reprovou - Notificando apenas SOLICITANTE");
                break;

            case 'reprovado_aprovacao_extra':
                // ⚠️ APROVAÇÃO EXTRA REPROVOU: Notifica SOLICITANTE + GESTOR
                if ($this->transferencia->Solicitante && $this->transferencia->Solicitante->login) {
                    $destinatarios[] = $this->transferencia->Solicitante->login;
                }
                if ($this->transferencia->UserAprovacao && $this->transferencia->UserAprovacao->login) {
                    $destinatarios[] = $this->transferencia->UserAprovacao->login;
                }
                Log::info("🔙 Aprovação Extra reprovou - Notificando SOLICITANTE + GESTOR");
                break;

            case 'reprovado_rh':
            case 'cancelado':
                // ⚠️ RH REPROVOU: Notifica TODAS as etapas anteriores
                if ($this->transferencia->Solicitante && $this->transferencia->Solicitante->login) {
                    $destinatarios[] = $this->transferencia->Solicitante->login;
                }
                if ($this->transferencia->UserAprovacao && $this->transferencia->UserAprovacao->login) {
                    $destinatarios[] = $this->transferencia->UserAprovacao->login;
                }
                if ($this->transferencia->aprovacao_extra_id) {
                    $email = $this->buscarEmailUsuario($this->transferencia->aprovacao_extra_id);
                    if ($email) {
                        $destinatarios[] = $email;
                    }
                }
                Log::info("🔙 RH reprovou - Notificando TODAS as etapas anteriores");
                break;

            case 'aprovado_final':
                // Notifica TODOS os envolvidos EXCETO RH (que já aprovou)
                if ($this->transferencia->Solicitante && $this->transferencia->Solicitante->login) {
                    $destinatarios[] = $this->transferencia->Solicitante->login;
                }
                if ($this->transferencia->UserAprovacao && $this->transferencia->UserAprovacao->login) {
                    $destinatarios[] = $this->transferencia->UserAprovacao->login;
                }
                if ($this->transferencia->aprovacao_extra_id) {
                    $email = $this->buscarEmailUsuario($this->transferencia->aprovacao_extra_id);
                    if ($email) {
                        $destinatarios[] = $email;
                    }
                }
                $this->adicionarEmailUsuario($destinatarios, $this->transferencia->user_id);
                $this->adicionarEmailUsuario($destinatarios, $this->transferencia->user_aprovacao_id);
                $this->adicionarEmailUsuario($destinatarios, $this->transferencia->aprovacao_extra_id);
                break;
        }

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
     * Envia email usando Mailable
     */
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
            'gestor_selecionado' => $this->transferencia->GestorAprovacao ? $this->transferencia->GestorAprovacao->nome : '',
            'aprovacao_extra' => $this->transferencia->UserAprovacaoExtra ? $this->transferencia->UserAprovacaoExtra->nome : '',
            'rh' => $this->transferencia->RhAprovacao ? $this->transferencia->RhAprovacao->nome : '',
            'nome_aprovacao_extra' => $this->cacheConfig ? $this->cacheConfig->nome_aprovacao : 'Aprovação Extra',
            'url' => route('g.movimentacao.index') . '?' . http_build_query([
                'aba_ativa' => 'transferencia',
                'token' => sha1($this->transferenciaId) . 'lpve' . $this->transferenciaId,
            ]),
            'empresa_id' => $this->transferencia->empresa_id,
            'has_aprovacao_extra' => (bool) $this->cacheConfig,
        ];

        Log::debug("Dados do email", [
            'tipo' => $tipo,
            'transferencia_id' => $this->transferencia->id,
            'colaborador' => $dados['colaborador'],
            'centro_custo_origem' => $dados['centro_custo_origem'],
            'centro_custo_destino' => $dados['centro_custo_destino'],
            'solicitante' => $dados['solicitante'],
            'gestor_aprovador' => $dados['gestor_aprovador'] ?? '',
            'gestor_selecionado' => $dados['gestor_selecionado'] ?? '',
            'aprovacao_extra' => $dados['aprovacao_extra'],
            'rh' => $dados['rh'],
            'empresa_id' => $this->transferencia->empresa_id,
        ]);
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

    /**
     * Dispara próxima notificação automaticamente
     */
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
