<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class NotificacaoGrupo extends Model
{
    use SoftDeletes;
    use TenantTrait;

    protected $table = 'notificacao_grupos';

    protected $fillable = [
        'empresa_id',
        'tipo_codigo',
        'nome',
        'ativo',
        'escopo_cc',
    ];

    protected $casts = [
        'empresa_id' => 'int',
        'tipo_codigo' => 'string',
        'nome' => 'string',
        'ativo' => 'boolean',
        'escopo_cc' => 'string',
        'deleted_at' => 'datetime',
    ];

    public function tipo(): BelongsTo
    {
        return $this->belongsTo(NotificacaoTipo::class, 'tipo_codigo', 'codigo');
    }

    public function destinatarios(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'notificacao_grupo_destinatarios',
            'grupo_id',
            'user_id'
        )->withPivot([]);
    }

    public function centrosCusto(): BelongsToMany
    {
        return $this->belongsToMany(
            CentroCusto::class,
            'notificacao_grupo_centros_custo',
            'grupo_id',
            'centro_custo_id'
        )->withPivot([]);
    }
}
