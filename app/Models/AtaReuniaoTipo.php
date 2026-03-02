<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\AtaReuniaoTipo
 *
 * @property int $id
 * @property int $ata_reuniao_id
 * @property string $tipo Comentário, Assuntos Pendentes ou Próxima Reunião
 * @property string|null $observacao
 * @property-read \App\Models\AtaReuniao|null $AtaReuniao
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoTipo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoTipo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoTipo query()
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoTipo whereAtaReuniaoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoTipo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoTipo whereObservacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoTipo whereTipo($value)
 * @mixin \Eloquent
 */
class AtaReuniaoTipo extends Model
{

    use LogsActivity, HasActivitylogOptions;

    protected static $logName = 'AtaReuniaoTipo';
    protected $fillable = [
        'ata_reuniao_id',
        'tipo',
        'observacao'
    ];

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }



    public $timestamps = false;

    public function AtaReuniao()
    {
        return $this->hasOne(AtaReuniao::class, 'id', 'ata_reuniao_id');
    }
}
