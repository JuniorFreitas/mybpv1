<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;

/**
 * App\Models\TipoDocumento
 *
 * @property int $id
 * @property string $nome
 * @property string $tipo pode ser empresa, ssma...
 * @property bool $ativo
 * @property int $empresa_id
 * @method static \Illuminate\Database\Eloquent\Builder|TipoDocumento newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TipoDocumento newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TipoDocumento query()
 * @method static \Illuminate\Database\Eloquent\Builder|TipoDocumento whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoDocumento whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoDocumento whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoDocumento whereNome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoDocumento whereTipo($value)
 * @mixin \Eloquent
 */
class TipoDocumento extends Model
{
    use LogsActivity, HasActivitylogOptions, HasFactory, TenantTrait;

    protected static $logName = 'TipoDocumento';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = "";
    }

    protected $table = 'tipo_documentos';
    public $timestamps = false;
    protected $fillable = [
        'nome',
        'empresa_id',
        'tipo',
        'ativo'
    ];

    protected $casts = [
        'nome' => 'string',
        'empresa_id' => 'int',
        'tipo' => 'string',
        'ativo' => 'boolean'
    ];

    public const TIPO_DOCUMENTOS = ['contrato' => 'Contrato', 'empresa' => 'Empresa', 'ssma' => 'SSMA'];
}
