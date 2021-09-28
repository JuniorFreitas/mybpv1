<?php

namespace App\Models;

use App\Scopes\ScopeEmpresa;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;


/**
 * App\Models\Models\PontoEletronico
 *
 * @property int $id
 * @property int $empresa_id
 * @property int $funcionario_id
 * @property int|null $jornada_id
 * @property int $ocorrencia_id
 * @property string|null $justificativa
 * @property \datetime|null $created_at
 * @property \datetime|null $updated_at
 * @property-read User|null $Funcionario
 * @property-read EscalaJornada|null $Jornada
 * @property-read OcorrenciaJornada|null $OcorrenciaJornada
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Models\PeriodoPontoEletronico[] $Periodos
 * @property-read int|null $periodos_count
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico query()
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereFuncionarioId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereJornadaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereJustificativa($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereOcorrenciaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read OcorrenciaJornada|null $Ocorrencia
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Models\PeriodoPontoEletronico[] $PeriodosEmAberto
 * @property-read int|null $periodos_em_aberto_count
 * @property int $duracao
 * @property string $tipo_frequencia
 * @property int $tempo_limite_falta
 * @property int $tempo_limite_saida
 * @property int $limite_tolerancia
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereDuracao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereLimiteTolerancia($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereTempoLimiteFalta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereTempoLimiteSaida($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereTipoFrequencia($value)
 * @property bool $verificado
 * @property-read mixed $dia
 * @property-read mixed $duracao_jornada
 * @property-read mixed $horas_extra
 * @property-read mixed $horas_extra_format
 * @property-read mixed $total_minutos
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereVerificado($value)
 * @property-read mixed $dia_sem
 * @property-read mixed $dia_semana
 * @property-read mixed $horas_normal
 * @property-read mixed $horas_normal_format
 * @property-read mixed $duracao_jornada_original
 * @property-read mixed $horas_normal_original
 * @property int|null $duracao_extra
 * @property int|null $duracao_noturna
 * @property-read mixed $horas_normal_original_format
 * @property-read mixed $horas_noturna
 * @property-read mixed $horas_noturna_format
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereDuracaoExtra($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereDuracaoNoturna($value)
 * @property int|null $duracao_normal
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereDuracaoNormal($value)
 * @property string $autenticacao
 * @property int $escala_id
 * @property int $ocorrencia_jornada_id
 * @property int $periodo_id
 * @property int $facial
 * @property float|null $lat
 * @property float|null $long
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereAutenticacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereEscalaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereFacial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereLong($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereOcorrenciaJornadaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico wherePeriodoId($value)
 */
class PontoEletronico extends Model {
    use HasFactory, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'ponto_eletronico';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;
    public $timestamps = true;
    protected $table = 'ponto_eletronicos';
    protected $fillable = [
        //'id' ,
        'empresa_id',
        'funcionario_id',
        'jornada_id',
        'ocorrencia_id',
        'duracao',
        'duracao_normal',
        'duracao_extra',
        'duracao_noturna',

        'tipo_frequencia',
        'tempo_limite_falta',
        'tempo_limite_saida',
        'limite_tolerancia',

        'justificativa',
        'verificado',
        'created_at',
        'updated_at',
    ];
    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'funcionario_id' => 'int',
        'jornada_id' => 'int',
        'ocorrencia_id' => 'int',

        'duracao' => 'int',
        'duracao_normal' => 'int',
        'duracao_extra' => 'int',
        'duracao_noturna' => 'int',

        'tipo_frequencia' => 'string',
        'tempo_limite_falta' => 'int',
        'tempo_limite_saida' => 'int',
        'limite_tolerancia' => 'int',

        'justificativa' => 'string',
        'verificado' => 'boolean',
        'created_at' => 'datetime:d/m/Y à\s H:i:s',
        'updated_at' => 'datetime:d/m/Y à\s H:i:s',

    ];

    protected $appends = [
        'totalMinutos', 'duracaoJornada', 'duracaoJornadaOriginal',
        'horasNormalOriginal', 'horasNormalOriginalFormat',
        'horasNormal', 'horasNormalFormat', 'horasExtra', 'horasExtraFormat',
        'horasNoturna', 'horasNoturnaFormat',
        'dia', 'diaSemana', 'diaSem'];


    public function getDescriptionForEvent(string $eventName): string {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName) {
        $activity->descricao = "";
    }

    protected function serializeDate(DateTimeInterface $date) {
        return $date->format('Y-m-d H:i:s');
    }

    protected static function booted() {
        static::creating(function ($model) {
            if (auth()->user()) { // esta assim pro conta do CRON
                $model->empresa_id = auth()->user()->empresa_id;
                $model->funcionario_id = auth()->id(); // se apagar isso, verificar os cruds na tela de ponto (PontoEletronicoController)
            }
        });

        /*static::updated(function ($model) {


        });*/

        static::addGlobalScope(new ScopeEmpresa());
    }

    //Relacionamentos --------------------------
    public function Funcionario() {
        return $this->hasOne(User::class, 'id', 'funcionario_id');
    }

    public function Jornada() {
        return $this->hasOne(EscalaJornada::class, 'id', 'jornada_id');
    }

    public function Ocorrencia() {
        return $this->hasOne(OcorrenciaJornada::class, 'id', 'ocorrencia_id')
            ->withoutGlobalScopes();
    }

    public function Periodos() {
        return $this->hasMany(PeriodoPontoEletronico::class, 'ponto_id', 'id');
    }

    public function PeriodosEmAberto() {
        return $this->hasMany(PeriodoPontoEletronico::class, 'ponto_id', 'id')->whereNull('saida');
    }


    public static function periodoAberto($data = null, $ID_USUARIO = null) {
        if ($ID_USUARIO == null) {
            $ID_USUARIO = auth()->id();
        }
        $ponto = PontoEletronico::orderByDesc('created_at');
        if ($data) {
            $data = new DataHora($data);
            $ponto->whereHas('Periodos', function ($q) use ($data) {
                $q->whereNull('saida');
                $q->whereDate('entrada', $data->dataInsert());
            });
        }
        $ponto->whereFuncionarioId($ID_USUARIO);
        $ponto = $ponto->first();
        return $ponto ? $ponto : null;

    }

    public function recalcularDuracoes() {
        if ($this->PeriodosEmAberto()->count() === 0) {
            $this->duracao_extra = $this->horas_extra;
            $this->duracao_normal = $this->horas_normal;
            $this->duracao_noturna = $this->horas_noturna;
            $this->save();
        }
    }

    /*public static function proximaAcao($data=null,$ID_USUARIO=null){
        if($ID_USUARIO==null){
            $ID_USUARIO =  auth()->id();
        }
        $busca = PontoEletronico::orderByDesc('created_at');
        if($data){
            $inicio = new DataHora($data.' 00:00:00');
            $fim = new DataHora($data.' 23:59:59');
            $busca->whereBetween('created_at',[$inicio->dataHoraInsert(),$fim->dataHoraInsert()]);
        }
        $busca->whereFuncionarioId($ID_USUARIO);
        $registro = $busca->first();
        if($registro){
            return $registro->acao ==PontoEletronico::ENTRADA ? PontoEletronico::SAIDA:PontoEletronico::ENTRADA;
        }else{
            return PontoEletronico::ENTRADA;
        }
    }*/

    public static function getJornadaAtual($ID_ESCALA, $data_futura = null) {
        $escala = EmpresaEscala::find($ID_ESCALA);
        if ($escala) {
            $incio = new DataHora($escala->inicio);
            if ($data_futura) {
                $hoje = new DataHora($data_futura . ' 23:59:59');
            } else {
                $hoje = new DataHora();
            }
            $jornadasBase = $escala->jornadas;
            $jornadas = [];
            foreach ($jornadasBase as $j) {
                for ($i = 1; $i <= $j->repetir; $i++) {
                    $j->load('Periodos');
                    $jornadas[] = $j;
                }
            }
            $jornadaIndex = 0;

            while (DataHora::distanciaTempo($incio->dataHoraInsert(), $hoje->dataHoraInsert())['totalDias'] > 0) {
                $incio->addDia(1);
                if (isset($jornadas[$jornadaIndex + 1])) {
                    $jornadaIndex++;
                } else {
                    $jornadaIndex = 0;
                }
            }
            /*if($data_alvo){ // mais alguns dias
                $data_alvo = new DataHora($data_alvo.' 23:59:59');
                while (DataHora::distanciaTempo($incio->dataHoraInsert(), $data_alvo->dataHoraInsert())['totalDias'] > 0) {
                    $incio->addDia(1);
                    if (isset($jornadas[$jornadaIndex + 1])) {
                        $jornadaIndex++;
                    } else {
                        $jornadaIndex = 0;
                    }
                }
            }*/
            return $jornadas[$jornadaIndex];
        }
        return null;

    }

    public function getDiaAttribute() {
        return $this->created_at->format('d/m/Y');
    }

    public function getDiaSemanaAttribute() {
        $dia = new DataHora($this->created_at->format('d/m/Y'));
        return $dia->diaSemanaExtM();
    }

    public function getDiaSemAttribute() {
        $dia = new DataHora($this->created_at->format('d/m/Y'));
        return mb_substr($dia->diaSemanaExtM(), 0, 3);
    }

    //DuracaoJornada Original ------------------------------
    public function getDuracaoJornadaOriginalAttribute() {
        $inicio = null;
        $fim = null;
        $total = 0;
        $dados = [
            "ano" => 0,
            "mes" => 0,
            "dia" => 0,
            "hora" => 0,
            "minuto" => 0,
            "segundo" => 0,
            "totalDias" => 0,
            'total_minutos' => 0
        ];
        foreach ($this->Jornada->Periodos as $index => $periodo) {
            $inicio = new DataHora($periodo->entrada);
            $fim = new DataHora($periodo->saida);
            $duracao = DataHora::distanciaTempo($inicio->dataHoraInsert(), $fim->dataHoraInsert());

            $dados['ano'] += $duracao['ano'];
            $dados['mes'] += $duracao['mes'];
            $dados['dia'] += $duracao['dia'];
            $dados['hora'] += $duracao['hora'];
            $dados['minuto'] += $duracao['minuto'];
            $dados['segundo'] += $duracao['segundo'];
            $dados['totalDias'] += $duracao['totalDias'];
            $dados['total_minutos'] += DataHora::diferencaMinutos($inicio->dataHoraInsert(), $fim->dataHoraInsert());

        }

        return $dados;
    }

    public function getHorasNormalOriginalAttribute() {
        $total = 0;
        $total += $this->duracao_jornada_original['total_minutos'];
        return $total;
    }

    public function getHorasNormalOriginalFormatAttribute() {
        $data = new DataHora();
        $data->setMinuto(0);
        $data->setHora(0);
        $data->addMinuto(abs($this->horas_normal_original));
        return "{$data->hora()}h:{$data->minuto()}m";


    }

    //DuracaoJornada realizada------------------------------
    public function getDuracaoJornadaAttribute() {
        $inicio = null;
        $fim = null;
        $total = 0;
        $dados = [
            "ano" => 0,
            "mes" => 0,
            "dia" => 0,
            "hora" => 0,
            "minuto" => 0,
            "segundo" => 0,
            "totalDias" => 0,
            'total_minutos' => 0,
            'total_minutos_extra' => 0,
            'total_minutos_noturno' => 0,
        ];
        foreach ($this->Periodos as $index => $periodo) {
            $inicio = new DataHora($periodo->entrada);
            $fim = new DataHora($periodo->saida);
            $duracao = DataHora::distanciaTempo($inicio->dataHoraInsert(), $fim->dataHoraInsert());


            $dados['ano'] += $duracao['ano'];
            $dados['mes'] += $duracao['mes'];
            $dados['dia'] += $duracao['dia'];
            $dados['hora'] += $duracao['hora'];
            $dados['minuto'] += $duracao['minuto'];
            $dados['segundo'] += $duracao['segundo'];
            $dados['totalDias'] += $duracao['totalDias'];
            $dados['total_minutos'] += DataHora::diferencaMinutos($inicio->dataHoraInsert(), $fim->dataHoraInsert());

            //Verificando se tem horas noturnas ----------------------------------------------------------------------
            $inicioNoturnoLei = Carbon::create($inicio->dataInsert() . ' 22:00:00');
            $inicio->setHora(23);
            $inicio->setMinuto(59);
            $inicio->setSegundo(59);
            $inicio->addSegundo(1);
            $inicio->addHora(5);
            $fimNoturnoLei = Carbon::create($inicio->dataHoraInsert());

            $inicio = new DataHora($periodo->entrada);
            $fim = new DataHora($periodo->saida);
            $inicioNoturno = Carbon::create($inicio->dataHoraInsert());
            $fimNoturno = Carbon::create($fim->dataHoraInsert());
            //se o fim esta terminando dentro do intervalo noturno

            $inicioDentroNoturno = $inicioNoturno->between($inicioNoturnoLei, $fimNoturnoLei);
            $fimDentroNoturno = $fimNoturno->between($inicioNoturnoLei, $fimNoturnoLei);
            //inicia normal, termina dentro do noturno
            if (!$inicioDentroNoturno && $fimDentroNoturno) { // somente o fim dentro noturno
                $dados['total_minutos_noturno'] += DataHora::diferencaMinutos($inicioNoturnoLei->format('d/m/Y H:i:s'), $fim->dataHoraInsert());
            }
            //inicia e termina no noturno
            if ($inicioDentroNoturno && $fimDentroNoturno) { // iniciou e termindou dentro do noturno
                //dd($inicio->dataHoraInsert(),$fim->dataHoraInsert());
                $dados['total_minutos_noturno'] += DataHora::diferencaMinutos($inicio->dataHoraInsert(), $fim->dataHoraInsert());
            }
            //inicia noturno e termina normal
            if ($inicioDentroNoturno && !$fimDentroNoturno) { // iniciou no noturno e termindou fora do noturno
                $dados['total_minutos_noturno'] += DataHora::diferencaMinutos($inicio->dataHoraInsert(), $fimDentroNoturno->format('d/m/Y H:i:s'));
            }
            //inicio e o fim é muito maior que a duração noturna inteira
            if ($inicioNoturnoLei->between($periodo->entrada, $periodo->saida, false) && $fimNoturnoLei->between($periodo->entrada, $periodo->saida, false)) {
                $dados['total_minutos_noturno'] += DataHora::diferencaMinutos($inicioNoturnoLei->format('d/m/Y H:i:s'), $fimNoturnoLei->format('d/m/Y H:i:s'));
            }


        }

        //descontar horas noturnas
        if ($dados['total_minutos_noturno'] > 0) {
            $dados['total_minutos'] -= $dados['total_minutos_noturno'];
        }


        //descontar horas extras
        $dados['total_minutos_extra'] = $dados['total_minutos'] - $this->duracao;
        if ($dados['total_minutos_extra'] > 0) {
            $dados['total_minutos'] -= $dados['total_minutos_extra'];
        }
        //dd($dados);
        return $dados;
    }

    public function getHorasNormalAttribute() {
        $total = 0;
        $total += $this->duracao_jornada['total_minutos'];
        return $total;
    }

    public function getHorasNormalFormatAttribute() {
        $data = new DataHora();
        $dataTeste = new DataHora();
        $data->setMinuto(0);
        $data->setHora(0);
        $data->setSegundo(0);
        $data->addMinuto(abs($this->horasNormal));
        return "{$data->hora()}h:{$data->minuto()}m";
        //return DataHora::distanciaTempo($dataTeste->dataHoraInsert(), $data->dataHoraInsert());
        /*$dados = DataHora::distanciaTempo($dataTeste->dataHoraInsert(), $data->dataHoraInsert());
        $saida = '';
        $saida .= abs($dados['dia']) > 0 ? abs($dados['dia']).'d ':'';
        $saida .= abs($dados['hora']) > 0 ? abs($dados['hora']).'h ':'';
        $saida .= abs($dados['minuto']) > 0 ? abs($dados['minuto']).'m ':'';
        return trim($saida);*/

        /*$data = Carbon::now();
        $data->addMinutes(abs($this->horasNormal));
        $data->addSecond();
        return $data->diffForHumans([
            'options' => Carbon::ROUND ,
            'parts' => 3,
            'short' => true,
            'sintax' => CarbonInterface::DIFF_ABSOLUTE,
            //'join' => true,
            //'aUnit' => true,
        ],CarbonInterface::DIFF_ABSOLUTE);*/
    }

    public function getTotalMinutosAttribute() {
        $total = 0; // em minutos
        foreach ($this->Periodos as $periodo) {
            $inicio = new DataHora($periodo->entrada);
            $fim = new DataHora($periodo->saida);
            $total += DataHora::diferencaMinutos($inicio->dataHoraInsert(), $fim->dataHoraInsert());
        }
        return $total;
    }


    public function getHorasExtraAttribute() {
        $total = 0;
        //$total+=  $this->duracao_jornada['total_minutos'] - $this->duracao;
        $total += $this->duracao_jornada['total_minutos_extra'];
        return $total;
    }

    public function getHorasExtraFormatAttribute() {
        $data = new DataHora();
        $dataTeste = new DataHora();
        $data->setMinuto(0);
        $data->setHora(0);
        $data->setSegundo(0);
        $data->addMinuto(abs($this->horasExtra));

        /*$dados = DataHora::distanciaTempo($dataTeste->dataHoraInsert(), $data->dataHoraInsert());
        $saida = '';
        $saida .= abs($dados['dia']) > 0 ? abs($dados['dia']).'d ':'';
        $saida .= abs($dados['hora']) > 0 ? abs($dados['hora']).'h ':'';
        $saida .= abs($dados['minuto']) > 0 ? abs($dados['minuto']).'m ':'';
        return trim($saida);*/
        if ($this->horasExtra < 0) {
            return "{$data->hora()}h:{$data->minuto()}m";
            //return "00h:00m";
        } else {
            return "{$data->hora()}h:{$data->minuto()}m";
        }

        /*$data = Carbon::now();
        $data->addMinutes(abs($this->horasExtra));
        $data->addSecond();
        return $data->diffForHumans([
            'options' => Carbon::ROUND ,
            'parts' => 3,
            'short' => true,
            'sintax' => CarbonInterface::DIFF_ABSOLUTE,
            //'join' => true,
            //'aUnit' => true,
        ],CarbonInterface::DIFF_ABSOLUTE);*/


    }

    public function getHorasNoturnaAttribute() {
        $total = 0;
        $total += $this->duracao_jornada['total_minutos_noturno'];
        return $total;
    }

    public function getHorasNoturnaFormatAttribute() {
        $data = new DataHora();
        $data->setMinuto(0);
        $data->setHora(0);
        $data->addMinuto(abs($this->horasNoturna));
        if ($this->horasNoturna < 0) {
            return "{$data->hora()}h:{$data->minuto()}m";
            //return "00h:00m";
        } else {
            return "{$data->hora()}h:{$data->minuto()}m";
        }
        /*$data = Carbon::now();
        $data->addMinutes(abs($this->horasNoturna));
        $data->addSecond();
        return $data->diffForHumans([
            'options' => Carbon::ROUND ,
            'parts' => 3,
            'short' => true,
            'sintax' => CarbonInterface::DIFF_ABSOLUTE,
            //'join' => true,
            //'aUnit' => true,
        ],CarbonInterface::DIFF_ABSOLUTE);*/


    }

    //--------------------------------------------------------

    /*public static function getTotalHoras($array_periodos) {
        $total = 0; // em minutos
        foreach ($array_periodos as $periodo) {
            $inicio = new DataHora($periodo->entrada);
            $fim = new DataHora($periodo->saida);
            $total += DataHora::diferencaMinutos($inicio->dataHoraInsert(), $fim->dataHoraInsert());
        }
        return $total;
    }

    public static function getTotalSegundos($array_periodos) {
        $total = 0; // em minutos
        foreach ($array_periodos as $periodo) {
            $inicio = new DataHora($periodo->entrada);
            $fim = new DataHora($periodo->saida);
            $total += DataHora::diferencaSegundos($inicio->dataHoraInsert(), $fim->dataHoraInsert());
        }
        return $total;
    }

    public static function getTotalMinutos($array_periodos) {
        $total = 0; // em minutos
        foreach ($array_periodos as $periodo) {
            $inicio = new DataHora($periodo->entrada);
            $fim = new DataHora($periodo->saida);
            $total += DataHora::diferencaMinutos($inicio->dataHoraInsert(), $fim->dataHoraInsert());
        }
        return $total;
    }*/
    public static function formataTempo($quantidadeMinutos) {

        $data = Carbon::now();
        $data->setSecond(0);
        $dataComparar = $data->copy();
        $data->addMinutes($quantidadeMinutos);
        $dataComparar->setSecond(0);

        $objeto = $data->diffAsCarbonInterval($dataComparar);
        $horas = $objeto->d > 0 ? $objeto->d * 24 : 0;
        $horas += $objeto->h;
        $minutos = $objeto->i;
        //$horas = $horas < 10 ? "0".$horas:$horas;
        $minutos = $minutos < 10 ? "0" . $minutos : $minutos;
        return "{$horas}h:$minutos:m";

    }

    public static function duracaoJornada(EscalaJornada $jornada) {
        $inicio = null;
        $fim = null;
        $total = 0;
        $dados = [
            "ano" => 0,
            "mes" => 0,
            "dia" => 0,
            "hora" => 0,
            "minuto" => 0,
            "segundo" => 0,
            "totalDias" => 0,
            'total_minutos' => 0
        ];
        foreach ($jornada->Periodos as $index => $periodo) {
            $inicio = new DataHora($periodo->entrada);
            $fim = new DataHora($periodo->saida);
            $duracao = DataHora::distanciaTempo($inicio->dataHoraInsert(), $fim->dataHoraInsert());

            $dados['ano'] += $duracao['ano'];
            $dados['mes'] += $duracao['mes'];
            $dados['dia'] += $duracao['dia'];
            $dados['hora'] += $duracao['hora'];
            $dados['minuto'] += $duracao['minuto'];
            $dados['segundo'] += $duracao['segundo'];
            $dados['totalDias'] += $duracao['totalDias'];
            $dados['total_minutos'] += DataHora::diferencaMinutos($inicio->dataHoraInsert(), $fim->dataHoraInsert());

        }

        return $dados;
    }

    /*public function Escala(){
        return $this->hasOne(EmpresaEscala::class,'id','escala_id');
    }

    public function Periodo(){
        return $this->hasOne(PeriodoJornada::class,'id','periodo_id');
    }*/
}
