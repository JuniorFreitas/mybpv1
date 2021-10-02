<?php

namespace App\Models\Models;

use App\Models\EmpresaEscala;
use App\Models\EscalaJornada;
use App\Models\OcorrenciaJornada;
use App\Models\PeriodoJornada;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\Models\PontoEletronico
 *
 * @property int $id
 * @property string $autenticacao
 * @property int $funcionario_id
 * @property int $escala_id
 * @property int $jornada_id
 * @property int $ocorrencia_jornada_id
 * @property int $periodo_id
 * @property bool $facial
 * @property float|null $lat
 * @property float|null $long
 * @property string|null $justificativa
 * @property \datetime|null $created_at
 * @property \datetime|null $updated_at
 * @property-read EmpresaEscala|null $Escala
 * @property-read User|null $Funcionario
 * @property-read EscalaJornada|null $Jornada
 * @property-read OcorrenciaJornada|null $OcorrenciaJornada
 * @property-read PeriodoJornada|null $Periodo
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico query()
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereAutenticacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereEscalaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereFacial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereFuncionarioId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereJornadaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereJustificativa($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereLong($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereOcorrenciaJornadaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico wherePeriodoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int $empresa_id
 * @property int $ocorrencia_id
 * @property int $duracao
 * @property int|null $duracao_normal
 * @property int|null $duracao_extra
 * @property int|null $duracao_noturna
 * @property string $tipo_frequencia
 * @property int $tempo_limite_falta
 * @property int $tempo_limite_saida
 * @property int $limite_tolerancia
 * @property int $verificado
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereDuracao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereDuracaoExtra($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereDuracaoNormal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereDuracaoNoturna($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereLimiteTolerancia($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereOcorrenciaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereTempoLimiteFalta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereTempoLimiteSaida($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereTipoFrequencia($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereVerificado($value)
 */
class PontoEletronico extends Model
{
    use HasFactory,LogsActivity;
    protected static $logFillable = true;
    protected static $logName = 'ponto_eletronico';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;
    public $timestamps=true;
    protected $table = 'ponto_eletronicos';
    protected $fillable = [
        'funcionario_id',
        'escala_id',
        'jornada_id',
        'ocorrencia_jornada_id',
        'periodo_id',
        'facial' ,
        'lat',
        'long',
        'justificativa' ,
    ];
    protected $casts = [
        'id' => 'int',
        'autenticacao' => 'string',
        'funcionario_id' => 'int',
        'escala_id' => 'int',
        'jornada_id' => 'int',
        'ocorrencia_jornada_id' => 'int',
        'periodo_id' => 'int',
        'facial' => 'boolean',
        'lat' => 'float',
        'long' => 'float',
        'justificativa' => 'string',
        'created_at' => 'datetime:d/m/Y à\s H:i:s',
        'updated_at' => 'datetime:d/m/Y à\s H:i:s',

    ];

    public function getDescriptionForEvent(string $eventName): string {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName) {
        $activity->descricao = "";
    }

    protected function serializeDate(DateTimeInterface $date) {
        return $date->format('Y-m-d H:i:s');
    }

    public function Funcionario(){
        return $this->hasOne(User::class,'id','funcionario_id');
    }

    public function Escala(){
        return $this->hasOne(EmpresaEscala::class,'id','escala_id');
    }

    public function Jornada(){
        return $this->hasOne(EscalaJornada::class,'id','jornada_id');
    }

    public function OcorrenciaJornada(){
        return $this->hasOne(OcorrenciaJornada::class,'id','ocorrencia_jornada_id');
    }

    public function Periodo(){
        return $this->hasOne(PeriodoJornada::class,'id','periodo_id');
    }
}
