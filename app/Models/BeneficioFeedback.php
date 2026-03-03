<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;

/**
 * App\Models\BeneficioFeedback
 *
 * @property int $beneficio_id
 * @property int $feedback_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Beneficio|null $Beneficio
 * @property-read \App\Models\FeedbackCurriculo|null $Feedback
 * @method static \Illuminate\Database\Eloquent\Builder|BeneficioFeedback newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BeneficioFeedback newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BeneficioFeedback query()
 * @method static \Illuminate\Database\Eloquent\Builder|BeneficioFeedback whereBeneficioId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeneficioFeedback whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeneficioFeedback whereFeedbackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeneficioFeedback whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class BeneficioFeedback extends Model
{

    use LogsActivity, HasActivitylogOptions;

    protected static $logName = 'BeneficioFeedback';
    protected $fillable = [
        'feedback_id',
        'beneficio_id'
    ];

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

    protected $table = 'beneficio_feedbacks';

    protected $casts = [
        'feedback_id' => 'int',
        'beneficio_id' => 'int'
    ];

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
        return $this->hasOne(FeedbackCurriculo::class,'id','feedback_id');
    }

    public function Beneficio()
    {
        return $this->hasOne(Beneficio::class,'id','beneficio_id');
    }
}
