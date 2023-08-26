<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\ChecklistsTarefa
 *
 * @property int $id
 * @property int $tarefa_id
 * @property string $titulo
 * @property int $ordem
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ChecklistsTarefaItem> $Itens
 * @property-read int|null $itens_count
 * @property-read \App\Models\Tarefa|null $Tarefa
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|ChecklistsTarefa newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChecklistsTarefa newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChecklistsTarefa query()
 * @method static \Illuminate\Database\Eloquent\Builder|ChecklistsTarefa whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChecklistsTarefa whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChecklistsTarefa whereOrdem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChecklistsTarefa whereTarefaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChecklistsTarefa whereTitulo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChecklistsTarefa whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ChecklistsTarefa extends Model
{
    use HasFactory,LogsActivity;
    protected static $logFillable = true;
    protected static $logName = 'ChecklistTarefa';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;
    public $timestamps=true;
    protected $table = 'checklists_tarefas';
    protected $fillable = [
        'tarefa_id',
        'titulo' ,
        'ordem' ,
        'created_at' ,
        'updated_at' ,
    ];
    protected $casts = [
        'id' => 'int',
        'tarefa_id' => 'int',
        'titulo' => 'string',
        'ordem' => 'int',
        'created_at' => 'datetime:d/m/Y à\s H:i:s',
        'updated_at' => 'datetime:d/m/Y à\s H:i:s',
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

    public function Itens(){
        return $this->hasMany(ChecklistsTarefaItem::class,'checklist_id','id')->orderBy('ordem');
    }

    public function Tarefa(){
        return $this->hasOne(Tarefa::class,'id','tarefa_id');
    }
}
