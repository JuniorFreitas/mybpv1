<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\UsuarioConta
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $banco
 * @property string|null $agencia
 * @property string|null $conta
 * @property bool $pix
 * @property string|null $tipochavepix
 * @property string|null $chavepix
 * @method static \Illuminate\Database\Eloquent\Builder|UsuarioConta newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UsuarioConta newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UsuarioConta query()
 * @method static \Illuminate\Database\Eloquent\Builder|UsuarioConta whereAgencia($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsuarioConta whereBanco($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsuarioConta whereChavepix($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsuarioConta whereConta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsuarioConta whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsuarioConta wherePix($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsuarioConta whereTipochavepix($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsuarioConta whereUserId($value)
 * @mixin \Eloquent
 */
class UsuarioConta extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'banco',
        'agencia',
        'conta',
        'pix',
        'tipochavepix',
        'chavepix',
    ];

    protected $casts = [
        'id' => 'int',
        'user_id' => 'int',
        'banco' => 'string',
        'agencia' => 'string',
        'conta' => 'string',
        'pix' => 'boolean',
        'tipochavepix' => 'string',
        'chavepix' => 'string',
    ];

    public static function criarAtualizar($id,$array)
    {
        $usuarioConta = UsuarioConta::whereUserId($id);
        if ($usuarioConta->count() == 0) {
            $usuarioConta->create($array);
        }else{
            $usuarioConta->update($array);
        }
    }
}
