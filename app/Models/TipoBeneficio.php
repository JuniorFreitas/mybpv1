<?php

namespace App\Models;

use App\Scopes\ScopeClientesEmpresa;
use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\TipoBeneficio
 *
 * @property int $id
 * @property string $nome
 * @property int $cliente_id
 * @property bool $ativo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Cliente|null $Cliente
 * @method static \Illuminate\Database\Eloquent\Builder|TipoBeneficio newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TipoBeneficio newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TipoBeneficio query()
 * @method static \Illuminate\Database\Eloquent\Builder|TipoBeneficio whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoBeneficio whereClienteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoBeneficio whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoBeneficio whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoBeneficio whereNome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoBeneficio whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int|null $empresa_id
 * @property-read \App\Models\User|null $Empresa
 * @method static \Illuminate\Database\Eloquent\Builder|TipoBeneficio whereEmpresaId($value)
 */
class TipoBeneficio extends Model
{
    use TenantTrait;

    protected $fillable = [
        'nome',
        'empresa_id',
        'ativo'
    ];

    protected $casts = [
        'nome' => 'string',
        'empresa_id' => 'int',
        'ativo' => 'boolean'
    ];

    protected $table = 'tipo_beneficios';

    public function Empresa()
    {
        return $this->hasOne(User::class, 'id', 'empresa_id');
    }

//    //Scopo de ClienteID (Empresa)
//    protected static function booted()
//    {
//        static::addGlobalScope(new ScopeClientesEmpresa);
//    }
}
