<?php

namespace App\Models;

use App\Scopes\ScopeClientesEmpresa;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PesquisaClimaCliente
 *
 * @property int $tipo_id
 * @property int $cliente_id
 * @property-read \App\Models\Clientes|null $Cliente
 * @property-read \App\Models\PesquisaClimaTipo|null $Tipo
 * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaCliente newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaCliente newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaCliente query()
 * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaCliente whereClienteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaCliente whereTipoId($value)
 * @mixin \Eloquent
 */
class PesquisaClimaCliente extends Model
{
    protected $fillable = [
        'tipo_id',
        'cliente_id'
    ];

    public $timestamps = false;

    protected $casts = [
        'tipo_id' => 'int',
        'cliente_id' => 'int'
    ];

    public function Tipo()
    {
        return $this->hasOne(PesquisaClimaTipo::class, 'id', 'tipo_id');
    }

    public function Cliente()
    {
        return $this->hasOne(Cliente::class, 'id', 'cliente_id');
    }

    protected static function booted()
    {
        static::addGlobalScope(new ScopeClientesEmpresa);
    }

}
