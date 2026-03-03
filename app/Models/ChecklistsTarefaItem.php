<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ChecklistsTarefa|null $CheckList
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|ChecklistsTarefaItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChecklistsTarefaItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChecklistsTarefaItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|ChecklistsTarefaItem whereChecklistId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChecklistsTarefaItem whereConcluido($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChecklistsTarefaItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChecklistsTarefaItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChecklistsTarefaItem whereOrdem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChecklistsTarefaItem whereTitulo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChecklistsTarefaItem whereUpdatedAt($value)
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
    ];
    protected $casts = [
        'id' => 'int',
        'checklist_id' => 'int',
        'titulo' => 'string',
        'concluido' => 'boolean',
        'ordem' => 'int',
        'created_at' => 'datetime:d/m/Y à\s H:i:s',
        'updated_at' => 'datetime:d/m/Y à\s H:i:s',
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

    public function CheckList(){
        return $this->hasOne(ChecklistsTarefa::class,'id','checklist_id');
    }
}
