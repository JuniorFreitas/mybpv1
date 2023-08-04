<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\LogTarefa
 *
 * @property int $id
 * @property int $tarefa_id
 * @property int $lista_anterior
 * @property int $lista_atual
 * @property \datetime|null $created_at
 * @property \datetime|null $updated_at
 * @property-read \App\Models\ListaTarefa|null $ListaAnterior
 * @property-read \App\Models\ListaTarefa|null $ListaAtual
 * @property-read \App\Models\Tarefa|null $Tarefa
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|LogTarefa newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LogTarefa newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LogTarefa query()
 * @method static \Illuminate\Database\Eloquent\Builder|LogTarefa whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogTarefa whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogTarefa whereListaAnterior($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogTarefa whereListaAtual($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogTarefa whereTarefaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogTarefa whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class LogTarefa extends Model
{
    use HasFactory, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'LogTarefas';
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

    protected $table = 'log_tarefas';

    protected $fillable = [
        'tarefa_id',
        'lista_anterior',
        'lista_atual',
    ];

    protected $casts = [
        'id' => 'int',
        'tarefa_id' => 'int',
        'lista_anterior' => 'int',
        'lista_atual' => 'int',
        'created_at' => 'datetime:d/m/Y à\s H:i:s',
        'updated_at' => 'datetime:d/m/Y à\s H:i:s',
    ];

    protected function serializeDate(DateTimeInterface $date) {
        return $date->format('Y-m-d H:i:s');
    }

    public $timestamps=false;

    public function ListaAnterior(){
        return $this->hasOne(ListaTarefa::class,'id','lista_anterior');
    }
    public function ListaAtual(){
        return $this->hasOne(ListaTarefa::class,'id','lista_atual');
    }

    public function Tarefa(){
        return $this->hasOne(Tarefa::class,'id','tarefa_id');
    }
}
