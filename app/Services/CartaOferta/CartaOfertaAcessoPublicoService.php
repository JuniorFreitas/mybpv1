<?php

namespace App\Services\CartaOferta;

use App\Jobs\JobEnvioCodigoAcessoCartaOferta;
use App\Models\CartaOferta;
use App\Models\Cliente;
use App\Models\Sistema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class CartaOfertaAcessoPublicoService
{
    // Mesmos tempos do MFA de login (MfaLoginService).
    public const CODIGO_TAMANHO = 8;
    public const CODIGO_TTL_MINUTOS = 10;
    public const CODIGO_MAX_TENTATIVAS = 5;
    public const CODIGO_BLOQUEIO_MINUTOS = 15;
    public const CODIGO_REENVIO_COOLDOWN_SEGUNDOS = 60;

    public function encontrarPorPublicId(string $apelido, string $publicId): ?CartaOferta
    {
        $empresa = Cliente::withoutGlobalScopes()
            ->select(['id', 'apelido', 'nome_fantasia', 'razao_social'])
            ->where('apelido', $apelido)
            ->first();

        if (!$empresa) {
            return null;
        }

        $carta = CartaOferta::withoutGlobalScopes()
            ->where('empresa_id', $empresa->id)
            ->where(function ($q) use ($publicId) {
                $q->where('public_id', $publicId)
                    ->orWhere('token', $publicId);
            })
            ->first();

        if ($carta) {
            $carta->setRelation('empresa', $empresa);
        }

        return $carta;
    }

    public function payloadPublicoMinimo(CartaOferta $carta): array
    {
        $empresa = $this->resolverEmpresa($carta);

        return [
            'public_id' => $carta->public_id ?: $carta->token,
            'status' => $carta->status,
            'empresa' => [
                'apelido' => $empresa->apelido ?? null,
                'nome' => $empresa->nome_fantasia ?? $empresa->razao_social ?? null,
            ],
            'autenticado' => false,
        ];
    }

    /**
     * Payload para quem já confirmou o CPF nesta sessão e aguarda digitar o código.
     */
    public function payloadDesafioPendente(CartaOferta $carta): array
    {
        $carta->loadMissing([
            'Curriculo' => function ($q) {
                $q->withoutGlobalScopes()->select(['id', 'nome', 'cpf', 'email']);
            },
        ]);

        return $this->payloadPublicoMinimo($carta) + [
            'desafio_pendente' => true,
            'email_mascarado' => $this->mascararEmail((string) ($carta->Curriculo->email ?? '')),
            'acesso' => $this->statusAcesso($carta),
        ];
    }

    public function payloadAutenticado(CartaOferta $carta): array
    {
        $carta->loadMissing([
            'Curriculo' => function ($q) {
                $q->withoutGlobalScopes()->select(['id', 'nome', 'email']);
            },
        ]);

        $cargo = 'Não informado';
        if ($carta->vagas_abertas_id) {
            try {
                $carta->loadMissing([
                    'vagaAberta' => function ($q) {
                        $q->withoutGlobalScopes()
                            ->select(['id', 'vaga_id', 'titulo'])
                            ->with(['Vaga' => function ($q) {
                                $q->withoutGlobalScopes()->select(['id', 'nome']);
                            }]);
                    },
                ]);
                $cargo = $carta->vagaAberta?->Vaga?->nome
                    ?? $carta->vagaAberta?->titulo
                    ?? 'Não informado';
            } catch (\Throwable $e) {
                $cargo = 'Não informado';
            }
        }

        $email = (string) ($carta->Curriculo->email ?? '');
        $empresa = $this->resolverEmpresa($carta);

        return [
            'public_id' => $carta->public_id ?: $carta->token,
            'status' => $carta->status,
            'curriculo_id' => $carta->curriculo_id,
            'candidato' => [
                'nome' => $carta->Curriculo->nome ?? 'Não informado',
                'email_mascarado' => $this->mascararEmail($email),
            ],
            'cargo' => $cargo,
            'empresa' => [
                'apelido' => $empresa->apelido ?? null,
                'nome' => $empresa->nome_fantasia ?? $empresa->razao_social ?? null,
            ],
            'autenticado' => true,
        ];
    }

    protected function resolverEmpresa(CartaOferta $carta): ?Cliente
    {
        if ($carta->relationLoaded('empresa') && $carta->empresa) {
            return $carta->empresa;
        }

        return Cliente::withoutGlobalScopes()
            ->select(['id', 'apelido', 'nome_fantasia', 'razao_social'])
            ->find($carta->empresa_id);
    }

    public function solicitarCodigo(CartaOferta $carta, string $cpfInformado): array
    {
        if (!Sistema::validaRuleCPF($cpfInformado)) {
            throw new RuntimeException('CPF inválido.');
        }

        $this->carregarCurriculo($carta);

        if (!$this->cpfConfere($cpfInformado, (string) $carta->Curriculo->cpf)) {
            throw new RuntimeException('CPF não confere com o cadastro desta carta oferta.');
        }

        return $this->enviarCodigo($carta);
    }

    /**
     * Reenvio para quem já confirmou o CPF (o chamador garante essa confirmação).
     */
    public function reenviarCodigo(CartaOferta $carta): array
    {
        $this->carregarCurriculo($carta);

        return $this->enviarCodigo($carta);
    }

    public function validarCodigo(CartaOferta $carta, string $codigoInformado): array
    {
        if ($this->statusTentativas($carta)['bloqueado']) {
            throw new RuntimeException('Muitas tentativas inválidas. Aguarde o tempo indicado para tentar novamente.');
        }

        if (empty($carta->acesso_codigo_hash) || empty($carta->acesso_codigo_expira_em)) {
            throw new RuntimeException('Solicite um novo código de acesso.');
        }

        if ($carta->acesso_codigo_usado_em) {
            throw new RuntimeException('Este código já foi utilizado. Solicite um novo.');
        }

        if (now()->greaterThan($carta->acesso_codigo_expira_em)) {
            throw new RuntimeException('Código expirado. Solicite um novo.');
        }

        $codigo = strtoupper(preg_replace('/\s+/', '', $codigoInformado) ?? '');
        if (strlen($codigo) !== self::CODIGO_TAMANHO || !Hash::check($codigo, $carta->acesso_codigo_hash)) {
            $status = $this->registrarFalha($carta);
            if ($status['bloqueado']) {
                throw new RuntimeException('Muitas tentativas inválidas. Aguarde o tempo indicado para tentar novamente.');
            }

            throw new RuntimeException('Código inválido. Tentativas restantes: ' . $status['tentativas_restantes'] . '.');
        }

        $carta->forceFill([
            'acesso_codigo_usado_em' => now(),
            'acesso_codigo_hash' => null,
        ])->save();

        Cache::forget($this->chaveTentativas($carta));
        Cache::forget($this->chaveCooldown($carta));

        return $this->payloadAutenticado($carta);
    }

    /**
     * Tempos (em segundos) que a tela usa para os contadores.
     *
     * @return array{codigo_segundos: int, reenvio_segundos: int, bloqueio_segundos: int, tentativas_restantes: int}
     */
    public function statusAcesso(CartaOferta $carta): array
    {
        $codigoSegundos = 0;
        if (!empty($carta->acesso_codigo_hash) && $carta->acesso_codigo_expira_em && !$carta->acesso_codigo_usado_em) {
            $codigoSegundos = max(0, $carta->acesso_codigo_expira_em->getTimestamp() - now()->getTimestamp());
        }

        $reenviarApos = (int) (Cache::get($this->chaveCooldown($carta))['reenviar_apos'] ?? 0);
        $tentativas = $this->statusTentativas($carta);

        return [
            'codigo_segundos' => $codigoSegundos,
            'reenvio_segundos' => max(0, $reenviarApos - now()->getTimestamp()),
            'bloqueio_segundos' => $tentativas['bloqueado'] ? $tentativas['segundos_restantes'] : 0,
            'tentativas_restantes' => $tentativas['tentativas_restantes'],
        ];
    }

    protected function carregarCurriculo(CartaOferta $carta): void
    {
        $carta->loadMissing([
            'Curriculo' => function ($q) {
                $q->withoutGlobalScopes()->select(['id', 'nome', 'cpf', 'email']);
            },
        ]);

        if (!$carta->Curriculo) {
            throw new RuntimeException('Carta oferta inválida.');
        }
    }

    protected function enviarCodigo(CartaOferta $carta): array
    {
        $status = $this->statusAcesso($carta);
        if ($status['bloqueio_segundos'] > 0) {
            throw new RuntimeException('Muitas tentativas inválidas. Aguarde o tempo indicado para tentar novamente.');
        }
        if ($status['reenvio_segundos'] > 0) {
            throw new RuntimeException('Aguarde o tempo indicado para reenviar o código.');
        }

        $email = trim((string) $carta->Curriculo->email);
        if ($email === '') {
            throw new RuntimeException('Não há e-mail cadastrado para envio do código.');
        }

        $codigo = $this->gerarCodigo8();
        $carta->forceFill([
            'acesso_codigo_hash' => Hash::make($codigo),
            'acesso_codigo_expira_em' => now()->addMinutes(self::CODIGO_TTL_MINUTOS),
            'acesso_codigo_usado_em' => null,
        ])->save();

        Cache::put($this->chaveCooldown($carta), [
            'reenviar_apos' => now()->addSeconds(self::CODIGO_REENVIO_COOLDOWN_SEGUNDOS)->getTimestamp(),
        ], now()->addSeconds(self::CODIGO_REENVIO_COOLDOWN_SEGUNDOS));

        JobEnvioCodigoAcessoCartaOferta::dispatch([
            'nome' => $carta->Curriculo->nome,
            'email' => $email,
            'empresa_id' => $carta->empresa_id,
            'codigo' => $codigo,
            'minutos' => self::CODIGO_TTL_MINUTOS,
        ]);

        return [
            'message' => 'Código enviado para o e-mail cadastrado.',
            'email_mascarado' => $this->mascararEmail($email),
            'expira_em_minutos' => self::CODIGO_TTL_MINUTOS,
            'acesso' => $this->statusAcesso($carta),
        ];
    }

    /**
     * @return array{bloqueado: bool, segundos_restantes: int, tentativas_restantes: int}
     */
    protected function statusTentativas(CartaOferta $carta): array
    {
        $dados = Cache::get($this->chaveTentativas($carta));
        $count = is_array($dados) ? (int) ($dados['count'] ?? 0) : 0;
        $bloqueadoAte = is_array($dados) ? (int) ($dados['bloqueado_ate'] ?? 0) : 0;
        $segundosRestantes = max(0, $bloqueadoAte - now()->getTimestamp());

        if ($count >= self::CODIGO_MAX_TENTATIVAS && $segundosRestantes <= 0) {
            Cache::forget($this->chaveTentativas($carta));
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
    protected function registrarFalha(CartaOferta $carta): array
    {
        $count = self::CODIGO_MAX_TENTATIVAS - $this->statusTentativas($carta)['tentativas_restantes'] + 1;

        Cache::put($this->chaveTentativas($carta), [
            'count' => $count,
            'bloqueado_ate' => $count >= self::CODIGO_MAX_TENTATIVAS
                ? now()->addMinutes(self::CODIGO_BLOQUEIO_MINUTOS)->getTimestamp()
                : 0,
        ], now()->addMinutes(self::CODIGO_BLOQUEIO_MINUTOS + 5));

        return $this->statusTentativas($carta);
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
        $userMask = mb_substr($user, 0, 1) . '***';

        return $userMask . '@' . $domain;
    }

    protected function gerarCodigo8(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $code = '';
        for ($i = 0; $i < self::CODIGO_TAMANHO; $i++) {
            $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        return $code;
    }

    public function sessionKey(string $publicId): string
    {
        return 'carta_oferta_auth_' . $publicId;
    }

    /**
     * Marca que o CPF já foi confirmado nesta sessão (permite reenviar sem redigitar).
     */
    public function sessionKeyCpfConfirmado(string $publicId): string
    {
        return 'carta_oferta_cpf_ok_' . $publicId;
    }

    protected function chaveCooldown(CartaOferta $carta): string
    {
        return 'carta_oferta_acesso:cooldown:' . $carta->id;
    }

    protected function chaveTentativas(CartaOferta $carta): string
    {
        return 'carta_oferta_acesso:tentativas:' . $carta->id;
    }
}
