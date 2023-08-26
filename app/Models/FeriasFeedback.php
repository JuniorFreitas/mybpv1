<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\FeriasFeedback
 *
 * @property int $id
 * @property int $feedback_id
 * @property int $quem_cadastrou
 * @property int $ano
 * @property bool|null $comprada
 * @property int|null $dias_comprados
 * @property \Illuminate\Support\Carbon|null $data_inicio
 * @property \Illuminate\Support\Carbon|null $data_fim
 * @property float $valor
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\FeedbackCurriculo|null $Feedback
 * @property-read User|null $Usuario
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read mixed $valor_format
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasFeedback newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasFeedback newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasFeedback query()
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasFeedback whereAno($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasFeedback whereComprada($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasFeedback whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasFeedback whereDataFim($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasFeedback whereDataInicio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasFeedback whereDiasComprados($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasFeedback whereFeedbackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasFeedback whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasFeedback whereQuemCadastrou($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasFeedback whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasFeedback whereValor($value)
 * @mixin \Eloquent
 */
class FeriasFeedback extends Model
{

    use LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'area';
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
        'feedback_id',
        'quem_cadastrou',
        'ano',
        'comprada',
        'dias_comprados',
        'data_inicio',
        'data_fim',
        'valor',
    ];

    protected $casts = [
        'feedback_id' => 'int',
        'quem_cadastrou' => 'int',
        'ano'=> 'int',
        'comprada' => 'boolean',
        'dias_comprados'=> 'int',
        'data_inicio' => 'date:d/m/Y',
        'data_fim' => 'date:d/m/Y',
        'valor'=> 'float',
    ];

    protected $table = 'ferias_feedbacks';


    protected $appends = ['valor_format'];

    //Modificador ->valor_passagem
    public function setValorAttribute($value)
    {
        if ($value) {
            $this->attributes['valor'] = Sistema::DinheiroInsert($value);
        }
    }

    public function getValorFormatAttribute()
    {
        return number_format($this->attributes['valor'], 2, ',', '.');
    }

    //Acessor ->dataInicio
    public function getDataInicioAttribute($value)
    {
        $data = new DataHora($this->attributes['data_inicio']);
        return $data->dataCompleta();
    }

    //Modificador ->dataInicio
    public function setDataInicioAttribute($value)
    {
        $data = new DataHora($value);
        $this->attributes['data_inicio'] = $data->dataInsert();
    }

    //Acessor ->DataFim
    public function getDataFimAttribute($value)
    {
        $data = new DataHora($this->attributes['data_fim']);
        return $data->dataCompleta();
    }

    //Modificador ->DataFim
    public function setDataFimAttribute($value)
    {
        $data = new DataHora($value);
        $this->attributes['data_fim'] = $data->dataInsert();
    }

    public function Feedback()
    {
        return $this->hasOne(FeedbackCurriculo::class, 'id', 'feedback_id');
    }

    public function Usuario()
    {
        return $this->hasOne(User::class, 'id', 'quem_cadastrou');
    }

}
