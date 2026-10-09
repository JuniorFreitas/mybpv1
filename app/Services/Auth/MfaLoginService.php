<?php

namespace App\Services\Auth;

use App\Jobs\Auth\JobEnvioCodigoMfaLoginEmail;
use App\Jobs\JobSendNotificacaoWhatsApp;
use App\Models\Cliente;
use App\Models\ClienteConfig;
use App\Models\User;
use App\Models\UsuarioTelefone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;

class MfaLoginService
{
    public const SESSION_PENDING_KEY = 'mfa_login_pending';

    protected const CODIGO_TTL_MINUTOS = 10;
    protected const CODIGO_MAX_TENTATIVAS = 5;
    protected const CODIGO_BLOQUEIO_MINUTOS = 15;
    protected const CODIGO_REENVIO_COOLDOWN_SEGUNDOS = 60;

    public function empresaExigeMfa(User $user): bool
    {
        if (!Schema::hasColumn('cliente_configs', 'mfa_login_habilitado')) {
            return false;
        }

        $config = $this->configDaEmpresa($user);

        return $config && (bool) $config->mfa_login_habilitado;
    }

    public function iniciarChallenge(User $user, Request $request, bool $remember = false): array
    {
        $config = $this->configDaEmpresa($user);
        if (!$config || !$config->mfa_login_habilitado) {
            throw new RuntimeException('MFA não está habilitado para esta empresa.');
        }

        $canais = $this->resolverCanaisDisponiveis($user, $config);
        if ($canais === []) {
            throw new RuntimeException(
                'Não foi possível enviar o código MFA. Verifique e-mail/login ou telefone do usuário e os canais habilitados na empresa.'
            );
        }

        $request->session()->put(self::SESSION_PENDING_KEY, [
            'user_id' => (int) $user->id,
            'remember' => $remember,
            'canais' => array_keys($canais),
            'destinos_mascarados' => $this->mascararDestinos($canais),
            'started_at' => now()->toDateTimeString(),
        ]);

        $this->enviarCodigo($user, $canais);

        return [
            'canais' => array_keys($canais),
            'destinos_mascarados' => $this->mascararDestinos($canais),
        ];
    }

    public function reenviarCodigo(User $user): array
    {
        $cooldown = $this->podeReenviar($user);
        if (!$cooldown['pode_enviar']) {
            return $cooldown + ['enviado' => false];
        }

        $config = $this->configDaEmpresa($user);
        if (!$config || !$config->mfa_login_habilitado) {
            throw new RuntimeException('MFA não está habilitado para esta empresa.');
        }

        $canais = $this->resolverCanaisDisponiveis($user, $config);
        if ($canais === []) {
            throw new RuntimeException(
                'Não foi possível reenviar o código MFA. Verifique os canais e os dados de contato do usuário.'
            );
        }

        $this->enviarCodigo($user, $canais);

        return [
            'enviado' => true,
            'pode_enviar' => false,
            'segundos_restantes' => self::CODIGO_REENVIO_COOLDOWN_SEGUNDOS,
            'canais' => array_keys($canais),
            'destinos_mascarados' => $this->mascararDestinos($canais),
        ];
    }

    public function validarCodigo(User $user, string $codigoInformado): bool
    {
        $registro = Cache::get($this->chaveCodigo($user->id));
        if (!is_array($registro) || empty($registro['hash'])) {
            return false;
        }

        $codigoNormalizado = strtoupper(trim($codigoInformado));

        return hash_equals((string) $registro['hash'], hash('sha256', $codigoNormalizado));
    }

    public function invalidarCodigo(User $user): void
    {
        Cache::forget($this->chaveCodigo($user->id));
    }

    /**
     * @return array{pode_enviar: bool, segundos_restantes: int}
     */
    public function podeReenviar(User $user): array
    {
        $dados = Cache::get($this->chaveCooldown($user->id));
        if (!is_array($dados) || empty($dados['reenviar_apos'])) {
            return ['pode_enviar' => true, 'segundos_restantes' => 0];
        }

        $restante = (int) $dados['reenviar_apos'] - now()->timestamp;
        if ($restante <= 0) {
            return ['pode_enviar' => true, 'segundos_restantes' => 0];
        }

        return ['pode_enviar' => false, 'segundos_restantes' => $restante];
    }

    /**
     * @return array{bloqueado: bool, segundos_restantes: int, tentativas_restantes: int}
     */
    public function statusTentativas(User $user): array
    {
        $dados = Cache::get($this->chaveTentativas($user->id));
        if (!is_array($dados)) {
            return [
                'bloqueado' => false,
                'segundos_restantes' => 0,
                'tentativas_restantes' => self::CODIGO_MAX_TENTATIVAS,
            ];
        }

        $bloqueadoAte = (int) ($dados['bloqueado_ate'] ?? 0);
        $count = (int) ($dados['count'] ?? 0);
        $segundosRestantes = max(0, $bloqueadoAte - now()->timestamp);
        $bloqueado = $segundosRestantes > 0 && $count >= self::CODIGO_MAX_TENTATIVAS;
        $tentativasRestantes = max(0, self::CODIGO_MAX_TENTATIVAS - $count);

        if (!$bloqueado && $segundosRestantes <= 0 && $count >= self::CODIGO_MAX_TENTATIVAS) {
            $this->limparTentativas($user);

            return [
                'bloqueado' => false,
                'segundos_restantes' => 0,
                'tentativas_restantes' => self::CODIGO_MAX_TENTATIVAS,
            ];
        }

        return [
            'bloqueado' => $bloqueado,
            'segundos_restantes' => $segundosRestantes,
            'tentativas_restantes' => $tentativasRestantes,
        ];
    }

    /**
     * @return array{bloqueado: bool, segundos_restantes: int, tentativas_restantes: int}
     */
    public function registrarFalha(User $user): array
    {
        $statusAtual = $this->statusTentativas($user);
        if ($statusAtual['bloqueado']) {
            return $statusAtual;
        }

        $dados = Cache::get($this->chaveTentativas($user->id));
        $count = is_array($dados) ? (int) ($dados['count'] ?? 0) : 0;
        $count++;

        $bloqueadoAte = 0;
        if ($count >= self::CODIGO_MAX_TENTATIVAS) {
            $bloqueadoAte = now()->addMinutes(self::CODIGO_BLOQUEIO_MINUTOS)->timestamp;
        }

        Cache::put($this->chaveTentativas($user->id), [
            'count' => $count,
            'bloqueado_ate' => $bloqueadoAte,
        ], now()->addMinutes(self::CODIGO_BLOQUEIO_MINUTOS + 5));

        return $this->statusTentativas($user);
    }

    public function limparTentativas(User $user): void
    {
        Cache::forget($this->chaveTentativas($user->id));
    }

    public function limparChallengeSessao(Request $request): void
    {
        $request->session()->forget(self::SESSION_PENDING_KEY);
    }

    public function userPendente(Request $request): ?User
    {
        $pending = $request->session()->get(self::SESSION_PENDING_KEY);
        if (!is_array($pending) || empty($pending['user_id'])) {
            return null;
        }

        return User::withoutGlobalScopes()
            ->where('id', (int) $pending['user_id'])
            ->where('ativo', true)
            ->first();
    }

    public function rememberPendente(Request $request): bool
    {
        $pending = $request->session()->get(self::SESSION_PENDING_KEY);

        return is_array($pending) && !empty($pending['remember']);
    }

    /**
     * @return array{canais?: string[], destinos_mascarados?: array<string, string>}
     */
    public function destinosSessao(Request $request): array
    {
        $pending = $request->session()->get(self::SESSION_PENDING_KEY);
        if (!is_array($pending)) {
            return [];
        }

        return [
            'canais' => $pending['canais'] ?? [],
            'destinos_mascarados' => $pending['destinos_mascarados'] ?? [],
        ];
    }

    public function ttlMinutos(): int
    {
        return self::CODIGO_TTL_MINUTOS;
    }

    /**
     * Segundos restantes de validade do código atual (0 se inexistente/expirado).
     */
    public function segundosRestantesCodigo(User $user): int
    {
        $registro = Cache::get($this->chaveCodigo($user->id));
        if (!is_array($registro) || empty($registro['expira_em'])) {
            return 0;
        }

        return max(0, (int) $registro['expira_em'] - now()->timestamp);
    }

    protected function enviarCodigo(User $user, array $canais): void
    {
        $codigo = Str::upper(Str::random(8));
        $expiraEm = now()->addMinutes(self::CODIGO_TTL_MINUTOS);

        Cache::put($this->chaveCodigo($user->id), [
            'hash' => hash('sha256', $codigo),
            'expira_em' => $expiraEm->timestamp,
        ], $expiraEm);

        Cache::put($this->chaveCooldown($user->id), [
            'reenviar_apos' => now()->addSeconds(self::CODIGO_REENVIO_COOLDOWN_SEGUNDOS)->timestamp,
        ], now()->addMinutes(30));

        $empresaNome = $this->nomeEmpresa($user);

        if (isset($canais['email'])) {
            JobEnvioCodigoMfaLoginEmail::dispatch(
                (int) $user->id,
                $codigo,
                self::CODIGO_TTL_MINUTOS,
                $empresaNome,
                $user->empresa_id ? (int) $user->empresa_id : null
            );
        }

        if (isset($canais['whatsapp'])) {
            JobSendNotificacaoWhatsApp::dispatch([
                'enviado_id' => (int) $user->id,
                'telefone' => $canais['whatsapp'],
                'mensagem' => sprintf(
                    'MyBP: seu código de verificação de login é %s. Válido por %d minutos. Se não solicitou, ignore.',
                    $codigo,
                    self::CODIGO_TTL_MINUTOS
                ),
                'sistema' => true,
                '_whatsapp_meta' => [
                    'tipo' => 'mfa_login',
                    'empresa_id' => (int) $user->empresa_id,
                ],
            ]);
        }

        Log::info('MfaLoginService: código MFA gerado', [
            'user_id' => $user->id,
            'canais' => array_keys($canais),
        ]);
    }

    /**
     * @return array<string, string> canal => destino bruto
     */
    protected function resolverCanaisDisponiveis(User $user, ClienteConfig $config): array
    {
        $canais = [];

        if ($config->mfa_login_email) {
            $email = trim((string) $user->login);
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $canais['email'] = $email;
            }
        }

        if ($config->mfa_login_whatsapp && $config->envia_whatsapp) {
            $telefone = $this->telefoneWhatsappUsuario($user);
            if ($telefone !== null) {
                $canais['whatsapp'] = $telefone;
            }
        }

        return $canais;
    }

    protected function telefoneWhatsappUsuario(User $user): ?string
    {
        $query = UsuarioTelefone::query()->where('user_id', $user->id);

        $principal = (clone $query)->where('principal', true)->first();
        if ($principal && $principal->sonumero) {
            return $principal->sonumero;
        }

        $whats = (clone $query)->where('tipo', UsuarioTelefone::TIPO_WHATS)->first();
        if ($whats && $whats->sonumero) {
            return $whats->sonumero;
        }

        $qualquer = $query->orderBy('id')->first();
        if ($qualquer && $qualquer->sonumero) {
            return $qualquer->sonumero;
        }

        return null;
    }

    /**
     * @param array<string, string> $canais
     * @return array<string, string>
     */
    protected function mascararDestinos(array $canais): array
    {
        $mascarados = [];

        if (isset($canais['email'])) {
            $mascarados['email'] = $this->mascararEmail($canais['email']);
        }

        if (isset($canais['whatsapp'])) {
            $digitos = preg_replace('/\D/', '', $canais['whatsapp']) ?? '';
            $mascarados['whatsapp'] = strlen($digitos) >= 4
                ? str_repeat('*', max(0, strlen($digitos) - 4)) . substr($digitos, -4)
                : '****';
        }

        return $mascarados;
    }

    protected function mascararEmail(string $email): string
    {
        $partes = explode('@', $email, 2);
        if (count($partes) !== 2) {
            return '***';
        }

        $local = $partes[0];
        $dominio = $partes[1];
        $inicio = substr($local, 0, 1);

        return $inicio . '***@' . $dominio;
    }

    protected function configDaEmpresa(User $user): ?ClienteConfig
    {
        if (!$user->empresa_id) {
            return null;
        }

        return ClienteConfig::query()
            ->where('cliente_id', $user->empresa_id)
            ->first();
    }

    protected function nomeEmpresa(User $user): string
    {
        if (!$user->empresa_id || !Schema::hasTable('clientes')) {
            return '';
        }

        $empresa = Cliente::withoutGlobalScopes()
            ->select(['id', 'razao_social', 'nome_fantasia'])
            ->find($user->empresa_id);

        if (!$empresa) {
            return '';
        }

        return (string) ($empresa->razao_social ?: $empresa->nome_fantasia ?: '');
    }

    protected function chaveCodigo(int $userId): string
    {
        return 'mfa_login:codigo:' . $userId;
    }

    protected function chaveCooldown(int $userId): string
    {
        return 'mfa_login:cooldown:' . $userId;
    }

    protected function chaveTentativas(int $userId): string
    {
        return 'mfa_login:tentativas:' . $userId;
    }
}
