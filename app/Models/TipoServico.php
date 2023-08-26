<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\TipoServico
 *
 * @property int $id
 * @property string $label
 * @property bool $ativo
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|TipoServico newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TipoServico newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TipoServico query()
 * @method static \Illuminate\Database\Eloquent\Builder|TipoServico whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoServico whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoServico whereLabel($value)
 * @mixin \Eloquent
 */
class TipoServico extends Model
{
    use HasFactory, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'tipo_servico';
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

    protected $fillable = ['label', 'ativo'];
    protected $casts = ['id' => 'int', 'label' => 'string', 'ativo' => 'boolean'];

    public function usesTimestamps(): bool
    {
        return false;
    }
}
