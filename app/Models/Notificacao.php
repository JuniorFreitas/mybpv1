<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Notificacao
 *
 * @property int $id
 * @property string $tipo
 * @property int $user_id
 * @property array $payload
 * @property bool $visto
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Notificacao newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Notificacao newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Notificacao query()
 * @method static \Illuminate\Database\Eloquent\Builder|Notificacao whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notificacao whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notificacao wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notificacao whereTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notificacao whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notificacao whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notificacao whereVisto($value)
 * @mixin \Eloquent
 */
class Notificacao extends Model
{
    use LogsActivity, HasActivitylogOptions, HasFactory;

    protected static $logName = 'Notificacao';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

    protected $table = 'notificacoes';
    protected $fillable = [
        'tipo',
        'payload',
        'user_id',
        'visto',
    ];
    protected $casts = [
        'id' => 'int',
        'tipo' => 'string',
        'user_id' => 'int',
        'payload' => 'array',
        'visto' => 'boolean',
        'created_at' => 'datetime:d/m/Y à\s H:i',
        'updated_at' => 'datetime:d/m/Y à\s H:i',
    ];

    protected function serializeDate(DateTimeInterface $date) {
        return $date->format('Y-m-d H:i:s');
    }

    protected static function booted()
    {
        /*static::creating(function ($model) {
            $model->user_id = auth()->user()->id;
        });*/
    }
}
