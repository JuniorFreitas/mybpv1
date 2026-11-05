<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * ASO emitido (NR-7) de um resultado de exame: foto do documento na emissão + assinatura digital.
 *
 * @property int $id
 * @property int $examesesmt_id
 * @property int $empresa_id
 * @property array{nome: string, crm: string, crm_uf: string} $examinador
 * @property array{nome: string, crm: string, crm_uf: string}|null $coordenador
 * @property list<string> $riscos
 * @property list<array{label: string, data: string|null}> $exames
 */
class ExamesesmtAso extends Model
{
    public const TABELA = 'examesesmts_aso';

    protected $table = self::TABELA;

    protected $fillable = [
        'examesesmt_id',
        'empresa_id',
        'saude_medico_id',
        'examinador',
        'coordenador',
        'funcao',
        'riscos',
        'exames',
        'documento_assinatura_id',
        'emitido_por',
        'emitido_em',
    ];

    protected $casts = [
        'id' => 'int',
        'examesesmt_id' => 'int',
        'empresa_id' => 'int',
        'saude_medico_id' => 'int',
        'examinador' => 'array',
        'coordenador' => 'array',
        'funcao' => 'string',
        'riscos' => 'array',
        'exames' => 'array',
        'documento_assinatura_id' => 'int',
        'emitido_por' => 'int',
        'emitido_em' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = ['emitido_em_br'];

    public function getEmitidoEmBrAttribute(): ?string
    {
        return $this->emitido_em?->format('d/m/Y H:i');
    }

    public function Documento()
    {
        return $this->belongsTo(DocumentoParaAssinatura::class, 'documento_assinatura_id');
    }
}
