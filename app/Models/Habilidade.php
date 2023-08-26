<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\Habilidade
 *
 * @property int $id
 * @property string $nome
 * @property string $descricao
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Papel> $papeis
 * @property-read int|null $papeis_count
 * @method static \Illuminate\Database\Eloquent\Builder|Habilidade newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Habilidade newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Habilidade query()
 * @method static \Illuminate\Database\Eloquent\Builder|Habilidade whereDescricao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Habilidade whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Habilidade whereNome($value)
 * @mixin \Eloquent
 */
class Habilidade extends Model
{
    use HasFactory, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'habilidade';
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

    protected $table = 'habilidades';
    protected $fillable = [
        'id','nome', 'descricao'
    ];
    protected $casts = [
        'id' => 'int','nome' => 'string', 'descricao' => 'string'
    ];

    public $timestamps = false;

    public function papeis()
    {
        return $this->belongsToMany(Papel::class, 'papeis_habilidades');
    }
}
