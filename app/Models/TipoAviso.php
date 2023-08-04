<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\TipoAviso
 *
 * @property int $id
 * @property string $descricao
 * @property bool $ativo
 * @method static \Illuminate\Database\Eloquent\Builder|TipoAviso newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TipoAviso newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TipoAviso query()
 * @method static \Illuminate\Database\Eloquent\Builder|TipoAviso whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoAviso whereDescricao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoAviso whereId($value)
 * @mixin \Eloquent
 */
class TipoAviso extends Model
{
    use HasFactory;

    protected $table = 'tipo_aviso';
    protected $fillable = [
        'descricao',
        'ativo',
    ];
    protected $casts = [
        'id' => 'int',
        'descricao' => 'string',
        'ativo' => 'boolean',
    ];
    public $timestamps = false;
}
