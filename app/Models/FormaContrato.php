<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\FormaContrato
 *
 * @property int $id
 * @property string $titulo
 * @property bool $ativo
 * @property int $empresa_id
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|Servico newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Servico newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Servico query()
 * @method static \Illuminate\Database\Eloquent\Builder|Servico whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Servico whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Servico whereTitulo($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|FormaContrato whereEmpresaId($value)
 */
class FormaContrato extends Model
{
    use HasFactory, LogsActivity, TenantTrait;

    protected static $logFillable = true;
    protected static $logName = 'documentos_legais_forma_contrato';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    protected $table = 'documentos_legais_forma_contrato';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = "";
    }

    public function usesTimestamps(): bool
    {
        return false;
    }

    protected $fillable = ['titulo', 'ativo', 'empresa_id'];
    protected $casts = ['id' => 'int', 'titulo' => 'string', 'ativo' => 'boolean', 'empresa_id' => 'int'];

    public static function getFormaContrato($id){
        return self::find($id);
    }
}
