<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;

/**
 * App\Models\Tarefa
 *
 * @property int $id
 * @property int $lista_id
 * @property int $user_id
 * @property string $titulo
 * @property string|null $descricao
 * @property int $ordem
 * @property \Illuminate\Support\Carbon|null $datahora_inicio
 * @property \Illuminate\Support\Carbon|null $datahora_entrega
 * @property string|null $lembrete
 * @property bool $concluido
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $Anexos
 * @property-read int|null $anexos_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ChecklistsTarefa> $Checklists
 * @property-read int|null $checklists_count
 * @property-read \App\Models\ListaTarefa|null $Lista
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LogWeekly> $Logs
 * @property-read int|null $logs_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $Membros
 * @property-read int|null $membros_count
 * @property-read \App\Models\User|null $Usuario
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read mixed $data_hora_entrega_formatada
 * @property-read mixed $data_hora_inicio_formatada
 * @property-read mixed $em_atraso
 * @property-read mixed $lembrete_text
 * @method static \Illuminate\Database\Eloquent\Builder|Tarefa newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tarefa newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tarefa query()
 * @method static \Illuminate\Database\Eloquent\Builder|Tarefa whereConcluido($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tarefa whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tarefa whereDatahoraEntrega($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tarefa whereDatahoraInicio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tarefa whereDescricao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tarefa whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tarefa whereLembrete($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tarefa whereListaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tarefa whereOrdem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tarefa whereTitulo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tarefa whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tarefa whereUserId($value)
 * @mixin \Eloquent
 */
class Tarefa extends Model {
    use HasFactory, LogsActivity, HasActivitylogOptions;

    protected static $logFillable = true;
    protected static $logName = 'Tarefa';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    public function getDescriptionForEvent(string $eventName): string {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName) {
        $activity->descricao = "";
    }

    protected $table = 'tarefas';

    protected $fillable = [
        'lista_id',
        'titulo',
        'descricao',
        'datahora_inicio',
        'datahora_entrega',
        'lembrete',
        'ordem',
        'concluido',
    ];

    protected $casts = [
        'id' => 'int',
        'lista_id' => 'int',

        'user_id' => 'int',
        'titulo' => 'string',
        'descricao' => 'string',
        'ordem' => 'int',
        'datahora_inicio' => 'datetime:d/m/Y à\s H:i',
        'datahora_entrega' => 'datetime:d/m/Y à\s H:i',
        'lembrete' => 'string',
        'concluido' => 'boolean',
        'created_at' => 'datetime:d/m/Y à\s H:i:s',
        'updated_at' => 'datetime:d/m/Y à\s H:i:s',
    ];

    protected function serializeDate(DateTimeInterface $date) {
        return $date->format('Y-m-d H:i:s');
    }

    protected $with = [
        'Membros',
        'Anexos',
        'Checklists.Itens',
        //'Logs'
    ];

    protected $appends = [
        'emAtraso',
        'lembreteText'
    ];

    public $timestamps = true;

    protected static function booted() {
        static::creating(function ($model) {
            $model->user_id = auth()->user()->id;
        });
    }

    public function Lista() {
        return $this->hasOne(ListaTarefa::class, 'id', 'lista_id');
    }

    public function Membros() {
        return $this->belongsToMany(User::class, 'membros_tarefa', 'tarefa_id', 'user_id');
    }

    public function Checklists() {
        return $this->hasMany(ChecklistsTarefa::class, 'tarefa_id', 'id')->orderBy('ordem');
    }

    public function Usuario() {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function Anexos() {
        return $this->belongsToMany(Arquivo::class, 'tarefa_anexos', 'tarefa_id', 'arquivo_id');
    }

    public function Logs() {
        return $this->hasMany(LogWeekly::class, 'tarefa_id', 'id')
            ->with('Usuario:id,nome')->take(5)
            ->orderByDesc('created_at');
    }

    public function getDataHoraInicioFormatadaAttribute() {
        if ($this->datahora_inicio) {
            $datahora = new DataHora($this->datahora_inicio);
            return $datahora->dataCompleta() . ' às ' . $datahora->horaCompleta();
        }
        return null;

    }

    public function setLembreteAttribute($value) {
        if($value==null){
            $this->attributes['lembrete']=null;
            return $this->attributes['lembrete'];
        }
        if ($this->datahora_entrega) {
            $dataLembrete = new DataHora($this->datahora_entrega);
            $dataLembrete->setSegundo(0);
            switch ($value){
                case '5m':
                    $dataLembrete->subtrairMinuto(5);
                    $this->attributes['lembrete'] = $dataLembrete->dataHoraInsert();
                    break;
                case '10m':
                    $dataLembrete->subtrairMinuto(10);
                    $this->attributes['lembrete'] = $dataLembrete->dataHoraInsert();
                    break;
                case '15m':
                    $dataLembrete->subtrairMinuto(15);
                    $this->attributes['lembrete'] = $dataLembrete->dataHoraInsert();
                    break;
                case '1H':
                    $dataLembrete->subtrairHora(1);
                    $this->attributes['lembrete'] = $dataLembrete->dataHoraInsert();
                    break;
                case '2H':
                    $dataLembrete->subtrairHora(2);
                    $this->attributes['lembrete'] = $dataLembrete->dataHoraInsert();
                    break;
                case '1d':
                    $dataLembrete->subtrairDia(1);
                    $this->attributes['lembrete'] = $dataLembrete->dataHoraInsert();
                    break;
                case '2d':
                    $dataLembrete->subtrairDia(2);
                    $this->attributes['lembrete'] = $dataLembrete->dataHoraInsert();
                    break;
                default:
                    /*$novo = new DataHora($value);
                    $this->attributes['lembrete'] = $novo->dataHoraInsert();*/
                    break;
            }
        }else{
            $this->attributes['lembrete']=null;
        }

    }

    public function getLembreteTextAttribute() {
        $lembrete = new DataHora($this->lembrete);
        $lembrete->setSegundo(0);
        if ($this->datahora_entrega) {
            //5 minutos
            $dataLembrete = new DataHora($this->datahora_entrega);
            $dataLembrete->setSegundo(0);
            $dataLembrete->subtrairMinuto(5);
            if ($dataLembrete->toTimeStamp() === $lembrete->toTimeStamp()) {
                return '5m';
            }
            //10 minutos
            $dataLembrete = new DataHora($this->datahora_entrega);
            $dataLembrete->setSegundo(0);
            $dataLembrete->subtrairMinuto(10);
            if ($dataLembrete->toTimeStamp() === $lembrete->toTimeStamp()) {
                return '10m';
            }
            //15 minutos
            $dataLembrete = new DataHora($this->datahora_entrega);
            $dataLembrete->setSegundo(0);
            $dataLembrete->subtrairMinuto(15);
            if ($dataLembrete->toTimeStamp() === $lembrete->toTimeStamp()) {
                return '15m';
            }
            //1 hora
            $dataLembrete = new DataHora($this->datahora_entrega);
            $dataLembrete->setSegundo(0);
            $dataLembrete->subtrairHora(1);
            if ($dataLembrete->toTimeStamp() === $lembrete->toTimeStamp()) {
                return '1H';
            }
            //2 hora
            $dataLembrete = new DataHora($this->datahora_entrega);
            $dataLembrete->setSegundo(0);
            $dataLembrete->subtrairHora(2);
            if ($dataLembrete->toTimeStamp() === $lembrete->toTimeStamp()) {
                return '2H';
            }
            //1 Dia
            $dataLembrete = new DataHora($this->datahora_entrega);
            $dataLembrete->setSegundo(0);
            $dataLembrete->subtrairDia(1);
            if ($dataLembrete->toTimeStamp() === $lembrete->toTimeStamp()) {
                return '1d';
            }
            //2 Dia
            $dataLembrete = new DataHora($this->datahora_entrega);
            $dataLembrete->setSegundo(0);
            $dataLembrete->subtrairDia(2);
            if ($dataLembrete->toTimeStamp() === $lembrete->toTimeStamp()) {
                return '2d';
            }
        }

        return null;

    }

    public function getDataHoraEntregaFormatadaAttribute() {
        if ($this->datahora_entrega) {
            $datahora = new DataHora($this->datahora_entrega);
            //return $datahora->dataCompleta() . ' às ' . $datahora->horaCompleta();
            return $datahora->dataCompleta() . ' às ' . $datahora->hora().":".$datahora->minuto();
        }
        return null;
    }

    public function getEmAtrasoAttribute() {

        if ($this->datahora_entrega) {
            $agora = new DataHora();
            $dataDeEntrega = new DataHora($this->datahora_entrega);
            if ((int)$agora->toTimeStamp() > (int)$dataDeEntrega->toTimeStamp()) {
                return true;
            } else {
                return false;
            }
        }
        return false;
    }


}
