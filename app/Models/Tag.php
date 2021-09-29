<?php

namespace App\Models;

use App\Scopes\ScopeEmpresa;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Tag
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Tag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Tag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Tag query()
 * @mixin \Eloquent
 * @property int $id
 * @property string $nome
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Tag whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Tag whereNome($value)
 */
class Tag extends Model
{
    protected $table='tags';
    protected $fillable = ['nome','empresa_id'];
    protected $casts = [
        'id' => 'int',
        'nome' => 'string',
        'empresa_id' => 'int'
    ];
    public $timestamps = false;


    //Scopo de ClienteID (Empresa)
    protected static function booted()
    {
        static::addGlobalScope(new ScopeEmpresa);
    }
}
