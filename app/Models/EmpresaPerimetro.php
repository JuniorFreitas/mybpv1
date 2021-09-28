<?php

namespace App\Models;

use App\Scopes\ScopeEmpresa;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\EmpresaPerimetro
 *
 * @property-read \App\Models\User|null $Empresa
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Activitylog\Models\Activity[] $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaPerimetro newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaPerimetro newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaPerimetro query()
 * @mixin \Eloquent
 * @property int $id
 * @property int $empresa_id
 * @property string $descricao
 * @property float $lat
 * @property float $long
 * @property int $perimetro
 * @property \datetime|null $created_at
 * @property \datetime|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaPerimetro whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaPerimetro whereDescricao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaPerimetro whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaPerimetro whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaPerimetro whereLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaPerimetro whereLong($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaPerimetro wherePerimetro($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaPerimetro whereUpdatedAt($value)
 * @property bool $obrigatorio
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaPerimetro whereObrigatorio($value)
 */
class EmpresaPerimetro extends Model
{
    use HasFactory,LogsActivity;
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

    protected static function booted() {
        static::creating(function ($model) {
            $model->empresa_id = auth()->user()->empresa_id;
        });

        static::updating(function ($model) {
            $model->empresa_id = auth()->user()->empresa_id;
        });

        static::addGlobalScope(new ScopeEmpresa());
    }
}
