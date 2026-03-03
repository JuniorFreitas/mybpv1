<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;
use phpDocumentor\Reflection\Types\Self_;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;

/**
 * App\Models\NotificacaoWhatsapp
 *
 * @property int $id
 * @property int $user_id
 * @property string $telefone
 * @property int $messageid
 * @property int $enviado_id
 * @property string $mensagem
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $EnviadoPor
 * @property-read \App\Models\User $User
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|NotificacaoWhatsapp newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NotificacaoWhatsapp newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NotificacaoWhatsapp query()
 * @method static \Illuminate\Database\Eloquent\Builder|NotificacaoWhatsapp whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NotificacaoWhatsapp whereEnviadoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NotificacaoWhatsapp whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NotificacaoWhatsapp whereMensagem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NotificacaoWhatsapp whereMessageid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NotificacaoWhatsapp whereTelefone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NotificacaoWhatsapp whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NotificacaoWhatsapp whereUserId($value)
 * @mixin \Eloquent
 */
class NotificacaoWhatsapp extends Model
{
    use HasFactory, LogsActivity, HasActivitylogOptions;

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
