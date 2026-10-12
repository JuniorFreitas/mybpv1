<?php

namespace App\Services\AcessoPublico;

use App\Models\Sistema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

/**
 * Código de acesso de uso único para telas públicas (CPF + código por e-mail).
 *
 * O registro precisa das colunas `acesso_codigo_hash`, `acesso_codigo_expira_em` (cast datetime)
 * e `acesso_codigo_usado_em`. O `$contexto` isola cooldown/tentativas por tipo de tela.
 */
class CodigoAcessoService
{
    // Mesmos tempos do MFA de login (MfaLoginService).
    public const CODIGO_TAMANHO = 8;
    public const CODIGO_TTL_MINUTOS = 10;
    public const CODIGO_MAX_TENTATIVAS = 5;
    public const CODIGO_BLOQUEIO_MINUTOS = 15;
    public const CODIGO_REENVIO_COOLDOWN_SEGUNDOS = 60;

    /**
     * Gera e persiste um novo código, respeitando bloqueio e cooldown. Retorna o código em claro
     * para o chamador enviar (nunca logar).
     */
    public function gerar(Model $registro, string $contexto): string
    {
        $status = $this->status($registro, $contexto);
        if ($status['bloqueio_segundos'] > 0) {
            throw new RuntimeException('Muitas tentativas inválidas. Aguarde o tempo indicado para tentar novamente.');
        }
        if ($status['reenvio_segundos'] > 0) {
            throw new RuntimeException('Aguarde o tempo indicado para reenviar o código.');
        }

        $codigo = $this->codigoAleatorio();

        // saveQuietly: tela pública não deve disparar auditoria/eventos de usuário logado.
        $registro->forceFill([
            'acesso_codigo_hash' => Hash::make($codigo),
            'acesso_codigo_expira_em' => now()->addMinutes(self::CODIGO_TTL_MINUTOS),
            'acesso_codigo_usado_em' => null,
        ])->saveQuietly();

        Cache::put($this->chaveCooldown($registro, $contexto), [
            'reenviar_apos' => now()->addSeconds(self::CODIGO_REENVIO_COOLDOWN_SEGUNDOS)->getTimestamp(),
        ], now()->addSeconds(self::CODIGO_REENVIO_COOLDOWN_SEGUNDOS));

        return $codigo;
    }

    /**
     * Valida e consome o código (uso único). Lança RuntimeException com a mensagem para o usuário.
     */
    public function validar(Model $registro, string $contexto, string $codigoInformado): void
    {
        if ($this->statusTentativas($registro, $contexto)['bloqueado']) {
            throw new RuntimeException('Muitas tentativas inválidas. Aguarde o tempo indicado para tentar novamente.');
        }

        if (empty($registro->acesso_codigo_hash) || empty($registro->acesso_codigo_expira_em)) {
            throw new RuntimeException('Solicite um novo código de acesso.');
        }

        if ($registro->acesso_codigo_usado_em) {
            throw new RuntimeException('Este código já foi utilizado. Solicite um novo.');
        }

        if (now()->greaterThan($registro->acesso_codigo_expira_em)) {
            throw new RuntimeException('Código expirado. Solicite um novo.');
        }

        $codigo = strtoupper(preg_replace('/\s+/', '', $codigoInformado) ?? '');
        if (strlen($codigo) !== self::CODIGO_TAMANHO || !Hash::check($codigo, $registro->acesso_codigo_hash)) {
            $status = $this->registrarFalha($registro, $contexto);
            if ($status['bloqueado']) {
                throw new RuntimeException('Muitas tentativas inválidas. Aguarde o tempo indicado para tentar novamente.');
            }

            throw new RuntimeException('Código inválido. Tentativas restantes: ' . $status['tentativas_restantes'] . '.');
        }

        $registro->forceFill([
            'acesso_codigo_usado_em' => now(),
            'acesso_codigo_hash' => null,
        ])->saveQuietly();

        Cache::forget($this->chaveTentativas($registro, $contexto));
        Cache::forget($this->chaveCooldown($registro, $contexto));
    }

    /**
     * Tempos (em segundos) que a tela usa para os contadores.
     *
     * @return array{codigo_segundos: int, reenvio_segundos: int, bloqueio_segundos: int, tentativas_restantes: int}
     */
    public function status(Model $registro, string $contexto): array
    {
        $codigoSegundos = 0;
        if (!empty($registro->acesso_codigo_hash) && $registro->acesso_codigo_expira_em && !$registro->acesso_codigo_usado_em) {
            $codigoSegundos = max(0, $registro->acesso_codigo_expira_em->getTimestamp() - now()->getTimestamp());
        }

        $reenviarApos = (int) (Cache::get($this->chaveCooldown($registro, $contexto))['reenviar_apos'] ?? 0);
        $tentativas = $this->statusTentativas($registro, $contexto);

        return [
            'codigo_segundos' => $codigoSegundos,
            'reenvio_segundos' => max(0, $reenviarApos - now()->getTimestamp()),
            'bloqueio_segundos' => $tentativas['bloqueado'] ? $tentativas['segundos_restantes'] : 0,
            'tentativas_restantes' => $tentativas['tentativas_restantes'],
        ];
    }

    public function cpfValido(string $cpf): bool
    {
        return (bool) Sistema::validaRuleCPF($cpf);
    }

    public function cpfConfere(string $informado, string $cadastrado): bool
    {
        $a = preg_replace('/\D/', '', $informado) ?? '';
        $b = preg_replace('/\D/', '', $cadastrado) ?? '';

        return strlen($a) === 11 && hash_equals($b, $a);
    }

    public function mascararEmail(string $email): string
    {
        if ($email === '' || !str_contains($email, '@')) {
            return '***';
        }
        [$user, $domain] = explode('@', $email, 2);

        return mb_substr($user, 0, 1) . '***@' . $domain;
    }

    /**
     * @return array{bloqueado: bool, segundos_restantes: int, tentativas_restantes: int}
     */
    protected function statusTentativas(Model $registro, string $contexto): array
    {
        $dados = Cache::get($this->chaveTentativas($registro, $contexto));
        $count = is_array($dados) ? (int) ($dados['count'] ?? 0) : 0;
        $bloqueadoAte = is_array($dados) ? (int) ($dados['bloqueado_ate'] ?? 0) : 0;
        $segundosRestantes = max(0, $bloqueadoAte - now()->getTimestamp());

        if ($count >= self::CODIGO_MAX_TENTATIVAS && $segundosRestantes <= 0) {
            Cache::forget($this->chaveTentativas($registro, $contexto));
            $count = 0;
        }

        return [
            'bloqueado' => $segundosRestantes > 0 && $count >= self::CODIGO_MAX_TENTATIVAS,
            'segundos_restantes' => $segundosRestantes,
            'tentativas_restantes' => max(0, self::CODIGO_MAX_TENTATIVAS - $count),
        ];
    }

    /**
     * @return array{bloqueado: bool, segundos_restantes: int, tentativas_restantes: int}
     */
    protected function registrarFalha(Model $registro, string $contexto): array
    {
        $count = self::CODIGO_MAX_TENTATIVAS - $this->statusTentativas($registro, $contexto)['tentativas_restantes'] + 1;

        Cache::put($this->chaveTentativas($registro, $contexto), [
            'count' => $count,
            'bloqueado_ate' => $count >= self::CODIGO_MAX_TENTATIVAS
                ? now()->addMinutes(self::CODIGO_BLOQUEIO_MINUTOS)->getTimestamp()
                : 0,
        ], now()->addMinutes(self::CODIGO_BLOQUEIO_MINUTOS + 5));

        return $this->statusTentativas($registro, $contexto);
    }

    protected function codigoAleatorio(): string
    {
        // Sem caracteres ambíguos (0/O, 1/I).
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $code = '';
        for ($i = 0; $i < self::CODIGO_TAMANHO; $i++) {
            $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        return $code;
    }

    protected function chaveCooldown(Model $registro, string $contexto): string
    {
        return $contexto . ':cooldown:' . $registro->getKey();
    }

    protected function chaveTentativas(Model $registro, string $contexto): string
    {
        return $contexto . ':tentativas:' . $registro->getKey();
    }
}
