<?php

namespace App\Models;

use App\Scopes\ScopeEmpresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Models\EmpresaExame
 *
 * @property int $id
 * @property int|null $empresa_id
 * @property string $nome
 * @property mixed $dados
 * @property int $ativo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaExame newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaExame newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaExame query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaExame whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaExame whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaExame whereDados($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaExame whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaExame whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaExame whereNome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaExame whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \App\Models\User|null $Empresa
 */
class EmpresaExame extends Model
{
    use HasFactory;

    protected $fillable = [
        "empresa_id",
        "nome",
        "dados",
        "ativo",
    ];

    protected $casts = [
        "id" => 'int',
        "empresa_id" => 'int',
        "nome" => 'string',
        "dados" => 'array',
        "ativo" => 'boolean',
    ];

    public function Empresa()
    {
        return $this->hasOne(User::class, 'id', 'empresa_id');
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->empresa_id = auth()->user()->empresa_id;
        });

        static::updating(function ($model) {
            $model->empresa_id = auth()->user()->empresa_id;
        });

        static::addGlobalScope(new ScopeEmpresa());
    }
}
