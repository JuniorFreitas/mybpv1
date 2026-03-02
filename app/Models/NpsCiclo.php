<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use Illuminate\Database\Eloquent\Model;

/**
 * Ciclo/campanha NPS: período com nome para exibir o modal e consolidar resultados.
 *
 * @property int $id
 * @property string $nome
 * @property \Carbon\Carbon $data_inicio
 * @property \Carbon\Carbon $data_fim
 * @property bool $ativo
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class NpsCiclo extends Model
{

    use LogsActivity, HasActivitylogOptions;

    protected static $logName = 'NpsCiclo';
    protected $table = 'nps_ciclos';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

    protected $fillable = [
        'nome',
        'data_inicio',
        'data_fim',
        'ativo',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'ativo' => 'boolean',
    ];

    /**
     * Ciclo que está vigente hoje (hoje entre data_inicio e data_fim e ativo).
     */
    public static function cicloVigente(): ?self
    {
        $hoje = now()->toDateString();

        return static::query()
            ->where('ativo', true)
            ->where('data_inicio', '<=', $hoje)
            ->where('data_fim', '>=', $hoje)
            ->orderByDesc('data_inicio')
            ->first();
    }

    public function respostas()
    {
        return $this->hasMany(NpsResposta::class, 'nps_ciclo_id');
    }

    /**
     * Label para selects (nome + período).
     */
    public function getLabelAttribute(): string
    {
        return $this->nome . ' (' . $this->data_inicio->format('d/m/Y') . ' a ' . $this->data_fim->format('d/m/Y') . ')';
    }
}
