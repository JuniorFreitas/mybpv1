<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;

/**
 * App\Models\TipoDocumentoServico
 *
 * @property int $id
 * @property string $nome
 * @property string $tipo pode ser para contrato, ssma...
 * @property bool $ativo
 * @property int $empresa_id
 * @method static \Illuminate\Database\Eloquent\Builder|TipoDocumentoServico newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TipoDocumentoServico newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TipoDocumentoServico query()
 * @method static \Illuminate\Database\Eloquent\Builder|TipoDocumentoServico whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoDocumentoServico whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoDocumentoServico whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoDocumentoServico whereNome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoDocumentoServico whereTipo($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @mixin \Eloquent
 */
class TipoDocumentoServico extends Model
{
    use LogsActivity, HasActivitylogOptions, HasFactory, TenantTrait;

    protected static $logName = 'TipoDocumentoServico';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = "";
    }

    protected $table = 'tipo_documento_servicos';
    public $timestamps = false;
    protected $fillable = [
        'nome',
        'empresa_id',
        'tipo_empresa',
        'ativo'
    ];

    protected $casts = [
        'nome' => 'string',
        'empresa_id' => 'int',
        'tipo_empresa' => 'boolean',
        'ativo' => 'boolean'
    ];
}
