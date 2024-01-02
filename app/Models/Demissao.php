<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\Demissao
 *
 * @property int $id
 * @property int $feedback_id
 * @property bool $cipa
 * @property Carbon $data_desmobilizacao
 * @property int $motivo_rescisao_id
 * @property string|null $outro_motivo
 * @property int $tipo_aviso_id
 * @property string $solicitado_por
 * @property string|null $comentario
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read FeedbackCurriculo $Feedback
 * @property-read User $User
 * @property-read Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read MotivoRescisao $motivoRescisao
 * @property-read TipoAviso $tipoAviso
 * @method static \Illuminate\Database\Eloquent\Builder|Demissao newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Demissao newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Demissao query()
 * @method static \Illuminate\Database\Eloquent\Builder|Demissao whereCipa($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Demissao whereComentario($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Demissao whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Demissao whereDataDesmobilizacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Demissao whereFeedbackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Demissao whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Demissao whereMotivoRescisaoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Demissao whereOutroMotivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Demissao whereSolicitadoPor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Demissao whereTipoAvisoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Demissao whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Demissao whereUserId($value)
 * @mixin \Eloquent
 */
class Demissao extends Model
{
    use LogsActivity;

    protected static bool $logFillable = true;
    protected static string $logName = 'demissão';
    protected static bool $logOnlyDirty = true;
    protected static bool $submitEmptyLogs = false;

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {
        $activity->descricao = "";
    }

    protected $fillable = [
        'feedback_id',
        'cipa',
        'data_desmobilizacao',
        'motivo_rescisao_id',
        'tipo_aviso_id',
        'solicitado_por',
        'comentario',
        'user_id',
    ];

    protected $casts = [
        'id' => 'int',
        'feedback_id' => 'int',
        'cipa' => 'boolean',
        'data_desmobilizacao' => 'date:d/m/Y',
        'motivo_rescisao_id' => 'int',
        'tipo_aviso_id' => 'int',
        'solicitado_por' => 'string',
        'comentario' => 'string',
        'user_id' => 'int',
    ];


    public function setDataDesmobilizacaoAttribute($value): void
    {
        if (!is_null($value)) {
            $data = new DataHora($value);
            $this->attributes['data_desmobilizacao'] = $data->dataInsert();
        } else {
            $this->attributes['data_desmobilizacao'] = null;
        }
    }

    public function Feedback(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(FeedbackCurriculo::class, 'feedback_id');
    }

    public function motivoRescisao(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(MotivoRescisao::class, 'motivo_rescisao_id');
    }

    public function tipoAviso(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(TipoAviso::class, 'tipo_aviso_id');
    }

    public function User(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            $model->user_id = auth()->id();
        });

        static::updating(function ($model) {
            $model->user_id = auth()->id();
        });
    }
}
