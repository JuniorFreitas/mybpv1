<?php

namespace App\Services\AcessoPublico;

use App\Models\AcessoPublicoAuditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Grava a trilha de evidências das telas públicas. Cada registro leva um HMAC do próprio conteúdo
 * e o hash do evento anterior do mesmo registro, para evidenciar alteração ou remoção no banco.
 */
class AuditoriaAcessoService
{
    public const EVENTO_CODIGO_SOLICITADO = 'codigo_solicitado';
    public const EVENTO_CPF_CONFIRMADO = 'cpf_confirmado';
    public const EVENTO_CODIGO_REENVIADO = 'codigo_reenviado';
    public const EVENTO_CODIGO_NEGADO = 'codigo_negado';
    public const EVENTO_CODIGO_VALIDADO = 'codigo_validado';
    public const EVENTO_CODIGO_INVALIDO = 'codigo_invalido';
    public const EVENTO_TERMO_ACEITO = 'termo_aceito';
    public const EVENTO_DOCUMENTOS_ENVIADOS = 'documentos_enviados';
    public const EVENTO_COMPROVANTE_EMITIDO = 'comprovante_emitido';
    public const EVENTO_CARTA_ENVIADA = 'carta_enviada';
    public const EVENTO_PROPOSTA_VISUALIZADA = 'proposta_visualizada';
    public const EVENTO_CARTA_ACEITA = 'carta_aceita';
    public const EVENTO_CARTA_RECUSADA = 'carta_recusada';

    /**
     * @param array{registro_id?: ?int, curriculo_id?: ?int, protocolo?: ?string, cpf?: ?string,
     *              email_mascarado?: ?string, dados?: array, ip?: ?string, user_agent?: ?string} $info
     */
    public function registrar(string $contexto, int $empresaId, string $evento, ?Request $request = null, array $info = []): AcessoPublicoAuditoria
    {
        $registroId = isset($info['registro_id']) ? (int) $info['registro_id'] : null;

        $atributos = [
            'empresa_id' => $empresaId,
            'contexto' => $contexto,
            'registro_id' => $registroId,
            'curriculo_id' => isset($info['curriculo_id']) ? (int) $info['curriculo_id'] : null,
            'evento' => $evento,
            'protocolo' => $info['protocolo'] ?? null,
            'ip' => $info['ip'] ?? $request?->ip(),
            'user_agent' => Str::limit((string) ($info['user_agent'] ?? $request?->userAgent()), 500, ''),
            'cpf_mascarado' => $this->mascararCpf($info['cpf'] ?? null),
            'email_mascarado' => $info['email_mascarado'] ?? null,
            'dados' => $info['dados'] ?? null,
            'created_at' => now()->startOfSecond(),
        ];

        $atributos['hash_anterior'] = $registroId
            ? AcessoPublicoAuditoria::query()
                ->where('contexto', $contexto)
                ->where('registro_id', $registroId)
                ->orderByDesc('id')
                ->value('hash')
            : null;
        $atributos['hash'] = $this->assinar($atributos);

        return AcessoPublicoAuditoria::create($atributos);
    }

    /**
     * Confere se o registro continua igual ao que foi gravado.
     */
    public function integro(AcessoPublicoAuditoria $registro): bool
    {
        $atributos = $registro->only([
            'empresa_id', 'contexto', 'registro_id', 'curriculo_id', 'evento', 'protocolo', 'ip',
            'user_agent', 'cpf_mascarado', 'email_mascarado', 'dados', 'created_at', 'hash_anterior',
        ]);

        return hash_equals((string) $registro->hash, $this->assinar($atributos));
    }

    public function novoProtocolo(string $prefixo): string
    {
        return $prefixo . '-' . now()->format('Ymd') . '-' . Str::upper(Str::random(8));
    }

    /**
     * Evidência guarda o CPF mascarado (o completo já está no cadastro do candidato).
     */
    public function mascararCpf(?string $cpf): ?string
    {
        $digitos = preg_replace('/\D/', '', (string) $cpf) ?? '';
        if (strlen($digitos) !== 11) {
            return $digitos === '' ? null : '***';
        }

        return '***.' . substr($digitos, 3, 3) . '.' . substr($digitos, 6, 3) . '-**';
    }

    protected function assinar(array $atributos): string
    {
        $atributos['created_at'] = $atributos['created_at'] instanceof \DateTimeInterface
            ? $atributos['created_at']->format('Y-m-d H:i:s')
            : (string) $atributos['created_at'];
        ksort($atributos);

        return hash_hmac(
            'sha256',
            json_encode($atributos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            (string) config('app.key')
        );
    }
}
