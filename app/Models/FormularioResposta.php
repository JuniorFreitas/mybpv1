<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\FormularioResposta
 *
 * @property int $id
 * @property int $formulario_id
 * @property int $user_id
 * @property array $respostas
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Formulario|null $Formulario
 * @property-read \App\Models\User|null $Usuario
 * @method static \Illuminate\Database\Eloquent\Builder|FormularioResposta newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FormularioResposta newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FormularioResposta query()
 * @method static \Illuminate\Database\Eloquent\Builder|FormularioResposta whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormularioResposta whereFormularioId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormularioResposta whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormularioResposta whereRespostas($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormularioResposta whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormularioResposta whereUserId($value)
 * @mixin \Eloquent
 */
class FormularioResposta extends Model
{
    use LogsActivity, HasActivitylogOptions, HasFactory;

    protected static $logName = 'FormularioResposta';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

    protected $fillable = [
        'user_id',
        'formulario_id',
        'respostas',
    ];
    protected $casts = [
        'id' => 'int',
        'user_id' => 'int',
        'formulario_id' => 'int',
        'respostas' => 'array',
    ];

    public function Formulario(){
        return $this->hasOne(Formulario::class, 'id','formulario_id');
    }

    public function Usuario(){
        return $this->hasOne(User::class, 'id','user_id');
    }
}
