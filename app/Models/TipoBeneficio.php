<?php

namespace App\Models;

use App\Scopes\ScopeClientesEmpresa;
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
 */
class TipoBeneficio extends Model
{
    protected $fillable = [
        'nome',
        'cliente_id',
        'ativo'
    ];

    protected $casts = [
        'nome' => 'string',
        'cliente_id' => 'int',
        'ativo' => 'boolean'
    ];

    protected $table = 'tipo_beneficios';

    public function Cliente()
    {
        return $this->hasOne(Cliente::class, 'id', 'cliente_id');
    }

    //Scopo de ClienteID (Empresa)
    protected static function booted()
    {
        static::addGlobalScope(new ScopeClientesEmpresa);
    }
}
