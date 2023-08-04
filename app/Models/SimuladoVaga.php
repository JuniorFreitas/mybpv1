<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\SimuladoVaga
 *
 * @property int $id
 * @property int $simulado_id
 * @property int $vaga_id
 * @property mixed $data_inicio
 * @property mixed $data_fim
 * @property int $duracao
 * @property bool|null $online
 * @property int|null $empresa_id
 * @property int|null $vagas_abertas_id
 * @property bool $ativo
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SimuladoPergunta[] $Perguntas
 * @property-read int|null $perguntas_count
 * @property-read \App\Models\Simulado|null $Simulado
 * @property-read \App\Models\Vaga|null $Vaga
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\VagasAbertas[] $VagasAbertas
 * @property-read int|null $vagas_abertas_count
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @property-read mixed $duracao_segundos
 * @property-read mixed $qnt_questoes
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Sanctum\PersonalAccessToken[] $tokens
 * @property-read int|null $tokens_count
 * @method static \Illuminate\Database\Eloquent\Builder|SimuladoVaga newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SimuladoVaga newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SimuladoVaga query()
 * @method static \Illuminate\Database\Eloquent\Builder|SimuladoVaga whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SimuladoVaga whereDataFim($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SimuladoVaga whereDataInicio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SimuladoVaga whereDuracao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SimuladoVaga whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SimuladoVaga whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SimuladoVaga whereOnline($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SimuladoVaga whereSimuladoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SimuladoVaga whereVagaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SimuladoVaga whereVagasAbertasId($value)
 * @mixin \Eloquent
 */
class SimuladoVaga extends Model
{
    use LogsActivity;
    use HasApiTokens;
    use TenantTrait;

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
        'ativo'
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
        'ativo' => 'boolean'
    ];

    public $timestamps = false;

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

    public function VagasAbertas()
    {
        return $this->hasMany(VagasAbertas::class, 'id', 'vagas_abertas_id');
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->data_inicio = (new DataHora())->dataHoraInsert();
            $model->data_fim = (new DataHora())->dataHoraInsert();
        });

        static::updating(function ($model) {
            $model->data_inicio = (new DataHora())->dataHoraInsert();
            $model->data_fim = (new DataHora())->dataHoraInsert();
        });

//        static::updating(function ($model) {
//            $model->data_inicio = auth()->id();
//        });
    }
}
