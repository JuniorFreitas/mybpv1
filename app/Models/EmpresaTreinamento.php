<?php

namespace App\Models;

use App\Scopes\ScopeEmpresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\EmpresaTreinamento
 *
 * @property int $id
 * @property string $nome
 * @property string $endereco
 * @property int $ativo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaTreinamento newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaTreinamento newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaTreinamento query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaTreinamento whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaTreinamento whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaTreinamento whereEndereco($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaTreinamento whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaTreinamento whereNome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaTreinamento whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class EmpresaTreinamento extends Model
{
    use HasFactory;

    protected $table = 'empresa_treinamentos';
    protected $fillable = [
        'id',
        'nome',
        'endereco',
        'ativo',
        'empresa_id'
    ];

    protected $casts = [
        'id' => 'int',
        'nome' => 'string',
        'endereco' => 'string',
        'ativo' => 'boolean',
        'empresa_id' => 'int'
    ];


    //Scopo de ClienteID (Empresa)
    protected static function booted()
    {
        static::addGlobalScope(new ScopeEmpresa);
    }
}
