<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\Vinculo
 *
 * @property int $id
 * @property int|null $feedback_id
 * @property int $vaga_id
 * @property bool $parente
 * @property string|null $nome
 * @property string|null $funcao
 * @property string|null $grau_parentesco
 * @property bool|null $foi_empregado
 * @property string|null $local_empregado
 * @property string|null $outra_empresa_parceira
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\FeedbackCurriculo|null $Feedback
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|Vinculo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Vinculo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Vinculo query()
 * @method static \Illuminate\Database\Eloquent\Builder|Vinculo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vinculo whereFeedbackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vinculo whereFoiEmpregado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vinculo whereFuncao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vinculo whereGrauParentesco($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vinculo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vinculo whereLocalEmpregado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vinculo whereNome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vinculo whereOutraEmpresaParceira($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vinculo whereParente($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vinculo whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vinculo whereVagaId($value)
 * @mixin \Eloquent
 */
class Vinculo extends Model
{
    use HasFactory, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'vinculo';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    public function getDescriptionForEvent(string $eventName): string {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = "";
    }

    protected $fillable = [
        'feedback_id',
        'vaga_id',
        'parente',
        'nome',
        'funcao',
        'grau_parentesco',
        'foi_empregado',
        'local_empregado',
        'outra_empresa_parceira',
    ];

    protected $casts = [
        'id' => 'int',
        'feedback_id' => 'int',
        'vaga_id' => 'int',
        'parente' => 'boolean',
        'nome' => 'string',
        'funcao' => 'string',
        'grau_parentesco' => 'string',
        'foi_empregado' => 'boolean',
        'local_empregado' => 'string',
        'outra_empresa_parceira' => 'string',
    ];

    public function getParenteAttribute($value)
    {
        return is_null($value) ? "" : (boolean)$value;
    }

    public function getFoiEmpregadoAttribute($value)
    {
        return is_null($value) ? "" : (boolean)$value;
    }

    public function Feedback()
    {
        return $this->hasOne(FeedbackCurriculo::class, 'id', 'feedback_id');
    }
}
