<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\NpsPergunta
 *
 * @property int $id
 * @property int|null $empresa_id
 * @property string $texto
 * @property int $ordem
 * @property bool $ativo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $Empresa
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\NpsRespostaItem> $respostaItens
 * @mixin \Eloquent
 */
class NpsPergunta extends Model
{
    protected $table = 'nps_perguntas';

    protected $fillable = [
        'empresa_id',
        'texto',
        'ordem',
        'ativo',
    ];

    protected $casts = [
        'empresa_id' => 'integer',
        'ordem' => 'integer',
        'ativo' => 'boolean',
    ];

    public function Empresa()
    {
        return $this->belongsTo(User::class, 'empresa_id');
    }

    public function respostaItens()
    {
        return $this->hasMany(NpsRespostaItem::class, 'nps_pergunta_id');
    }

    /**
     * Perguntas ativas para a empresa do usuário (globais + da empresa), ordenadas.
     *
     * @param int|null $empresaId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function ativasParaEmpresa(?int $empresaId)
    {
        $query = static::query()
            ->where('ativo', true)
            ->orderBy('ordem');

        if ($empresaId !== null) {
            $query->where(function ($q) use ($empresaId) {
                $q->whereNull('empresa_id')
                    ->orWhere('empresa_id', $empresaId);
            });
        } else {
            $query->whereNull('empresa_id');
        }

        return $query;
    }
}
