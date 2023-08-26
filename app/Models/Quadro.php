<?php

namespace App\Models;

use App\Scopes\ScopeEmpresa;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\Quadro
 *
 * @property int $id
 * @property int $user_id
 * @property string $titulo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $empresa_id
 * @property-read \App\Models\User|null $Empresa
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ListaTarefa> $Listas
 * @property-read int|null $listas_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LogWeekly> $Logs
 * @property-read int|null $logs_count
 * @property-read \App\Models\User|null $Usuario
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|Quadro newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Quadro newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Quadro query()
 * @method static \Illuminate\Database\Eloquent\Builder|Quadro whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Quadro whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Quadro whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Quadro whereTitulo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Quadro whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Quadro whereUserId($value)
 * @mixin \Eloquent
 */
class Quadro extends Model {
    use HasFactory, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'Quadros';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;
    public $timestamps = true;
    protected $table = 'quadros';
    protected $fillable = [
        'titulo',
    ];
    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'user_id' => 'int',
        'titulo' => 'string',
        'created_at' => 'datetime:d/m/Y à\s H:i:s',
        'updated_at' => 'datetime:d/m/Y à\s H:i:s',
    ];

    protected function serializeDate(DateTimeInterface $date) {
        return $date->format('Y-m-d H:i:s');
    }

    protected static function booted() {
        static::creating(function ($model) {
            $model->empresa_id = auth()->user()->empresa_id;
            $model->user_id = auth()->user()->id;
        });

        static::addGlobalScope(new ScopeEmpresa());
    }

    public function getDescriptionForEvent(string $eventName): string {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName) {
        $activity->descricao = "";
    }

    public function Empresa() {
        return $this->hasOne(User::class, 'id', 'empresa_id');
    }

    public function Usuario() {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function Listas() {
        return $this->hasMany(ListaTarefa::class, 'quadro_id', 'id'); // nao colocar orderBy('ordem')
    }
    public function Logs(){
        return $this->hasMany(LogWeekly::class,'quadro_id', 'id')->whereNull('tarefa_id')
            ->with('Usuario:id,nome')
            ->orderByDesc('created_at');
    }
}
