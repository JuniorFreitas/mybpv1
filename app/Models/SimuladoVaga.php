<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\SimuladoVaga
 *
 * @property int $id
 * @property int $vaga_id
 * @property mixed $data_inicio
 * @property mixed $data_fim
 * @property int $duracao
 * @property int|null $simulado_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SimuladoPergunta[] $Perguntas
 * @property-read int|null $perguntas_count
 * @property-read \App\Models\Simulado $Simulado
 * @property-read mixed $duracao_segundos
 * @property-read mixed $qnt_questoes
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SimuladoVaga newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SimuladoVaga newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SimuladoVaga query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SimuladoVaga whereDataFim($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SimuladoVaga whereDataInicio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SimuladoVaga whereDuracao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SimuladoVaga whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SimuladoVaga whereSimuladoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SimuladoVaga whereVagaId($value)
 * @mixin \Eloquent
 * @property bool|null $online
 * @property-read \App\Models\Vaga $Vaga
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Activitylog\Models\Activity[] $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SimuladoVaga whereOnline($value)
 * @property int|null $empresa_id
 * @property int|null $vagas_abertas_id
 * @method static \Illuminate\Database\Eloquent\Builder|SimuladoVaga whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SimuladoVaga whereVagasAbertasId($value)
 */
class SimuladoVaga extends Model
{
    use LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'SimuladoVaga';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = "";
    }

    protected $fillable = [
        'simulado_id',
        'vaga_id',
        'data_inicio',
        'data_fim',
        'duracao',
        'online',
        'empresa_id',
        'vagas_abertas_id',
    ];

    protected $casts = [
        'simulado_id' => 'int',
        'vaga_id' => 'int',
        'data_inicio' => 'date:d/m/Y',
        'data_fim' => 'date:d/m/Y',
        'duracao' => 'int',
        'online' => 'boolean',
        'empresa_id' => 'int',
        'vagas_abertas_id' => 'int',
    ];

    protected $appends = ['duracao_segundos'];

    public function Simulado()
    {
        return $this->hasOne(Simulado::class, 'id', 'simulado_id');
    }

    public function Vaga()
    {
        return $this->hasOne(Vaga::class, 'id', 'vaga_id');
    }

    public function getQntQuestoesAttribute()
    {
        return $this->Perguntas()->count();
    }

    public function getDuracaoSegundosAttribute()
    {
        return $this->duracao * 3600;
    }

    public function Perguntas()
    {
        return $this->hasMany(SimuladoPergunta::class, 'simulado_id', 'simulado_id');
    }
}
