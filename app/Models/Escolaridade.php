<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;

/**
 * App\Models\Escolaridade
 *
 * @property int $id
 * @property string $tipo
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|Escolaridade newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Escolaridade newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Escolaridade query()
 * @method static \Illuminate\Database\Eloquent\Builder|Escolaridade whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Escolaridade whereTipo($value)
 * @mixin \Eloquent
 */
class Escolaridade extends Model
{
    use HasFactory, LogsActivity, HasActivitylogOptions;

    protected static $logFillable = true;
    protected static $logName = 'escolaridades';
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

    protected $fillable = ['tipo'];
    protected $casts = ['id' => 'int', 'tipo' => 'string'];
    protected $table = 'escolaridades';

    public function getTipoAttribute()
    {
        $tipo = explode('- ', $this->attributes['tipo']);
        return $tipo[1];
    }
}
