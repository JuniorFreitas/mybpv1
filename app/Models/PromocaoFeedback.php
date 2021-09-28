<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\PromocaoFeedback
 *
 * @property int $id
 * @property int $feedback_id
 * @property string $novo_cargo
 * @property float $novo_salario
 * @property string $motivo
 * @property float $percentual
 * @property string $tipo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $novo_salario_format
 * @property-read mixed $tipo_text
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PromocaoFeedback newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PromocaoFeedback newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PromocaoFeedback query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PromocaoFeedback whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PromocaoFeedback whereFeedbackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PromocaoFeedback whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PromocaoFeedback whereMotivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PromocaoFeedback whereNovoCargo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PromocaoFeedback whereNovoSalario($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PromocaoFeedback wherePercentual($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PromocaoFeedback whereTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PromocaoFeedback whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 */
class PromocaoFeedback extends Model
{

    use LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'ocorrencias';
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
        'novo_cargo',
        'novo_salario',
        'motivo',
        'percentual',
        'tipo',
    ];

    protected $casts = [
        'feedback_id' => 'int',
        'novo_cargo' => 'string',
        'novo_salario' => 'float',
        'motivo' => 'string',
        'percentual' => 'float',
        'tipo' => 'string',
    ];

    protected $table = 'promocao_feedbacks';

    protected $appends = ['novo_salario_format'];

    //Modificador ->valor
    public function setNovoSalarioAttribute($value)
    {
        if ($value) {
            $this->attributes['novo_salario'] = Sistema::DinheiroInsert($value);
        }
    }

    public function getNovoSalarioFormatAttribute()
    {
        return number_format($this->attributes['novo_salario'], 2, ',', '.');
    }

    const PROMOCAO = 'promocao';
    const REAJUSTE = 'reajuste';
    const ACORDOCOLETIVO = 'acordocoletivo';
    const MERITO = 'merito';

    public function getTipoTextAttribute()
    {
        switch ($this->tipo) {
            case self::PROMOCAO:
                return "Promoção";
                break;

            case self::REAJUSTE:
                return "Reajuste";
                break;

            case self::ACORDOCOLETIVO:
                return "Acordo Coletivo";
                break;

            case self::MERITO:
                return "Mérito";
                break;
        }
    }
}
