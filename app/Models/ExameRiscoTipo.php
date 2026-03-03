<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ExameRiscoTipo
 *
 * @property int $id
 * @property string $label
 * @property bool $ativo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ExameRiscoTipo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExameRiscoTipo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExameRiscoTipo query()
 * @method static \Illuminate\Database\Eloquent\Builder|ExameRiscoTipo whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameRiscoTipo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameRiscoTipo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameRiscoTipo whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameRiscoTipo whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ExameRiscoTipo extends Model
{
    use LogsActivity, HasActivitylogOptions, HasFactory;

    protected static $logName = 'ExameRiscoTipo';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

    protected $fillable = [

        'label',
        'ativo'
    ];
    protected $casts = [
        'id' => 'int',
        'label' => 'string',
        'ativo' => 'boolean'
    ];

}
