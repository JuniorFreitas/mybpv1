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

/**
 * App\Models\EmpresaPerimetro
 *
 * @property int $id
 * @property int $empresa_id
 * @property string $descricao
 * @property float $lat
 * @property float $long
 * @property int $perimetro
 * @property bool $obrigatorio
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $user_deletou_id
 * @property-read \App\Models\User|null $Empresa
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaPerimetro newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaPerimetro newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaPerimetro onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaPerimetro query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaPerimetro whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaPerimetro whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaPerimetro whereDescricao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaPerimetro whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaPerimetro whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaPerimetro whereLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaPerimetro whereLong($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaPerimetro whereObrigatorio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaPerimetro wherePerimetro($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaPerimetro whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaPerimetro whereUserDeletouId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaPerimetro withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaPerimetro withoutTrashed()
 * @mixin \Eloquent
 */
class EmpresaPerimetro extends Model
{
    use HasFactory,LogsActivity, TenantTrait,SoftDeletes;
    protected static $logFillable = true;
    protected static $logName = 'EmpresaPerimetros';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;
    public $timestamps=true;
    protected $table = 'empresa_perimetros';
    protected $fillable = [
        'empresa_id' ,
        'descricao' ,
        'lat' ,
        'long' ,
        'perimetro' ,
        'obrigatorio'
    ];
    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'descricao' => 'string',
        'lat' => 'float',
        'long' => 'float',
        'perimetro' => 'int',
        'obrigatorio' => 'boolean',
        'created_at' => 'datetime:d/m/Y à\s H:i:s',
        'updated_at' => 'datetime:d/m/Y à\s H:i:s',
    ];

    public function getDescriptionForEvent(string $eventName): string {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName) {
        $activity->descricao = "";
    }
    protected function serializeDate(DateTimeInterface $date) {
        return $date->format('Y-m-d H:i:s');
    }

    public function Empresa(){
        return $this->hasOne(User::class,'id','empresa_id');
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
