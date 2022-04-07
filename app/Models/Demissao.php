<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class Demissao extends Model
{
    use HasFactory, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'demissão';
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

    protected $fillable = [
        'feedback_id',
        'cipa',
        'data_desmobilizacao',
        'motivo_rescisao_id',
        'tipo_aviso_id',
        'solicitado_por',
        'comentario',
        'user_id',
    ];

    protected $casts = [
        'id' => 'int',
        'feedback_id' => 'int',
        'cipa' => 'boolean',
        'data_desmobilizacao' => 'date:d/m/Y',
        'motivo_rescisao_id' => 'int',
        'tipo_aviso_id' => 'int',
        'solicitado_por' => 'string',
        'comentario' => 'string',
        'user_id' => 'int',
    ];


    public function setDataDesmobilizacaoAttribute($value)
    {
        if (!is_null($value)) {
            $data = new DataHora($value);
            $this->attributes['data_desmobilizacao'] = $data->dataInsert();
        } else {
            $this->attributes['data_desmobilizacao'] = null;
        }
    }

    public function Feedback()
    {
        return $this->belongsTo(\App\Models\FeedbackCurriculo::class, 'feedback_id');
    }

    public function motivoRescisao()
    {
        return $this->belongsTo(\App\Models\MotivoRescisao::class, 'motivo_rescisao_id');
    }

    public function tipoAviso()
    {
        return $this->belongsTo(\App\Models\TipoAviso::class, 'tipo_aviso_id');
    }

    public function User()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->user_id = auth()->id();
        });

        static::updating(function ($model) {
            $model->user_id = auth()->id();
        });
    }
}
