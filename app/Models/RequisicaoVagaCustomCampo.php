<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $empresa_id
 * @property string $label
 * @property string $tipo
 * @property array<array-key, mixed>|null $opcoes
 * @property bool $obrigatorio
 * @property int $ordem
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Cliente $Empresa
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaCustomCampo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaCustomCampo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaCustomCampo query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaCustomCampo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaCustomCampo whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaCustomCampo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaCustomCampo whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaCustomCampo whereObrigatorio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaCustomCampo whereOpcoes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaCustomCampo whereOrdem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaCustomCampo whereTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaCustomCampo whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class RequisicaoVagaCustomCampo extends Model
{
    use HasFactory, TenantTrait, LogsActivity, HasActivitylogOptions;



    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }


    protected $table = 'requisicao_vaga_custom_campos';

    public const TIPO_SIM_NAO = 'sim_nao';
    public const TIPO_TEXTO = 'texto';
    public const TIPO_TEXTAREA = 'textarea';
    public const TIPO_SELECT = 'select';

    public const TIPOS = [
        self::TIPO_SIM_NAO,
        self::TIPO_TEXTO,
        self::TIPO_TEXTAREA,
        self::TIPO_SELECT,
    ];

    protected $fillable = [
        'empresa_id',
        'label',
        'tipo',
        'opcoes',
        'obrigatorio',
        'ordem',
    ];

    protected $casts = [
        'empresa_id' => 'integer',
        'opcoes' => 'array',
        'obrigatorio' => 'boolean',
        'ordem' => 'integer',
    ];

    public function Empresa()
    {
        return $this->belongsTo(Cliente::class, 'empresa_id');
    }

    /**
     * Campos custom da empresa ordenados.
     */
    public static function porEmpresa(int $empresaId)
    {
        return static::where('empresa_id', $empresaId)->orderBy('ordem')->orderBy('id');
    }
}
