<?php

namespace App\Models;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;

/**
 * App\Models\RespostaOcorrencia
 *
 * @property int $id
 * @property int $ocorrencia_id
 * @property int $user_id
 * @property string $resposta
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $Anexos
 * @property-read int|null $anexos_count
 * @property-read \App\Models\Ocorrencia $Ocorrencia
 * @property-read User|null $Usuario
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|RespostaOcorrencia newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RespostaOcorrencia newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RespostaOcorrencia query()
 * @method static \Illuminate\Database\Eloquent\Builder|RespostaOcorrencia whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RespostaOcorrencia whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RespostaOcorrencia whereOcorrenciaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RespostaOcorrencia whereResposta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RespostaOcorrencia whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RespostaOcorrencia whereUserId($value)
 * @mixin \Eloquent
 */
class RespostaOcorrencia extends Model {
    use LogsActivity, HasActivitylogOptions;
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
