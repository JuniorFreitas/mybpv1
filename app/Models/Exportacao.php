<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\Exportacao
 *
 * @property int $id
 * @property int $user_id
 * @property string $arquivo
 * @property string $local
 * @property bool $removido
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @property-read mixed $data_hora_criacao
 * @method static \Illuminate\Database\Eloquent\Builder|Exportacao newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Exportacao newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Exportacao query()
 * @method static \Illuminate\Database\Eloquent\Builder|Exportacao whereArquivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Exportacao whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Exportacao whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Exportacao whereLocal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Exportacao whereRemovido($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Exportacao whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Exportacao whereUserId($value)
 * @mixin \Eloquent
 */
class Exportacao extends Model
{
    use HasFactory, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'exportacao';
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
        'user_id',
        'arquivo',
        'local',
        'removido',
    ];

    protected $casts = [
        'id' => 'int',
        'user_id' => 'int',
        'arquivo' => 'string',
        'local' => 'string',
        'removido' => 'boolean',
    ];


    protected $appends = ['data_hora_criacao'];

    public function getDataHoraCriacaoAttribute($value)
    {
        $data = new DataHora($this->attributes['created_at']);
        return $data->dataCompleta() . ' ' . $data->hora() . ':' . $data->minuto();
    }
}
