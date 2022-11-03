<?php

namespace App\Models;

use App\Scopes\ScopeClientesEmpresa;
use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\CentroCusto
 *
 * @property int $id
 * @property int $cliente_id
 * @property string $label
 * @property bool $ativo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Cliente|null $Cliente
 * @method static \Illuminate\Database\Eloquent\Builder|CentroCusto newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CentroCusto newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CentroCusto query()
 * @method static \Illuminate\Database\Eloquent\Builder|CentroCusto whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CentroCusto whereClienteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CentroCusto whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CentroCusto whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CentroCusto whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CentroCusto whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int|null $empresa_id
 * @property-read \App\Models\User|null $Empresa
 * @method static \Illuminate\Database\Eloquent\Builder|CentroCusto whereEmpresaId($value)
 * @property-read \App\Models\User|null $Gestor
 * @property int|null $gestor_id
 * @method static \Illuminate\Database\Eloquent\Builder|CentroCusto whereGestorId($value)
 */
class CentroCusto extends Model
{
    use HasFactory, TenantTrait;

    protected $fillable = ['gestor_id', 'label', 'empresa_id', 'ativo'];
    protected $casts = ['id' => 'int', 'gestor_id' => 'int', 'label' => 'string', 'empresa_id' => 'int', 'ativo' => 'boolean'];

    public function Empresa()
    {
        return $this->hasOne(User::class, 'id', 'empresa_id');
    }

    public function Gestor()
    {
        return $this->hasOne(User::class, 'id', 'gestor_id')->select(['id', 'nome', 'login']);
    }

    public function Admissao()
    {
        return $this->hasMany(Admissao::class, 'centro_custo_id', 'id');
    }

}
