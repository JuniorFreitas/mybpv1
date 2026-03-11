<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\RecuperacaoSenha
 *
 * @property int $id
 * @property int $user_id
 * @property string $token
 * @property string $ip_solicitacao
 * @property \Illuminate\Support\Carbon $solicitacao
 * @property \Illuminate\Support\Carbon $expiracao
 * @property string|null $ip_recuperacao
 * @property \Illuminate\Support\Carbon|null $recuperacao
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
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @mixin \Eloquent
 */
class RecuperacaoSenha extends Model
{

    use LogsActivity, HasActivitylogOptions;

    protected static $logName = 'RecuperacaoSenha';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

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
