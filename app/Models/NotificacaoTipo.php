<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotificacaoTipo extends Model
{
    public $timestamps = false;

    protected $table = 'notificacao_tipos';

    protected $primaryKey = 'codigo';

    public $incrementing = false;

    protected $keyType = 'string';

    public const APROVACAO_RH = 'aprovacao_rh';

    public const REGRA_PRIVILEGIOS_RH = 'privilegios_rh';

    public const ESCOPO_TODOS = 'todos';

    public const ESCOPO_MATRIZ = 'matriz';

    public const ESCOPO_FILIAIS = 'filiais';

    public const ESCOPO_ESPECIFICOS = 'especificos';

    public const ESCOPOS_CC = [
        self::ESCOPO_TODOS,
        self::ESCOPO_MATRIZ,
        self::ESCOPO_FILIAIS,
        self::ESCOPO_ESPECIFICOS,
    ];

    protected $fillable = [
        'codigo',
        'nome',
        'canal',
        'regra_elegibilidade',
        'ativo',
        'editavel',
        'ordem',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'editavel' => 'boolean',
        'ordem' => 'int',
    ];

    public function grupos(): HasMany
    {
        return $this->hasMany(NotificacaoGrupo::class, 'tipo_codigo', 'codigo');
    }
}
