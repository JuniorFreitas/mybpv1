<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\RecuperacaoSenha
 *
 * @property int $id
 * @property int $user_id
 * @property string $token
 * @property string $ip_solicitacao
 * @property mixed $solicitacao
 * @property mixed $expiracao
 * @property string|null $ip_recuperacao
 * @property mixed|null $recuperacao
 * @property bool $recuperado
 * @property-read \App\Models\User|null $User
 * @method static \Illuminate\Database\Eloquent\Builder|RecuperacaoSenha newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecuperacaoSenha newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecuperacaoSenha query()
 * @method static \Illuminate\Database\Eloquent\Builder|RecuperacaoSenha whereExpiracao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecuperacaoSenha whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecuperacaoSenha whereIpRecuperacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecuperacaoSenha whereIpSolicitacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecuperacaoSenha whereRecuperacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecuperacaoSenha whereRecuperado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecuperacaoSenha whereSolicitacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecuperacaoSenha whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecuperacaoSenha whereUserId($value)
 * @mixin \Eloquent
 */
class RecuperacaoSenha extends Model
{

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'token',
        'ip_solicitacao',
        'solicitacao',
        'expiracao',
        'ip_recuperacao',
        'recuperacao',
        'recuperado',
    ];

    protected $casts = [
        'user_id' => 'int',
        'token' => 'string',
        'ip_solicitacao' => 'string',
        'solicitacao' => 'date:d/m/Y H:i',
        'expiracao' => 'date:d/m/Y H:i',
        'ip_recuperacao' => 'string',
        'recuperacao' => 'date:d/m/Y H:i',
        'recuperado' => 'boolean'
    ];



    public function User()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

}
