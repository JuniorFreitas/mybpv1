<?php

namespace App\Models;

use App\Scopes\ScopeEmpresa;
use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;

/**
 * App\Models\PlanejamentoDiario
 *
 * @property int $id
 * @property int $user_id
 * @property string $data
 * @property string|null $tarefas_agendadas
 * @property string|null $importante
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PlanejamentoDiarioTarefas[] $Tarefas
 * @property-read int|null $tarefas_count
 * @method static \Illuminate\Database\Eloquent\Builder|PlanejamentoDiario newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PlanejamentoDiario newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PlanejamentoDiario query()
 * @method static \Illuminate\Database\Eloquent\Builder|PlanejamentoDiario whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlanejamentoDiario whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlanejamentoDiario whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlanejamentoDiario whereImportante($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlanejamentoDiario whereTarefasAgendadas($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlanejamentoDiario whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlanejamentoDiario whereUserId($value)
 * @mixin \Eloquent
 * @property int|null $empresa_id
 * @method static \Illuminate\Database\Eloquent\Builder|PlanejamentoDiario whereEmpresaId($value)
 */
class PlanejamentoDiario extends Model
{
    use TenantTrait;

    protected $fillable = [
        'data',
        'user_id',
        'tarefas_agendadas',
        'importante',
        'empresa_id'
    ];

    protected $casts = [
        'tarefas_agendadas',
        'importante',
        'data' => 'string',
        'user_id' => 'int',
        'empresa_id' => 'int'
    ];

    public function getDataAttribute($value)
    {
        if ($value) {
            $data = new DataHora($this->attributes['data']);
            return $data->dataCompleta();
        }
    }

    //Modificador ->data
    public function setDataAttribute($value)
    {
        if ($value) {
            $data = new DataHora($value);
            $this->attributes['data'] = $data->dataInsert();
        }
    }

    public function Tarefas()
    {
        return $this->hasMany(PlanejamentoDiarioTarefas::class, 'planejamento_id', 'id');
    }

}
