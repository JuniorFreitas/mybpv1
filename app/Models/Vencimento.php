<?php

namespace App\Models;

use App\Scopes\ScopeEmpresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\Vencimento
 *
 * @property int $id
 * @property string $label
 * @property string|null $descricao
 * @property int|null $prazo_parada
 * @property int|null $prazo_fixo
 * @property bool $ativo
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|Vencimento newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Vencimento newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Vencimento query()
 * @method static \Illuminate\Database\Eloquent\Builder|Vencimento whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vencimento whereDescricao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vencimento whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vencimento whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vencimento wherePrazoFixo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vencimento wherePrazoParada($value)
 * @mixin \Eloquent
 * @property int|null $ordem
 * @method static \Illuminate\Database\Eloquent\Builder|Vencimento whereOrdem($value)
 */
class Vencimento extends Model
{
    use HasFactory, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'vencimento';
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

    public $timestamps = false;
    protected $fillable = [
        'label',
        'descricao',
        'prazo_parada',
        'prazo_fixo',
        'ativo',
        'ordem',
        'empresa_id'
    ];

    protected $casts = [
        'id' => 'int',
        'label' => 'string',
        'descricao' => 'string',
        'prazo_parada' => 'int',
        'prazo_fixo' => 'int',
        'ativo' => 'boolean',
        'ordem' => 'int',
        'empresa_id' => 'int'
    ];

    //Scopo de ClienteID (Empresa)
    protected static function booted()
    {
        static::addGlobalScope(new ScopeEmpresa);
    }
}
