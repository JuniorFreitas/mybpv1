<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\HorarioAcesso
 *
 * @property int $id
 * @property mixed $abertura
 * @property mixed $fechamento
 * @property bool $ativo
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|HorarioAcesso newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HorarioAcesso newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HorarioAcesso query()
 * @method static \Illuminate\Database\Eloquent\Builder|HorarioAcesso whereAbertura($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HorarioAcesso whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HorarioAcesso whereFechamento($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HorarioAcesso whereId($value)
 * @mixin \Eloquent
 */
class HorarioAcesso extends Model
{
    use HasFactory, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'horario_acesso';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    public function getDescriptionForEvent(string $eventName): string {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = "";
    }

    protected $table='horario_acesso';
    protected $fillable = [
        'abertura',
        'fechamento',
        'ativo'];

    protected $casts = [
        'abertura' => 'time',
        'fechamento' => 'time',
        'ativo' => 'boolean',
    ];

    public function usesTimestamps(): bool
    {
        return false;
    }

    public  function getAberturaAttribute(){
        $hora = new DataHora($this->attributes['abertura']);
        return $hora->hora().':'.$hora->minuto();
    }

    public  function getFechamentoAttribute(){
        $hora = new DataHora($this->attributes['fechamento']);
        return $hora->hora().':'.$hora->minuto();
    }
}
