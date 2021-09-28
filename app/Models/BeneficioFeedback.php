<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;

/**
 * App\Models\BeneficioFeedback
 *
 * @property int $beneficio_id
 * @property int $feedback_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Beneficio $Beneficio
 * @property-read \App\Models\FeedbackCurriculo $Feedback
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BeneficioFeedback newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BeneficioFeedback newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BeneficioFeedback query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BeneficioFeedback whereBeneficioId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BeneficioFeedback whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BeneficioFeedback whereFeedbackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BeneficioFeedback whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class BeneficioFeedback extends Model
{
    protected $fillable = [
        'feedback_id',
        'beneficio_id'
    ];

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
