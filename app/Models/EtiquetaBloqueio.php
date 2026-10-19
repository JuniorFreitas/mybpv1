<?php

namespace App\Models;

use App\Models\Concerns\HasActivitylogOptions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Modelo visual da etiqueta de bloqueio (global ou por empresa).
 *
 * @property int $id
 * @property int|null $empresa_id
 * @property string $nome
 * @property string $slug
 * @property bool $ativo
 * @property string|null $ramal_emergencia
 * @property string|null $bloqueio_texto_nao_use
 * @property string|null $bloqueio_texto_demissao
 * @property string|null $bloqueio_texto_cuidado
 * @property string|null $bloqueio_texto_homens_trabalhando
 */
class EtiquetaBloqueio extends Model
{
    use LogsActivity, HasActivitylogOptions;

    protected static $logName = 'EtiquetaBloqueio';

    protected $table = 'etiquetas_bloqueio';

    protected $fillable = [
        'empresa_id',
        'nome',
        'slug',
        'ativo',
        'ramal_emergencia',
        'bloqueio_texto_nao_use',
        'bloqueio_texto_demissao',
        'bloqueio_texto_cuidado',
        'bloqueio_texto_homens_trabalhando',
    ];

    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'ativo' => 'boolean',
    ];

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

    public function Empresa()
    {
        return $this->belongsTo(Cliente::class, 'empresa_id', 'id');
    }

    public function isGlobal(): bool
    {
        return $this->empresa_id === null;
    }

    public function scopeVisiveisParaEmpresa(Builder $query, int $empresaId): Builder
    {
        return $query->where(function (Builder $q) use ($empresaId) {
            $q->whereNull('empresa_id')->orWhere('empresa_id', $empresaId);
        });
    }

    public function scopeAtivos(Builder $query): Builder
    {
        return $query->where('ativo', true);
    }

    /**
     * Array compatível com o antigo config_carteira (campos de etiqueta).
     */
    public function toConfigCarteiraFragment(): array
    {
        return [
            'exibir_etiqueta_bloqueio' => true,
            'ramal_emergencia' => (string) ($this->ramal_emergencia ?? '1199'),
            'bloqueio_texto_nao_use' => (string) ($this->bloqueio_texto_nao_use ?? ''),
            'bloqueio_texto_demissao' => (string) ($this->bloqueio_texto_demissao ?? ''),
            'bloqueio_texto_cuidado' => (string) ($this->bloqueio_texto_cuidado ?? ''),
            'bloqueio_texto_homens_trabalhando' => (string) ($this->bloqueio_texto_homens_trabalhando ?? ''),
        ];
    }
}
