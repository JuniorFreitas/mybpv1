<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LogicException;

/**
 * Evidência de um ato feito em tela pública (sem login). Somente inserção: não altera nem exclui.
 */
class AcessoPublicoAuditoria extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'acessos_publicos_auditorias';

    protected $fillable = [
        'empresa_id',
        'contexto',
        'registro_id',
        'curriculo_id',
        'evento',
        'protocolo',
        'ip',
        'user_agent',
        'cpf_mascarado',
        'email_mascarado',
        'dados',
        'hash_anterior',
        'hash',
        'created_at',
    ];

    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'registro_id' => 'int',
        'curriculo_id' => 'int',
        'contexto' => 'string',
        'evento' => 'string',
        'protocolo' => 'string',
        'ip' => 'string',
        'user_agent' => 'string',
        'cpf_mascarado' => 'string',
        'email_mascarado' => 'string',
        'dados' => 'array',
        'hash_anterior' => 'string',
        'hash' => 'string',
        'created_at' => 'datetime',
    ];

    protected $appends = ['created_at_br'];

    public function getCreatedAtBrAttribute(): ?string
    {
        return $this->created_at?->format('d/m/Y H:i:s');
    }

    protected static function booted()
    {
        static::updating(function () {
            throw new LogicException('Registro de auditoria é imutável.');
        });

        static::deleting(function () {
            throw new LogicException('Registro de auditoria é imutável.');
        });
    }
}
