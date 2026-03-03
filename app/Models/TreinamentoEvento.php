<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;

/**
 * App\Models\TreinamentoEvento
 *
 * @property int $id
 * @property int $cliente_id
 * @property int $treinamento_sgi_id
 * @property int $empresa_treinamento_id
 * @property \Illuminate\Support\Carbon $data_inicio
 * @property \Illuminate\Support\Carbon $data_fim
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Cliente|null $Cliente
 * @property-read \App\Models\EmpresaTreinamento|null $EmpresaTreinamento
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Instrutor> $InstrutoresEvento
 * @property-read int|null $instrutores_evento_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PessoaEmpresa> $PessoasEvento
 * @property-read int|null $pessoas_evento_count
 * @property-read \App\Models\TreinamentoSgi|null $TreinamentoSgi
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoEvento newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoEvento newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoEvento query()
 * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoEvento whereClienteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoEvento whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoEvento whereDataFim($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoEvento whereDataInicio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoEvento whereEmpresaTreinamentoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoEvento whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoEvento whereTreinamentoSgiId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoEvento whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TreinamentoEvento extends Model
{
    use HasFactory, LogsActivity, HasActivitylogOptions;

    protected static $logFillable = true;
    protected static $logName = 'TreinamentoEventos';
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

    protected $table = 'treinamento_eventos';

    protected $fillable = [
        'cliente_id',
        'treinamento_sgi_id',
        'empresa_treinamento_id',
        'data_inicio',
        'data_fim',
    ];

    protected $casts = [
        'cliente_id' => 'int',
        'treinamento_sgi_id' => 'int',
        'empresa_treinamento_id' => 'int',
        'data_inicio' => 'date:d/m/Y h:i',
        'data_fim' => 'date:d/m/Y h:i',
    ];

    //Acessor ->data_inicio
    public function getDataInicioAttribute($value)
    {
        if ($value) {
            $data = new DataHora($this->attributes['data_inicio']);
            return $data->dataCompleta() . ' às ' . $data->hora().':'.$data->minuto();
        }
    }

    //Modificador ->data_inicio
    public function setDataInicioAttribute($value)
    {
        if ($value) {
            $dt = explode(' às ', $value);
            $data = new DataHora($dt[0] . ' ' . $dt[1] . ':00');
            $this->attributes['data_inicio'] = $data->dataHoraInsert();
        }
    }

    //Acessor ->data_fim
    public function getDataFimAttribute($value)
    {
        if ($value) {
            $data = new DataHora($this->attributes['data_fim']);
            return $data->dataCompleta() . ' às ' . $data->hora().':'.$data->minuto();
        }
    }

    //Modificador ->data_fim
    public function setDataFimAttribute($value)
    {
        if ($value) {
            $dt = explode(' às ', $value);
            $data = new DataHora($dt[0] . ' ' . $dt[1] . ':00');
            $this->attributes['data_fim'] = $data->dataHoraInsert();
        }
    }

    public function Cliente()
    {
        return $this->hasOne(Cliente::class, 'id', 'cliente_id');
    }

    public function TreinamentoSgi()
    {
        return $this->hasOne(TreinamentoSgi::class, 'id', 'treinamento_sgi_id');
    }

    public function EmpresaTreinamento()
    {
        return $this->hasOne(EmpresaTreinamento::class, 'id', 'empresa_treinamento_id');
    }

    public function InstrutoresEvento()
    {
        return $this->belongsToMany(Instrutor::class, 'instrutor_treinamento_evento', 'treinamento_evento_id', 'instrutor_id');
    }

    public function PessoasEvento()
    {
        return $this->belongsToMany(PessoaEmpresa::class, 'pessoa_evento', 'treinamento_evento_id', 'pessoa_treinamento_id')->withPivot(['nota']);
    }

//    public function scopeEmpresa($query)
//    {
//        if (auth()->user()->cliente_id !== User::BPSE) {
//            return $query->where('cliente_id', auth()->user()->cliente_id);
//        }
//    }
}
