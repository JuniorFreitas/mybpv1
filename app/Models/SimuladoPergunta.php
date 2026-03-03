<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;

/**
 * App\Models\SimuladoPergunta
 *
 * @property int $id
 * @property int $simulado_id
 * @property string $enunciado
 * @property int|null $qnt_linhas
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SimuladoResposta> $Respostas
 * @property-read int|null $respostas_count
 * @property-read \App\Models\Simulado|null $Simulado
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|SimuladoPergunta newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SimuladoPergunta newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SimuladoPergunta query()
 * @method static \Illuminate\Database\Eloquent\Builder|SimuladoPergunta whereEnunciado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SimuladoPergunta whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SimuladoPergunta whereQntLinhas($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SimuladoPergunta whereSimuladoId($value)
 * @mixin \Eloquent
 */
class SimuladoPergunta extends Model
{
    use LogsActivity, HasActivitylogOptions;

    protected static $logFillable = true;
    protected static $logName = 'SimuladoPergunta';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = "";
    }

    protected $fillable = [
        'simulado_id',
        'enunciado',
        'solucao_tempo',
        'qnt_linhas'
    ];

    protected $casts = [
        'id' => 'int',
        'simulado_id' => 'int',
        'enunciado' => 'string',
        'solucao_tempo' => 'string',
        'qnt_linhas' => 'int',
    ];

    public $timestamps = false;

    public function Simulado()
    {
        return $this->hasOne(Simulado::class, 'id', 'simulado_id');
    }

    public function Respostas()
    {
        return $this->hasMany(SimuladoResposta::class, 'simulado_pergunta_id', 'id');
    }

//    public function SimuladoAlunoResposta()
//    {
//        return $this->hasOne(SimuladoAlunoResposta::class,'pergunta_id','id');
//    }
//
//    public function AlunoResposta()
//    {
//        return $this->hasOne(SimuladoAlunoResposta::class,'simulado_id','simulado_id');
//    }
}
