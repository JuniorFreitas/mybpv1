<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;

/**
 * App\Models\ListaTarefa
 *
 * @property int $id
 * @property int $quadro_id
 * @property int $user_id
 * @property string $titulo
 * @property int $ordem
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Quadro|null $Quadro
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Tarefa> $Tarefas
 * @property-read int|null $tarefas_count
 * @property-read \App\Models\User|null $Usuario
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|ListaTarefa newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ListaTarefa newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ListaTarefa query()
 * @method static \Illuminate\Database\Eloquent\Builder|ListaTarefa whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ListaTarefa whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ListaTarefa whereOrdem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ListaTarefa whereQuadroId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ListaTarefa whereTitulo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ListaTarefa whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ListaTarefa whereUserId($value)
 * @mixin \Eloquent
 */
class ListaTarefa extends Model
{
    use HasFactory, LogsActivity, HasActivitylogOptions;

    protected static $logFillable = true;
    protected static $logName = 'ListaTarefas';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;
    public $timestamps=true;
    protected $table = 'lista_tarefas';
    protected $fillable = [
        'titulo',
        'quadro_id',
        'ordem',
    ];
    protected $casts = [
        'id' => 'int',
        'titulo' => 'string',
        'quadro_id' => 'int',
        'user_id' => 'int',
        'created_at' => 'datetime:d/m/Y à\s H:i:s',
        'updated_at' => 'datetime:d/m/Y à\s H:i:s',
    ];

    protected function serializeDate(DateTimeInterface $date) {
        return $date->format('Y-m-d H:i:s');
    }


    protected $with=[
        'Tarefas.Membros',
        'Tarefas.Checklists.Itens',
        //'Tarefas.Logs',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->user_id = auth()->user()->id;
        });
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = "";
    }

    public function Quadro(){
        return $this->hasOne(Quadro::class,'id','quadro_id');
    }

    public function Usuario(){
        return $this->hasOne(User::class,'id','user_id');
    }

    public function Tarefas(){
        return $this->hasMany(Tarefa::class,'lista_id','id')->orderBy('ordem');
    }
}
