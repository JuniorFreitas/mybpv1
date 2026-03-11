<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\EtapaTipo
 *
 * @property int $id
 * @property int $cliente_id
 * @property string $nome
 * @property bool $ativo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Cliente|null $Cliente
 * @method static \Illuminate\Database\Eloquent\Builder|EtapaTipo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EtapaTipo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EtapaTipo query()
 * @method static \Illuminate\Database\Eloquent\Builder|EtapaTipo whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtapaTipo whereClienteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtapaTipo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtapaTipo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtapaTipo whereNome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtapaTipo whereUpdatedAt($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @mixin \Eloquent
 */
class EtapaTipo extends Model
{
    use LogsActivity, HasActivitylogOptions, HasFactory;

    protected static $logName = 'EtapaTipo';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

    protected $fillable = [
        'nome',
        'cliente_id',
        'ativo'
    ];

    protected $casts = [
        'nome' => 'string',
        'cliente_id' => 'int',
        'ativo' => 'boolean'
    ];

    public function Cliente()
    {
        return $this->hasOne(Cliente::class, 'id', 'cliente_id');
    }
}
