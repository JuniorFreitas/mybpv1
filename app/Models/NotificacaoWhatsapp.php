<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;
use phpDocumentor\Reflection\Types\Self_;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class NotificacaoWhatsapp extends Model
{
    use HasFactory, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'NotificacaoWhatsapp';
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
        'user_id',
        'telefone',
        'messageid',
        'enviado_id',
        'mensagem',
    ];

    protected $casts = [
        'id' => 'int',
        'user_id' => 'int',
        'telefone' => 'string',
        'messageid' => 'int',
        'enviado_id' => 'int',
        'mensagem' => 'string',
    ];

    public function getCreatedAtAttribute($value)
    {
        $data = new DataHora($this->attributes['created_at']);
        return $data->dataCompleta() . ' ' . $data->hora() . ':' . $data->minuto() . 'h';
    }

    public function User()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function EnviadoPor()
    {
        return $this->belongsTo(\App\Models\User::class, 'enviado_id');
    }

}
