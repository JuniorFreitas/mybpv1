<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LogicException;

/**
 * Decisão eletrônica do candidato sobre a carta oferta (aceite ou recusa) e suas evidências.
 * Depois de gravada não muda: só o comprovante em PDF é anexado em seguida.
 */
class CartaOfertaAceite extends Model
{
    public const DECISAO_ACEITO = 'aceito';
    public const DECISAO_RECUSADO = 'recusado';

    protected $table = 'curriculo_carta_oferta_aceites';

    protected $fillable = [
        'carta_oferta_id',
        'empresa_id',
        'curriculo_id',
        'decisao',
        'motivo',
        'nome',
        'email',
        'cpf',
        'ip',
        'user_agent',
        'geolocalizacao',
        'consentimento',
        'consentimento_texto',
        'data_decisao_utc',
        'proposta_arquivo_id',
        'proposta_hash',
        'hash_evidencia',
        'codigo_verificacao',
        'comprovante_arquivo_id',
    ];

    protected $casts = [
        'id' => 'int',
        'carta_oferta_id' => 'int',
        'empresa_id' => 'int',
        'curriculo_id' => 'int',
        'decisao' => 'string',
        'motivo' => 'string',
        'nome' => 'string',
        'email' => 'string',
        'cpf' => 'string',
        'ip' => 'string',
        'user_agent' => 'string',
        'geolocalizacao' => 'array',
        'consentimento' => 'boolean',
        'consentimento_texto' => 'string',
        'data_decisao_utc' => 'datetime',
        'proposta_arquivo_id' => 'int',
        'proposta_hash' => 'string',
        'hash_evidencia' => 'string',
        'codigo_verificacao' => 'string',
        'comprovante_arquivo_id' => 'int',
    ];

    protected $appends = ['data_decisao_br'];

    /**
     * Data da decisão no horário de Brasília (o banco guarda em UTC).
     */
    public function getDataDecisaoBrAttribute(): ?string
    {
        $raw = $this->getRawOriginal('data_decisao_utc');

        return $raw
            ? \Carbon\Carbon::parse($raw, 'UTC')->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i:s')
            : null;
    }

    public function aceito(): bool
    {
        return $this->decisao === self::DECISAO_ACEITO;
    }

    public function cartaOferta()
    {
        return $this->belongsTo(CartaOferta::class, 'carta_oferta_id');
    }

    public function comprovante()
    {
        return $this->belongsTo(Arquivo::class, 'comprovante_arquivo_id');
    }

    protected static function booted()
    {
        static::updating(function (self $aceite) {
            $alterados = array_diff(array_keys($aceite->getDirty()), ['comprovante_arquivo_id', 'updated_at']);
            if ($alterados !== []) {
                throw new LogicException('A decisão da carta oferta é imutável.');
            }
        });

        static::deleting(function () {
            throw new LogicException('A decisão da carta oferta é imutável.');
        });
    }
}
