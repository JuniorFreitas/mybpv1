<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Vínculo do médico com a empresa (papel, clínica, vigência).
 */
class SaudeMedicoVinculo extends Model
{
    use SoftDeletes;

    public const TABELA = 'saude_medicos_vinculos';

    public const PAPEL_COORDENADOR = 'coordenador';

    public const PAPEL_EXAMINADOR = 'examinador';

    public const PAPEIS = [self::PAPEL_COORDENADOR, self::PAPEL_EXAMINADOR];

    protected $table = self::TABELA;

    protected $fillable = [
        'saude_medico_id',
        'empresa_id',
        'papel',
        'empresa_exame_id',
        'vigencia_inicio',
        'vigencia_fim',
    ];

    protected $casts = [
        'id' => 'int',
        'saude_medico_id' => 'int',
        'empresa_id' => 'int',
        'papel' => 'string',
        'empresa_exame_id' => 'int',
        'vigencia_inicio' => 'date',
        'vigencia_fim' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $appends = ['vigencia_inicio_br', 'vigencia_fim_br'];

    public function getVigenciaInicioBrAttribute(): ?string
    {
        return $this->vigencia_inicio?->format('d/m/Y');
    }

    public function getVigenciaFimBrAttribute(): ?string
    {
        return $this->vigencia_fim?->format('d/m/Y');
    }

    public function Medico()
    {
        return $this->belongsTo(SaudeMedico::class, 'saude_medico_id');
    }
}
