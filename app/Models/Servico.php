<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\Servico
 *
 * @property int $id
 * @property string $titulo
 * @property bool $ativo
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|Servico newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Servico newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Servico query()
 * @method static \Illuminate\Database\Eloquent\Builder|Servico whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Servico whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Servico whereTitulo($value)
 * @mixin \Eloquent
 * @property int|null $empresa_id
 * @method static \Illuminate\Database\Eloquent\Builder|Servico whereEmpresaId($value)
 */
class Servico extends Model
{
    use HasFactory, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'servico';
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

    public function usesTimestamps(): bool
    {
        return false;
    }

    protected $fillable = ['titulo', 'tipo_servico_id', 'ativo'];
    protected $casts = ['id' => 'int', 'titulo' => 'string', 'ativo' => 'boolean'];
}
