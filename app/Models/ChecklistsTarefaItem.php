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
 * App\Models\ChecklistsTarefaItem
 *
 * @property int $id
 * @property int $checklist_id
 * @property string $titulo
 * @property bool $concluido
 * @property int $ordem
 * @property \Illuminate\Support\Carbon|null $datahora_entrega
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $datahora_entrega_br
 * @property-read mixed $em_atraso
 * @property-read \App\Models\ChecklistsTarefa|null $CheckList
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @mixin \Eloquent
 */
class ChecklistsTarefaItem extends Model
{
    use HasFactory,LogsActivity, HasActivitylogOptions;

    protected static $logFillable = true;
    protected static $logName = 'checklists_tarefa_items';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;
    public $timestamps=true;
    protected $table = 'checklists_tarefa_items';
    protected $fillable = [
        'checklist_id',
        'titulo',
        'concluido',
        'ordem',
        'datahora_entrega',
    ];
    protected $casts = [
        'id' => 'int',
        'checklist_id' => 'int',
        'titulo' => 'string',
        'concluido' => 'boolean',
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
        if (!$this->datahora_entrega || $this->concluido) {
            return false;
        }
        $agora = new DataHora();
        $entrega = new DataHora($this->datahora_entrega);

        return (int) $agora->toTimeStamp() > (int) $entrega->toTimeStamp();
    }

    public function CheckList(){
        return $this->hasOne(ChecklistsTarefa::class,'id','checklist_id');
    }

    public function Membros()
    {
        return $this->belongsToMany(
            User::class,
            'checklists_tarefa_items_membros',
            'checklists_tarefa_item_id',
            'user_id'
        )->select(['users.id', 'users.nome']);
    }
}
