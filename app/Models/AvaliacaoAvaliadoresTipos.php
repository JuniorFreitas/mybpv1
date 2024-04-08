<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\AvaliacaoAvaliadoresTipos
 *
 * @property int $id
 * @property int $empresa_id
 * @property string $label
 * @property string|null $descricao
 * @property bool $ativo
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoAvaliadoresTipos newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoAvaliadoresTipos newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoAvaliadoresTipos query()
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoAvaliadoresTipos whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoAvaliadoresTipos whereDescricao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoAvaliadoresTipos whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoAvaliadoresTipos whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoAvaliadoresTipos whereLabel($value)
 * @mixin \Eloquent
 */
class AvaliacaoAvaliadoresTipos extends Model
{
    use TenantTrait, LogsActivity;

    protected static bool $logFillable = true;
    protected static string $logName = 'avaliacao_avaliadores_tipos';
    protected static bool $logOnlyDirty = true;
    protected static bool $submitEmptyLogs = false;
    public $timestamps = false;

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {
        $activity->descricao = "";
    }

    protected $table = 'avaliacao_avaliadores_tipos';

    protected $fillable = [
        'empresa_id',
        'label',
        'descricao',
        'ativo',
    ];

    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'label' => 'string',
        'descricao' => 'string',
        'ativo' => 'boolean',
    ];
}
