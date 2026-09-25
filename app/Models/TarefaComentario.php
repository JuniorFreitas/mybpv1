<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;

class TarefaComentario extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, HasActivitylogOptions;

    public const TIPO_COMENTARIO = 'comentario';
    public const TIPO_BLOQUEIO = 'bloqueio';
    public const TIPO_DEPENDENCIA = 'dependencia';

    public const TIPOS = [
        self::TIPO_COMENTARIO,
        self::TIPO_BLOQUEIO,
        self::TIPO_DEPENDENCIA,
    ];

    protected static $logFillable = true;
    protected static $logName = 'TarefaComentario';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    protected $table = 'tarefas_comentarios';

    protected $fillable = [
        'tarefa_id',
        'user_id',
        'comentario',
        'tipo',
    ];

    protected $casts = [
        'id' => 'int',
        'tarefa_id' => 'int',
        'user_id' => 'int',
        'comentario' => 'string',
        'tipo' => 'string',
        'created_at' => 'datetime:d/m/Y à\s H:i:s',
        'updated_at' => 'datetime:d/m/Y à\s H:i:s',
        'deleted_at' => 'datetime:d/m/Y à\s H:i:s',
    ];

    protected $with = ['Usuario'];

    protected $appends = [
        'tipo_label',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (!$model->user_id) {
                $model->user_id = auth()->id();
            }
            if (!$model->tipo) {
                $model->tipo = self::TIPO_COMENTARIO;
            }
        });
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

    public function getTipoLabelAttribute(): string
    {
        return match ($this->tipo) {
            self::TIPO_BLOQUEIO => 'Bloqueio',
            self::TIPO_DEPENDENCIA => 'Dependência externa',
            default => 'Comentário',
        };
    }

    public function Tarefa()
    {
        return $this->belongsTo(Tarefa::class, 'tarefa_id', 'id');
    }

    public function Usuario()
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->select(['id', 'nome']);
    }
}
