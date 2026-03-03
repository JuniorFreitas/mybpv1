<?php

namespace App\Models;

use App\Scopes\ScopeEmpresa;
use App\Tenant\Traits\TenantTrait;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;

/**
 * App\Models\EmpresaEscala
 *
 * @property int $id
 * @property int $empresa_id
 * @property string $descricao
 * @property \Illuminate\Support\Carbon $inicio
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $user_deletou_id
 * @property-read \App\Models\User|null $Empresa
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EscalaJornada> $Jornadas
 * @property-read int|null $jornadas_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaEscala newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaEscala newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaEscala onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaEscala query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaEscala whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaEscala whereDescricao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaEscala whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaEscala whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaEscala whereInicio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaEscala whereUserDeletouId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaEscala withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaEscala withoutTrashed()
 * @mixin \Eloquent
 */
class EmpresaEscala extends Model
{
    use HasFactory, LogsActivity, HasActivitylogOptions, TenantTrait, SoftDeletes;

    protected static $logFillable = true;
    protected static $logName = 'EmpresaEscala';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;
    public $timestamps = false;
    protected $table = 'empresa_escalas';
    protected $fillable = [
        'descricao',
        'inicio',
    ];
    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'descricao' => 'string',
        'inicio' => 'datetime:d/m/Y',

    ];

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = "";
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function Empresa()
    {
        return $this->hasOne(User::class, 'id', 'empresa_id');
    }

    public function Jornadas()
    {
        return $this->hasMany(EscalaJornada::class, 'escala_id', 'id');
    }

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
}
