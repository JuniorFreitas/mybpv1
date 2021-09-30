<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\Cloud
 *
 * @property int $id
 * @property string $nome
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ItensCloud[] $Itens
 * @property-read int|null $itens_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ItensCloud[] $Raiz
 * @property-read int|null $raiz_count
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|Cloud newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cloud newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cloud query()
 * @method static \Illuminate\Database\Eloquent\Builder|Cloud whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cloud whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cloud whereNome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cloud whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int|null $empresa_id
 * @method static \Illuminate\Database\Eloquent\Builder|Cloud whereEmpresaId($value)
 */
class Cloud extends Model
{
    use HasFactory, LogsActivity;
    use TenantTrait;

    protected static $logFillable = true;
    protected static $logName = 'cloud';
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

    protected $fillable = ['nome','empresa_id'];
    protected $casts = ['id' => 'int', 'empresa_id' => 'int', 'nome' => 'string'];

    public function Itens()
    {
        return $this->hasMany(ItensCloud::class, 'cloud_id', 'id');
    }

    public function Raiz()
    {
        return $this->hasMany(ItensCloud::class, 'cloud_id', 'id')->whereNull('pertence');
    }

}
