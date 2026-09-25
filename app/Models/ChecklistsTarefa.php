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
 * App\Models\ChecklistsTarefa
 *
 * @property int $id
 * @property int $tarefa_id
 * @property string $titulo
 * @property int $ordem
 * @property \Illuminate\Support\Carbon|null $datahora_entrega
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $datahora_entrega_br
 * @property-read mixed $em_atraso
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ChecklistsTarefaItem> $Itens
 * @property-read int|null $itens_count
 * @property-read \App\Models\Tarefa|null $Tarefa
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @mixin \Eloquent
 */
class ChecklistsTarefa extends Model
{
    use HasFactory,LogsActivity, HasActivitylogOptions;
    protected static $logFillable = true;
    protected static $logName = 'ChecklistTarefa';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;
    public $timestamps=true;
    protected $table = 'checklists_tarefas';
    protected $fillable = [
        'tarefa_id',
        'titulo',
        'ordem',
        'datahora_entrega',
        'created_at',
        'updated_at',
    ];
    protected $casts = [
        'id' => 'int',
        'tarefa_id' => 'int',
        'titulo' => 'string',
        'ordem' => 'int',
        'datahora_entrega' => 'datetime:d/m/Y à\s H:i',
        'created_at' => 'datetime:d/m/Y à\s H:i:s',
        'updated_at' => 'datetime:d/m/Y à\s H:i:s',
    ];

    protected $appends = [
        'datahora_entrega_br',
        'em_atraso',
    ];

    protected function serializeDate(DateTimeInterface $date) {
        return $date->format('Y-m-d H:i:s');
    }
    protected $with=[
        'Itens'
    ];

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = "";
    }

    public function getDatahoraEntregaBrAttribute()
    {
        if (!$this->datahora_entrega) {
            return null;
        }
        $datahora = new DataHora($this->datahora_entrega);

        return $datahora->dataCompleta() . ' às ' . $datahora->hora() . ':' . $datahora->minuto();
    }

    public function getEmAtrasoAttribute()
    {
        if (!$this->datahora_entrega) {
            return false;
        }
        $agora = new DataHora();
        $entrega = new DataHora($this->datahora_entrega);

        return (int) $agora->toTimeStamp() > (int) $entrega->toTimeStamp();
    }

    public function Itens(){
        return $this->hasMany(ChecklistsTarefaItem::class,'checklist_id','id')
            ->with('Membros')
            ->orderBy('ordem');
    }

    public function Tarefa(){
        return $this->hasOne(Tarefa::class,'id','tarefa_id');
    }
}
