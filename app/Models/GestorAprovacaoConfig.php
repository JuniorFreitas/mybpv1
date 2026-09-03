<?php

namespace App\Models;

use App\Models\Concerns\HasActivitylogOptions;
use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\GestorAprovacaoConfig
 *
 * Config genérica por empresa + tipo de processo: define um único usuário como
 * "gestor aprovação" designado para aquele processo, substituindo o fluxo de
 * aprovação padrão (ex.: gestor de origem/destino, na Transferência Prevista).
 * Independente da AprovacaoExtraConfig (que continua sendo uma etapa extra opcional).
 *
 * @property int $id
 * @property int $empresa_id
 * @property string $tipo_processo
 * @property int $gestor_aprovacao_id
 * @property bool $ativo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Cliente|null $Empresa
 * @property-read User|null $GestorAprovacao
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GestorAprovacaoConfig ativo()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GestorAprovacaoConfig tipoProcesso($tipo)
 * @mixin \Eloquent
 */
class GestorAprovacaoConfig extends Model
{
    use LogsActivity, HasActivitylogOptions, HasFactory, TenantTrait;

    protected static $logName = 'GestorAprovacaoConfig';

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
        'tipo_processo',
        'gestor_aprovacao_id',
        'ativo',
    ];

    protected $casts = [
        'empresa_id' => 'int',
        'tipo_processo' => 'string',
        'gestor_aprovacao_id' => 'int',
        'ativo' => 'boolean',
        'created_at' => 'datetime:d/m/Y à\s H:i:s',
        'updated_at' => 'datetime:d/m/Y à\s H:i:s',
    ];

    /**
     * Reaproveita a mesma lista de tipos de processo da Aprovação Extra, para manter
     * consistência entre as duas telas de configuração.
     */
    public const TIPOS_PROCESSO = AprovacaoExtraConfig::TIPOS_PROCESSO;

    public function Empresa()
    {
        return $this->belongsTo(Cliente::class, 'empresa_id', 'id');
    }

    public function GestorAprovacao()
    {
        return $this->hasOne(User::class, 'id', 'gestor_aprovacao_id')->select(['id', 'nome', 'login', 'ativo']);
    }

    public function scopeAtivo($query)
    {
        return $query->where('ativo', true);
    }

    public function scopeTipoProcesso($query, $tipo)
    {
        return $query->where('tipo_processo', $tipo);
    }

    /**
     * Busca a configuração ativa de gestor aprovação para um tipo de processo específico.
     */
    public static function getConfigAtiva($empresaId, $tipoProcesso)
    {
        return self::withoutGlobalScopes()
            ->select('id', 'empresa_id', 'tipo_processo', 'gestor_aprovacao_id', 'ativo')
            ->where('empresa_id', $empresaId)
            ->where('tipo_processo', $tipoProcesso)
            ->where('ativo', true)
            ->first();
    }
}
