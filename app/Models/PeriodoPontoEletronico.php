<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\Models\PeriodoPontoEletronico
 *
 * @property int $id
 * @property int $ponto_id
 * @property string|null $autenticacao_entrada
 * @property \datetime $entrada
 * @property bool $facial_entrada
 * @property int|null $arquivo_id_entrada
 * @property float|null $lat_entrada
 * @property float|null $long_entrada
 * @property string|null $autenticacao_saida
 * @property \datetime|null $saida
 * @property bool|null $facial_saida
 * @property int|null $arquivo_id_saida
 * @property float|null $lat_saida
 * @property float|null $long_saida
 * @property int $minutos
 * @property \datetime|null $created_at
 * @property \datetime|null $updated_at
 * @property-read Arquivo|null $FotoEntrada
 * @property-read Arquivo|null $FotoSaida
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @property-read mixed $hora_entrada
 * @property-read mixed $hora_saida
 * @property-read mixed $horas_trabalhadas
 * @property-read mixed $horas_trabalhadas_format
 * @method static \Illuminate\Database\Eloquent\Builder|PeriodoPontoEletronico newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PeriodoPontoEletronico newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PeriodoPontoEletronico query()
 * @method static \Illuminate\Database\Eloquent\Builder|PeriodoPontoEletronico whereArquivoIdEntrada($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PeriodoPontoEletronico whereArquivoIdSaida($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PeriodoPontoEletronico whereAutenticacaoEntrada($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PeriodoPontoEletronico whereAutenticacaoSaida($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PeriodoPontoEletronico whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PeriodoPontoEletronico whereEntrada($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PeriodoPontoEletronico whereFacialEntrada($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PeriodoPontoEletronico whereFacialSaida($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PeriodoPontoEletronico whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PeriodoPontoEletronico whereLatEntrada($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PeriodoPontoEletronico whereLatSaida($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PeriodoPontoEletronico whereLongEntrada($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PeriodoPontoEletronico whereLongSaida($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PeriodoPontoEletronico whereMinutos($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PeriodoPontoEletronico wherePontoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PeriodoPontoEletronico whereSaida($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PeriodoPontoEletronico whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \App\Models\PontoEletronico|null $Ponto
 */
class PeriodoPontoEletronico extends Model
{
    use HasFactory,LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'periodo_ponto';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;
    public $timestamps = true;
    protected $table = 'periodo_ponto_eletronicos';

    protected $fillable = [
        //'id' ,
        'ponto_id',

        //'autenticacao_entrada' ,
        'entrada',
        'facial_entrada',
        'arquivo_id_entrada',
        'lat_entrada',
        'long_entrada',

        //'autenticacao_saida' ,
        'saida',
        'facial_saida',
        'arquivo_id_saida',
        'lat_saida',
        'long_saida',

        'minutos',
    ];
    protected $casts = [
        'id' => 'int',
        'ponto_id' => 'int',

        'autenticacao_entrada' => 'string',
        'entrada' => 'datetime:d/m/Y à\s H:i:s',
        'facial_entrada' => 'boolean',
        'arquivo_id_entrada' => 'int',
        'lat_entrada' => 'float',
        'long_entrada' => 'float',

        'autenticacao_saida' => 'string',
        'saida' => 'datetime:d/m/Y à\s H:i:s',
        'facial_saida' => 'boolean',
        'arquivo_id_saida' => 'int',
        'lat_saida' => 'float',
        'long_saida' => 'float',

        'minutos' => 'int',

        'created_at' => 'datetime:d/m/Y à\s H:i:s',
        'updated_at' => 'datetime:d/m/Y à\s H:i:s',

    ];

    protected $appends = ['horaEntrada', 'horaSaida', 'horasTrabalhadas', 'horasTrabalhadasFormat'];

    //protected $touches = ['Ponto'];

    public function getDescriptionForEvent(string $eventName): string {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName) {
        $activity->descricao = "";
    }

    protected function serializeDate(DateTimeInterface $date) {
        return $date->format('Y-m-d H:i:s');
    }

    public function getHoraEntradaAttribute() {
        $data = new DataHora($this->entrada);
        return $data->hora() . ':' . $data->minuto();
    }

    public function getHoraSaidaAttribute() {
        $data = new DataHora($this->saida);
        if ($this->saida) {
            return $data->hora() . ':' . $data->minuto();
        }
        return null;

    }

    public function Ponto() {
        return $this->hasOne(PontoEletronico::class, 'id', 'ponto_id');
    }

    public function FotoEntrada() {
        return $this->hasOne(Arquivo::class, 'id', 'arquivo_id_entrada');
    }

    public function FotoSaida() {
        return $this->hasOne(Arquivo::class, 'id', 'arquivo_id_saida');
    }

    public function getHorasTrabalhadasAttribute() {
        $inicio = new DataHora($this->entrada);
        if ($this->saida) {
            $fim = new DataHora($this->saida);
        } else {
            $fim = new DataHora();
        }

        return DataHora::distanciaTempo($inicio->dataHoraInsert(), $fim->dataHoraInsert());
    }

    public function getHorasTrabalhadasFormatAttribute() {
        $dados = $this->horasTrabalhadas;

        $string = "$dados[hora]h:$dados[minuto]m";

        if (intval($dados['dia']) > 0) {
            $string = intval($dados['dia']) . " dia(s) " . $string;
        }
        return $string;
    }
}
