<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;

/**
 * App\Models\AtaReuniaoAcao
 *
 * @property int $id
 * @property int $ata_reuniao_id
 * @property string $acao
 * @property string|null $prazo
 * @property int|null $continuo
 * @property string $status
 * @property string|null $observacao
 * @property string $responsavel
 * @property string $email
 * @property-read \App\Models\AtaReuniao $AtaReuniao
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AtaReuniaoAcao newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AtaReuniaoAcao newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AtaReuniaoAcao query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AtaReuniaoAcao whereAcao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AtaReuniaoAcao whereAtaReuniaoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AtaReuniaoAcao whereContinuo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AtaReuniaoAcao whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AtaReuniaoAcao whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AtaReuniaoAcao whereObservacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AtaReuniaoAcao wherePrazo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AtaReuniaoAcao whereResponsavel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AtaReuniaoAcao whereStatus($value)
 * @mixin \Eloquent
 */
class AtaReuniaoAcao extends Model
{
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
