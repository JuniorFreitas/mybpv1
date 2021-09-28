<?php

namespace App\Models;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\RespostaOcorrencia
 *
 * @property int $id
 * @property int $ocorrencia_id
 * @property int $user_id
 * @property string $resposta
 * @property mixed|null $created_at
 * @property mixed|null $updated_at
 * @property-read \App\Models\Ocorrencia $Ocorrencia
 * @property-read \App\Models\User $Usuario
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RespostaOcorrencia newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RespostaOcorrencia newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RespostaOcorrencia query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RespostaOcorrencia whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RespostaOcorrencia whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RespostaOcorrencia whereOcorrenciaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RespostaOcorrencia whereResposta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RespostaOcorrencia whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RespostaOcorrencia whereUserId($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $Anexos
 * @property-read int|null $anexos_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Activitylog\Models\Activity[] $activities
 * @property-read int|null $activities_count
 */
class RespostaOcorrencia extends Model {
    use LogsActivity;
    protected static $logFillable = true;
    protected static $logName = 'ocorrencias';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    public function getDescriptionForEvent(string $eventName): string {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName) {
        $activity->descricao = "";
    }

    protected $table = 'ocorrencias_respostas';
    protected $fillable = [
        'id',
        'ocorrencia_id',
        'user_id',
        'resposta',
        'created_at',
        'updated_at',
    ];
    protected $casts = [
        'id' => 'int',
        'ocorrencia_id' => 'int',
        'user_id' => 'int',
        'resposta' => 'string',
        'created_at' => 'datetime:d/m/Y à\s H:i',
        'updated_at' => 'datetime:d/m/Y à\s H:i',
    ];

    //Relacionamentos

    public function  Usuario(){
        return $this->hasOne(User::class,'id','user_id');
    }

    public function  Ocorrencia(){
        return $this->belongsTo(Ocorrencia::class,'ocorrencia_id','id');
    }

    public function Anexos()
    {
        return $this->belongsToMany(Arquivo::class, 'ocorrencias_anexos', 'resposta_id', 'arquivo_id');
    }


}
