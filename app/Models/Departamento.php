<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Departamento
 *
 * @property int $id
 * @property string $label
 * @property int|null $cliente_id
 * @property bool $ativo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $empresa_id
 * @property-read \App\Models\User|null $Empresa
 * @method static \Illuminate\Database\Eloquent\Builder|Departamento newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Departamento newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Departamento query()
 * @method static \Illuminate\Database\Eloquent\Builder|Departamento whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Departamento whereClienteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Departamento whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Departamento whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Departamento whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Departamento whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Departamento whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Departamento extends Model
{
    use HasFactory, TenantTrait, LogsActivity, HasActivitylogOptions;



    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }


    protected $table = 'departamentos';

    protected $fillable = [
        'label',
        'empresa_id',
        'ativo'
    ];

    protected $casts = [
        'label' => 'string',
        'empresa_id' => 'int',
        'ativo' => 'boolean'
    ];

    public function Empresa()
    {
        return $this->hasOne(User::class, 'id', 'empresa_id');
    }
}
