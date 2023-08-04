<?php

namespace App\Models;

use App\Scopes\ScopeEmpresa;
use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\EmpresaConfig
 *
 * @property int $empresa_id
 * @property string $tipo_frequencia
 * @property int $tempo_limite_falta
 * @property int $tempo_limite_saida
 * @property string $dia_nova_frequencia
 * @property int $limite_tolerancia
 * @property-read \App\Models\User|null $Empresa
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaConfig newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaConfig newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaConfig query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaConfig whereDiaNovaFrequencia($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaConfig whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaConfig whereLimiteTolerancia($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaConfig whereTempoLimiteFalta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaConfig whereTempoLimiteSaida($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaConfig whereTipoFrequencia($value)
 * @mixin \Eloquent
 */
class EmpresaConfig extends Model
{
    use HasFactory, LogsActivity, TenantTrait;

    protected static $logFillable = true;
    protected static $logName = 'EmpresaConfiguracao';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = "";
    }

    public $timestamps = false;
    protected $table = 'empresa_configuracoes';
    protected $primaryKey = 'empresa_id';
    protected $fillable = [
        'empresa_id',
        'tipo_frequencia',
        'tempo_limite_falta',
        'tempo_limite_saida',
        'dia_nova_frequencia',
        'limite_tolerancia',
    ];
    protected $casts = [
        'empresa_id' => 'int',
        'tipo_frequencia' => 'string',
        'tempo_limite_falta' => 'int',
        'tempo_limite_saida' => 'int',
        'dia_nova_frequencia' => 'int',
        'limite_tolerancia' => 'int',
    ];

    public function setDiaNovaFrequenciaAttribute($value)
    {
        if ($value > 25){
            $value = 25;
        }
        $this->attributes['dia_nova_frequencia'] = (int)$value;
    }
    public function getDiaNovaFrequenciaAttribute($value): string
    {
        if ($value < 10) return '0' . $value;
        return (string)$value;
    }

    const TIPO_HORA_EXTRA = 'hora_extra';
    const TIPO_BANCO_HORAS = 'banco_horas';
    const TIPO_HIBRIDO = 'hibrido';

//    protected static function booted() {
//        static::creating(function ($model) {
//            $model->empresa_id = auth()->user()->empresa_id;
//        });
//
//        static::updating(function ($model) {
//            $model->empresa_id = auth()->user()->empresa_id;
//        });
//
//        static::addGlobalScope(new ScopeEmpresa());
//    }

    public function Empresa()
    {
        return $this->hasOne(User::class, 'id', 'empresa_id');
    }

}
