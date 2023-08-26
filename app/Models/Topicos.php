<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\Topicos
 *
 * @property int $id
 * @property string $nome
 * @property bool $ativo
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FormularioAvaliacaoAnual> $Perguntas
 * @property-read int|null $perguntas_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|Topicos newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Topicos newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Topicos query()
 * @method static \Illuminate\Database\Eloquent\Builder|Topicos whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Topicos whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Topicos whereNome($value)
 * @mixin \Eloquent
 */
class Topicos extends Model
{
    use LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'topicos';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    public function getDescriptionForEvent(string $eventName): string {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = "";
    }

    protected $fillable = ['nome', 'ativo'];

    protected $casts = [
        'nome' => 'string',
        'ativo' => 'boolean'
    ];

    protected $table = 'topicos';

    public $timestamps = false;

    public function Perguntas()
    {
        return $this->hasMany(FormularioAvaliacaoAnual::class,'topicos_id','id');
    }
}
