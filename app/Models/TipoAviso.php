<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\TipoAviso
 *
 * @property int $id
 * @property string $descricao
 * @property bool $ativo
 * @method static \Illuminate\Database\Eloquent\Builder|TipoAviso newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TipoAviso newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TipoAviso query()
 * @method static \Illuminate\Database\Eloquent\Builder|TipoAviso whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoAviso whereDescricao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoAviso whereId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @mixin \Eloquent
 */
class TipoAviso extends Model
{
    use LogsActivity, HasActivitylogOptions, HasFactory;

    protected static $logName = 'TipoAviso';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

    protected $table = 'tipo_aviso';
    protected $fillable = [
        'descricao',
        'ativo',
    ];
    protected $casts = [
        'id' => 'int',
        'descricao' => 'string',
        'ativo' => 'boolean',
    ];
    public $timestamps = false;
}
