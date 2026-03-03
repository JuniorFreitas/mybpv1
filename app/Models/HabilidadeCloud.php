<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\HabilidadeCloud
 *
 * @property int $id
 * @property string $nome
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GrupoCloud> $grupo
 * @property-read int|null $grupo_count
 * @method static \Illuminate\Database\Eloquent\Builder|HabilidadeCloud newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HabilidadeCloud newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HabilidadeCloud query()
 * @method static \Illuminate\Database\Eloquent\Builder|HabilidadeCloud whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HabilidadeCloud whereNome($value)
 * @mixin \Eloquent
 */
class HabilidadeCloud extends Model
{
    use LogsActivity, HasActivitylogOptions, HasFactory;

    protected static $logName = 'HabilidadeCloud';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

    protected $fillable = [
        'nome'
    ];

    public function usesTimestamps(): bool
    {
        return false;
    }

    public function grupo()
    {
        return $this->belongsToMany(GrupoCloud::class, 'grupo_habilidade_cloud');
    }
}
