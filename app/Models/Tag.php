<?php

namespace App\Models;

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
    protected $fillable = ['nome'];
    protected $casts = [
        'id' => 'int',
        'nome' => 'string',
    ];
    public $timestamps = false;
}
