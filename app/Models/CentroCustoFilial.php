<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\CentroCustoFilial
 *
 * @property int $id
 * @property int $empresa_id
 * @property int $centro_custo_id
 * @property int $cliente_filial_id
 * @property bool $ativo
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CentroCusto $CentroCusto
 * @property-read \App\Models\Cliente $Empresa
 * @property-read \App\Models\ClienteFilial $Filial
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|CentroCustoFilial newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CentroCustoFilial newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CentroCustoFilial onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|CentroCustoFilial query()
 * @method static \Illuminate\Database\Eloquent\Builder|CentroCustoFilial whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CentroCustoFilial whereCentroCustoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CentroCustoFilial whereClienteFilialId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CentroCustoFilial whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CentroCustoFilial whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CentroCustoFilial whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CentroCustoFilial whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CentroCustoFilial whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CentroCustoFilial withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|CentroCustoFilial withoutTrashed()
 * @mixin \Eloquent
 */
class CentroCustoFilial extends Model
{
    use HasFactory, SoftDeletes, TenantTrait, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'CentroCustoFilial';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    public function getDescriptionForEvent(string $eventName)
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = "";
    }

    protected $fillable = [
        'empresa_id',
        'centro_custo_id',
        'cliente_filial_id',
        'ativo',
    ];

    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'centro_custo_id' => 'int',
        'cliente_filial_id' => 'int',
        'ativo' => 'boolean',
    ];

    public function Empresa()
    {
        return $this->belongsTo(Cliente::class, 'empresa_id');
    }

    public function CentroCusto()
    {
        return $this->belongsTo(CentroCusto::class, 'centro_custo_id');
    }

    public function Filial()
    {
        return $this->belongsTo(ClienteFilial::class, 'cliente_filial_id');
    }
}
