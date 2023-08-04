<?php

namespace App\Models;

use App\Scopes\ScopeEmpresa;
use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Tag
 *
 * @property int $id
 * @property string $nome
 * @property int|null $empresa_id
 * @method static \Illuminate\Database\Eloquent\Builder|Tag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tag query()
 * @method static \Illuminate\Database\Eloquent\Builder|Tag whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tag whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tag whereNome($value)
 * @mixin \Eloquent
 */
class Tag extends Model
{
    use TenantTrait;

    protected $table='tags';
    protected $fillable = ['nome','empresa_id'];
    protected $casts = [
        'id' => 'int',
        'nome' => 'string',
        'empresa_id' => 'int'
    ];
    public $timestamps = false;

}
