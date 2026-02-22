<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\AvaliacaoNoventaFeedbackQuantidade
 *
 * @property int $id
 * @property int $feedback_id
 * @property int $quantidade_avaliacao
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\FeedbackCurriculo|null $Feedback
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property mixed $data_admissao
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaFeedbackQuantidade newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaFeedbackQuantidade newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaFeedbackQuantidade query()
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaFeedbackQuantidade whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaFeedbackQuantidade whereFeedbackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaFeedbackQuantidade whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaFeedbackQuantidade whereQuantidadeAvaliacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaFeedbackQuantidade whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class AvaliacaoNoventaFeedbackQuantidade extends Model
{

    use LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'AvaliacaoNoventaFeedbackQuantidade';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    public function getDescriptionForEvent(string $eventName)
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = "";
    }


    protected $fillable = [
        'id',
        'feedback_id',
        'quantidade_avaliacao',
        'definicao_contrato',
    ];

    protected $casts = [
        'feedback_id' => 'int',
        'quantidade_avaliacao' => 'int',
    ];

    protected $table = 'avaliacao_noventa_feedback_quantidades';

    //Acessor ->created_at
    public function getDataAdmissaoAttribute($value)
    {
        if ($value) {
            $data = new DataHora($this->attributes['data_admissao']);
            return $data->dataCompleta();
        }
    }

    //Modificador ->created_at
    public function setDataAdmissaoAttribute($value)
    {
        if ($value) {
            $data = new DataHora($value);
            $this->attributes['data_admissao'] = $data->dataInsert();
        }
    }
    //Acessor ->created_at
    public function getCreatedAtAttribute($value)
    {
        if ($value) {
            $data = new DataHora($this->attributes['created_at']);
            return $data->dataCompleta();
        }
    }

    //Modificador ->created_at
    public function setCreatedAtAttribute($value)
    {
        if ($value) {
            $data = new DataHora($value);
            $this->attributes['created_at'] = $data->dataInsert();
        }
    }

    public function Feedback()
    {
        return $this->hasOne(FeedbackCurriculo::class, 'id', 'feedback_id');
    }

}
