<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\EmpresaDispositivos
 *
 * @property-read \App\Models\User|null $Empresa
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Activitylog\Models\Activity[] $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaDispositivos newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaDispositivos newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaDispositivos query()
 * @mixin \Eloquent
 * @property int $id
 * @property int $empresa_id
 * @property string $descricao
 * @property float $lat
 * @property float $long
 * @property int $perimetro
 * @property \datetime|null $created_at
 * @property \datetime|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaDispositivos whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaDispositivos whereDescricao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaDispositivos whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaDispositivos whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaDispositivos whereLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaDispositivos whereLong($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaDispositivos wherePerimetro($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaDispositivos whereUpdatedAt($value)
 * @property int $obrigatorio
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaDispositivos whereObrigatorio($value)
 * @property string|null $deleted_at
 * @property int|null $user_deletou_id
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaDispositivos whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaDispositivos whereUserDeletouId($value)
 */
class EmpresaDispositivos extends Model
{
    use HasFactory,LogsActivity;
    protected static $logFillable = true;
    protected static $logName = 'EmpresaDispositivos';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;
    public $timestamps=true;
    protected $table = 'empresa_perimetros';
    protected $fillable = [
        'empresa_id' ,
        'descricao' ,
        'lat' ,
        'long' ,
        'distancia' ,
        'PIN' ,
    ];
    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'descricao' => 'string',
        'lat' => 'float',
        'long' => 'float',
        'distancia' => 'int',
        'PIN' => 'int',
        'created_at' => 'datetime:d/m/Y à\s H:i:s',
        'updated_at' => 'datetime:d/m/Y à\s H:i:s',
    ];

    protected function serializeDate(DateTimeInterface $date) {
        return $date->format('Y-m-d H:i:s');
    }

    public function Empresa(){
        return $this->hasOne(User::class,'id','empresa_id');
    }
}
