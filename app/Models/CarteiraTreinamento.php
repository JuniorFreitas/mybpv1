<?php

namespace App\Models;

use App\Models\Concerns\HasActivitylogOptions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Modelo visual da carteira de treinamento (global ou por empresa).
 *
 * @property int $id
 * @property int|null $empresa_id
 * @property string $nome
 * @property string $slug
 * @property bool $ativo
 * @property string|null $cabecalho_img
 * @property string|null $verso_img
 */
class CarteiraTreinamento extends Model
{
    use LogsActivity, HasActivitylogOptions;

    protected static $logName = 'CarteiraTreinamento';

    protected $table = 'carteiras_treinamento';

    protected $fillable = [
        'empresa_id',
        'nome',
        'slug',
        'ativo',
        'cabecalho_img',
        'verso_img',
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
}
