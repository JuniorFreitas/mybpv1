<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Pcmso
 *
 * @property int $id
 * @property int $empresa_id
 * @property string $label
 * @property bool $ativo
 * @property-read \App\Models\Cliente $Empresa
 * @method static \Illuminate\Database\Eloquent\Builder|Pcmso newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Pcmso newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Pcmso query()
 * @method static \Illuminate\Database\Eloquent\Builder|Pcmso whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pcmso whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pcmso whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pcmso whereLabel($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @mixin \Eloquent
 */
class Pcmso extends Model
{
    use LogsActivity, HasActivitylogOptions, HasFactory, TenantTrait;

    protected static $logName = 'Pcmso';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

    const TABELA = 'pcmsos';
    protected $table = self::TABELA;

    public $timestamps = false;

    protected $fillable = [
        'empresa_id',
        'label',
        'ativo'
    ];

    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'label' => 'string',
        'ativo' => 'boolean'
    ];

    public function Empresa()
    {
        return $this->belongsTo(Cliente::class, 'empresa_id');
    }
}
