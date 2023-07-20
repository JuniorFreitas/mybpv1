<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\EntrevistaRh
 *
 * @property int $id
 * @property int|null $feedback_id
 * @property string|null $parecer
 * @property string|null $indicado_para
 * @property int|null $nota
 * @property string|null $entrevistado_por
 * @property int|null $user_id
 * @property string|null $comentario
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Curriculo|null $Curriculo
 * @property-read \App\Models\FeedbackCurriculo|null $Feedback
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaRh newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaRh newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaRh query()
 * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaRh whereComentario($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaRh whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaRh whereEntrevistadoPor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaRh whereFeedbackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaRh whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaRh whereIndicadoPara($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaRh whereNota($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaRh whereParecer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaRh whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaRh whereUserId($value)
 * @mixin \Eloquent
 */
class EntrevistaRh extends Model
{
    use HasFactory, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'entrevista_rh';
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
        'formulario_id',
        'curriculo_id',
        'parecer',
        'indicado_para',
        'nota',
        'entrevistado_por',
        'user_id',
        'comentario'
    ];
    protected $casts = [
        'feedback_id' => 'int',
        'formulario_id' => 'int',
        'curriculo_id' => 'int',
        'parecer' => 'string',
        'indicado_para' => 'string',
        'nota' => 'int',
        'entrevistado_por' => 'string',
        'user_id' => 'int',
        'comentario' => 'string'
    ];

    public function Feedback()
    {
        return $this->hasOne(FeedbackCurriculo::class, 'id','feedback_id');
    }

    public function Curriculo()
    {
        return $this->hasOne(Curriculo::class, 'id','curriculo_id');
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->user_id = auth()->id();
        });

        static::updating(function ($model) {
            $model->user_id = auth()->id();
        });
    }
}
