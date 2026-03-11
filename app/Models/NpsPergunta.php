<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

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
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read int|null $resposta_itens_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NpsPergunta newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NpsPergunta newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NpsPergunta query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NpsPergunta whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NpsPergunta whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NpsPergunta whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NpsPergunta whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NpsPergunta whereOrdem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NpsPergunta whereTexto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NpsPergunta whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class NpsPergunta extends Model
{

    use LogsActivity, HasActivitylogOptions;

    protected static $logName = 'NpsPergunta';
    protected $table = 'nps_perguntas';

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
