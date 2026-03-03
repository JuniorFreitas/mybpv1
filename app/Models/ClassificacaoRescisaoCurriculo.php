<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ClassificacaoRescisaoCurriculo
 *
 * @property int $classificacao_id
 * @property int|null $feedback_id
 * @property string|null $observacoes
 * @property string|null $quem_classificou
 * @property \Illuminate\Support\Carbon|null $data_afastamento
 * @property string|null $preenchido_por
 * @property int|null $user_id
 * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisaoCurriculo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisaoCurriculo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisaoCurriculo query()
 * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisaoCurriculo whereClassificacaoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisaoCurriculo whereDataAfastamento($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisaoCurriculo whereFeedbackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisaoCurriculo whereObservacoes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisaoCurriculo wherePreenchidoPor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisaoCurriculo whereQuemClassificou($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisaoCurriculo whereUserId($value)
 * @mixin \Eloquent
 */
class ClassificacaoRescisaoCurriculo extends Model
{
    use LogsActivity, HasActivitylogOptions, HasFactory;

    protected static $logName = 'ClassificacaoRescisaoCurriculo';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

    protected $table = 'classificacao_rescisao_curriculo';
    public $timestamps = false;
    protected $primaryKey = 'curriculo_id';

    protected $fillable = [
        'classificacao_id',
        'curriculo_id',
        'observacoes',
        'quem_classificou',
        'data_afastamento',
        'preenchido_por',
        'user_id'
    ];

    protected $casts = [
        'classificacao_id' => 'int',
        'curriculo_id' => 'int',
        'observacoes' => 'string',
        'quem_classificou' => 'string',
        'data_afastamento' => 'date:d/m/Y',
        'preenchido_por' => 'string',
        'user_id' => 'int',
    ];
}
