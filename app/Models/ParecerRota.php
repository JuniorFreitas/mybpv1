<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\ParecerRota
 *
 * @property int $id
 * @property int|null $feedback_id
 * @property bool|null $tem_rota
 * @property string|null $qual
 * @property string|null $bairro_rota
 * @property string|null $ponto_referencia_rota
 * @property string|null $bairro_residencia
 * @property string|null $ponto_referencia_residencia
 * @property bool|null $pega_onibus
 * @property string|null $pega_onibus_qual_ponto
 * @property bool|null $vale_transporte
 * @property bool|null $rota_disponivel_turno_a
 * @property bool|null $rota_disponivel_turno_b
 * @property bool|null $rota_disponivel_turno_c
 * @property bool|null $rota_disponivel_turno_o
 * @property string|null $rota_disponivel_outros
 * @property bool|null $rota_atende
 * @property string|null $rota_tipo
 * @property int|null $aprovado_por
 * @property string|null $quem_entrevistou
 * @property string|null $observacao
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $formulario_id
 * @property-read \App\Models\FeedbackCurriculo|null $FeedbackCurriculo
 * @property-read \App\Models\User|null $QuemAprovou
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @property-read mixed $data_entrevista
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota query()
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota whereAprovadoPor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota whereBairroResidencia($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota whereBairroRota($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota whereFeedbackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota whereFormularioId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota whereObservacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota wherePegaOnibus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota wherePegaOnibusQualPonto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota wherePontoReferenciaResidencia($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota wherePontoReferenciaRota($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota whereQual($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota whereQuemEntrevistou($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota whereRotaAtende($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota whereRotaDisponivelOutros($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota whereRotaDisponivelTurnoA($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota whereRotaDisponivelTurnoB($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota whereRotaDisponivelTurnoC($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota whereRotaDisponivelTurnoO($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota whereRotaTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota whereTemRota($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota whereValeTransporte($value)
 * @mixin \Eloquent
 */
class ParecerRota extends Model
{
    use HasFactory, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'parecer_rota';
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

    protected $table = 'parecer_rotas';
    protected $fillable = [
        'feedback_id',
        'formulario_id',
        'tem_rota',
        'qual',
        'pega_onibus',
        'pega_onibus_qual_ponto',
        'vale_transporte',
        'rota_disponivel_turno_a',
        'rota_disponivel_turno_b',
        'rota_disponivel_turno_c',
        'rota_disponivel_turno_o',
        'rota_disponivel_outros',
        'rota_atende',
        'rota_tipo',
        'aprovado_por',
        'quem_entrevistou',
        'bairro_rota',
        'ponto_referencia_rota',
        'bairro_residencia',
        'ponto_referencia_residencia',
        'observacao',
    ];

    protected $casts = [
        'feedback_id' => 'int',
        'formulario_id' => 'int',
        'tem_rota' => 'boolean',
        'qual' => 'string',
        'pega_onibus' => 'boolean',
        'pega_onibus_qual_ponto' => 'string',
        'vale_transporte' => 'boolean',
        'rota_disponivel_turno_a' => 'boolean',
        'rota_disponivel_turno_b' => 'boolean',
        'rota_disponivel_turno_c' => 'boolean',
        'rota_disponivel_turno_o' => 'boolean',
        'rota_disponivel_outros' => 'string',
        'rota_atende' => 'boolean',
        'rota_tipo' => 'string',
        'aprovado_por' => 'int',
        'quem_entrevistou' => 'string',
        'bairro_rota' => 'string',
        'ponto_referencia_rota' => 'string',
        'bairro_residencia' => 'string',
        'ponto_referencia_residencia' => 'string',
        'observacao' => 'string',
    ];

    //Acessor ->datalido
    public function getDataEntrevistaAttribute($value)
    {
        $data = new DataHora($this->attributes['created_at']);
        return $data->dataCompleta() . ' ' . $data->hora() . ':' . $data->minuto() . 'h';
    }

//    protected $appends = ['TemRotaFormat'];
//
//    public function getTemRotaFormatAttribute($value)
//    {
//        return $this->attributes['tem_rota'] == true ? 'Sim' : 'Não';
//    }

    public function FeedbackCurriculo()
    {
        return $this->hasOne(FeedbackCurriculo::class, 'id', 'feedback_id');
    }


    public function QuemAprovou()
    {
        return $this->hasOne(User::class, 'id', 'aprovado_por');
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->aprovado_por = auth()->id();
        });

        static::updating(function ($model) {
            $model->aprovado_por = auth()->id();
        });
    }
}
