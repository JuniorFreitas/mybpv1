<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Model;

class CentroCustoGestor extends Model
{
    use TenantTrait;

    public const TIPO_GESTOR_PRINCIPAL = 'GESTOR_PRINCIPAL';
    public const TIPO_GESTOR_SUBSTITUTO = 'GESTOR_SUBSTITUTO';

    protected $table = 'centro_custo_gestores';

    protected $fillable = [
        'centro_custo_id',
        'usuario_id',
        'tipo',
        'ativo',
        'inicio_vigencia',
        'fim_vigencia',
        'empresa_id',
    ];

    protected $casts = [
        'id' => 'int',
        'centro_custo_id' => 'int',
        'usuario_id' => 'int',
        'tipo' => 'string',
        'ativo' => 'boolean',
        'inicio_vigencia' => 'date',
        'fim_vigencia' => 'date',
        'empresa_id' => 'int',
    ];

    public function CentroCusto()
    {
        return $this->belongsTo(CentroCusto::class, 'centro_custo_id', 'id');
    }

    public function Usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id', 'id')
            ->select(['id', 'nome', 'login', 'ativo', 'gestor_superior_id']);
    }
}
