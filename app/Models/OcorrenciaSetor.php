<?php

namespace App\Models;

use App\Scopes\ScopeEmpresa;
use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\OcorrenciaSetor
 *
 * @property int $id
 * @property string $nome
 * @property int|null $empresa_id
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|OcorrenciaSetor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OcorrenciaSetor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OcorrenciaSetor query()
 * @method static \Illuminate\Database\Eloquent\Builder|OcorrenciaSetor whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OcorrenciaSetor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OcorrenciaSetor whereNome($value)
 * @mixin \Eloquent
 */
class OcorrenciaSetor extends Model {
    use LogsActivity;
    use TenantTrait;

    protected static $logFillable = true;
    protected static $logName = 'ocorrencias';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    public function getDescriptionForEvent(string $eventName): string {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName) {
        $activity->descricao = "";
    }

    protected $table = 'ocorrencias_setores';
    protected $fillable = ['nome','empresa_id'];
    protected $casts = [
        'id' => 'int',
        'nome' => 'string',
        'empresa_id' => 'int'
    ];
    public $timestamps = false;

}
