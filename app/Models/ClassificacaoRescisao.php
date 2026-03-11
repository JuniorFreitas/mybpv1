<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ClassificacaoRescisao
 *
 * @property int $id
 * @property string $classe
 * @property string $descricao
 * @property string $periodo
 * @property bool $ativo
 * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisao newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisao newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisao query()
 * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisao whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisao whereClasse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisao whereDescricao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisao whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisao wherePeriodo($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @mixin \Eloquent
 */
class ClassificacaoRescisao extends Model
{

    use LogsActivity, HasActivitylogOptions;

    protected static $logName = 'ClassificacaoRescisao';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

    protected $table = 'classificacao_rescisao';
    protected $fillable = [
        'classe',
        'descricao',
        'periodo',
        'ativo',
    ];
    protected $casts = [
        'id' => 'int',
        'classe' => 'string',
        'descricao' => 'string',
        'periodo' => 'string',
        'ativo' => 'boolean',
    ];
    public $timestamps = false;
}
