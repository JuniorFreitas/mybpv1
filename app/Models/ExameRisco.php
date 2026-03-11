<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ExameRisco
 *
 * @property int $id
 * @property int $empresa_id
 * @property int|null $risco_tipo
 * @property string $label
 * @property bool $ativo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ExameRiscoTipo|null $Tipo
 * @method static \Illuminate\Database\Eloquent\Builder|ExameRisco newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExameRisco newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExameRisco query()
 * @method static \Illuminate\Database\Eloquent\Builder|ExameRisco whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameRisco whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameRisco whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameRisco whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameRisco whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameRisco whereRiscoTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameRisco whereUpdatedAt($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @mixin \Eloquent
 */
class ExameRisco extends Model
{
    use LogsActivity, HasActivitylogOptions, HasFactory;

    protected static $logName = 'ExameRisco';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

    protected $fillable = [
        'empresa_id',
        'risco_tipo',
        'label',
        'ativo'
    ];
    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'risco_tipo' => 'int',
        'label' => 'string',
        'ativo' => 'boolean'
    ];

    public function Tipo()
    {
        return $this->hasOne(ExameRiscoTipo::class,'id','risco_tipo');
    }
}
