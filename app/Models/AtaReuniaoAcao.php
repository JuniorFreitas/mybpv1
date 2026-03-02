<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;

/**
 * App\Models\AtaReuniaoAcao
 *
 * @property int $id
 * @property int $ata_reuniao_id
 * @property string $responsavel
 * @property string $email
 * @property string $acao
 * @property string|null $prazo
 * @property int|null $continuo
 * @property string|null $observacao
 * @property string $status
 * @property-read \App\Models\AtaReuniao|null $AtaReuniao
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAcao newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAcao newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAcao query()
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAcao whereAcao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAcao whereAtaReuniaoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAcao whereContinuo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAcao whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAcao whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAcao whereObservacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAcao wherePrazo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAcao whereResponsavel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAcao whereStatus($value)
 * @mixin \Eloquent
 */
class AtaReuniaoAcao extends Model
{

    use LogsActivity, HasActivitylogOptions;

    protected static $logName = 'AtaReuniaoAcao';
    protected $fillable = [
        'ata_reuniao_id',
        'responsavel',
        'email',
        'acao',
        'prazo',
        'continuo',
        'observacao',
        'status',
    ];

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

    //Acessor ->prazo
    public function getPrazoAttribute($value)
    {
        if ($value) {
            $data = new DataHora($this->attributes['prazo']);
            return $data->dataCompleta();
        }
    }

    //Modificador ->prazo
    public function setPrazoAttribute($value)
    {
        if ($value) {
            $data = new DataHora($value);
            $this->attributes['prazo'] = $data->dataInsert();
        }
    }


    public $timestamps = false;

    public function AtaReuniao()
    {
        return $this->hasOne(AtaReuniao::class, 'id', 'ata_reuniao_id');
    }
}
