<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PlanejamentoDiarioTarefas
 *
 * @property int $id
 * @property int $planejamento_id
 * @property string $tarefa
 * @property string $status
 * @property-read \App\Models\PlanejamentoDiario|null $PlanejamentoDiario
 * @method static \Illuminate\Database\Eloquent\Builder|PlanejamentoDiarioTarefas newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PlanejamentoDiarioTarefas newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PlanejamentoDiarioTarefas query()
 * @method static \Illuminate\Database\Eloquent\Builder|PlanejamentoDiarioTarefas whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlanejamentoDiarioTarefas wherePlanejamentoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlanejamentoDiarioTarefas whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlanejamentoDiarioTarefas whereTarefa($value)
 * @mixin \Eloquent
 */
class PlanejamentoDiarioTarefas extends Model
{
    protected $fillable = [
        'planejamento_id',
        'tarefa',
        'status',
    ];

    protected $casts = [
        'planejamento_id' => 'int',
        'tarefa' => 'string',
        'status' => 'string',
    ];

    public $timestamps = false;

    public function PlanejamentoDiario()
    {
        return $this->hasOne(PlanejamentoDiario::class, 'id', 'planejamento_id');
    }
}
