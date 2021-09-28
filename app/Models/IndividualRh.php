<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\IndividualRh
 *
 * @property int $id
 * @property int $feedback_id
 * @property int|null $formulario_id
 * @property int $curriculo_id
 * @property string|null $parecer
 * @property int|null $nota
 * @property string|null $entrevistado_por
 * @property int|null $user_id
 * @property string|null $comentario
 * @property string|null $avaliacao_psicologica
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Curriculo|null $Curriculo
 * @property-read \App\Models\FeedbackCurriculo|null $Feedback
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|IndividualRh newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IndividualRh newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IndividualRh query()
 * @method static \Illuminate\Database\Eloquent\Builder|IndividualRh whereAvaliacaoPsicologica($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IndividualRh whereComentario($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IndividualRh whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IndividualRh whereCurriculoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IndividualRh whereEntrevistadoPor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IndividualRh whereFeedbackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IndividualRh whereFormularioId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IndividualRh whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IndividualRh whereNota($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IndividualRh whereParecer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IndividualRh whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IndividualRh whereUserId($value)
 * @mixin \Eloquent
 */
class IndividualRh extends Model
{
    use HasFactory, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'individual_rh';
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
        'parecer',
        'nota',
        'entrevistado_por',
        'user_id',
        'comentario',
        'avaliacao_psicologica'
    ];
    protected $casts = [
        'id' => 'int',
        'feedback_id' => 'int',
        'formulario_id' => 'int',
        'parecer' => 'string',
        'nota' => 'int',
        'entrevistado_por' => 'string',
        'user_id' => 'int',
        'comentario' => 'string',
        'avaliacao_psicologica' => 'string'
    ];

    public function Feedback()
    {
        return $this->hasOne(FeedbackCurriculo::class, 'id', 'feedback_id');
    }

    public function Curriculo()
    {
        return $this->hasOne(Curriculo::class, 'id', 'curriculo_id');
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
