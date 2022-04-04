<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\CurriculoQualificacao
 *
 * @property-read \App\Models\Curriculo|null $Curriculo
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|CurriculoQualificacao newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CurriculoQualificacao newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CurriculoQualificacao query()
 * @mixin \Eloquent
 * @property int $id
 * @property int $curriculo
 * @property string $nome
 * @property string $instituicao
 * @property string $mes_conclusao
 * @property mixed $ano_conclusao
 * @method static \Illuminate\Database\Eloquent\Builder|CurriculoQualificacao whereAnoConclusao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CurriculoQualificacao whereCurriculo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CurriculoQualificacao whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CurriculoQualificacao whereInstituicao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CurriculoQualificacao whereMesConclusao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CurriculoQualificacao whereNome($value)
 * @property int $curriculo_id
 * @method static \Illuminate\Database\Eloquent\Builder|CurriculoQualificacao whereCurriculoId($value)
 */
class CurriculoQualificacao extends Model
{
    use HasFactory,LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'curriculo_qualificacao';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    public function getDescriptionForEvent(string $eventName): string {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = "";
    }

    protected $table = 'curriculo_qualificacoes';
    public $timestamps = false;
    protected $fillable = [
        'nome',
        'curriculo_id',
        'instituicao',
        'mes_conclusao',
        'ano_conclusao',
    ];
    protected $casts = [
        'id' => 'int',
        'nome' => 'string',
        'curriculo_id' => 'int',
        'instituicao' => 'string',
        'mes_conclusao' => 'string',
        'ano_conclusao' => 'int',
    ];

    public function Curriculo()
    {
        return $this->hasOne(Curriculo::class,'id','curriculo');
    }
}
