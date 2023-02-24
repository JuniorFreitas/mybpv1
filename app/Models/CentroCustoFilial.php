<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

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
