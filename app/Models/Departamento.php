<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Departamento
 *
 * @property int $id
 * @property string $label
 * @property int $cliente_id
 * @property bool $ativo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Cliente|null $Cliente
 * @method static \Illuminate\Database\Eloquent\Builder|Departamento newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Departamento newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Departamento query()
 * @method static \Illuminate\Database\Eloquent\Builder|Departamento whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Departamento whereClienteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Departamento whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Departamento whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Departamento whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Departamento whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Departamento extends Model
{
    use HasFactory;
    use TenantTrait;

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
