<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;

/**
 * App\Models\CurriculoAtualizacao
 *
 * @property int $curriculo_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|CurriculoAtualizacao newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CurriculoAtualizacao newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CurriculoAtualizacao query()
 * @method static \Illuminate\Database\Eloquent\Builder|CurriculoAtualizacao whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CurriculoAtualizacao whereCurriculoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CurriculoAtualizacao whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CurriculoAtualizacao extends Model
{
    use LogsActivity, HasActivitylogOptions, HasFactory;

    protected static $logName = 'CurriculoAtualizacao';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

    protected $fillable = ['curriculo_id'];
    protected $casts = ['curriculo_id' => 'int'];

    public function getCreatedAtAttribute($value)
    {
        $data = new DataHora($this->attributes['created_at']);
        return $data->dataCompleta() . ' ' . $data->hora() . ':' . $data->minuto() . 'h';
    }
}
