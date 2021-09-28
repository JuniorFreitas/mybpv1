<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\AtaReuniaoTipo
 *
 * @property int $id
 * @property int $ata_reuniao_id
 * @property string $tipo Comentário, Assuntos Pendentes ou Próxima Reunião
 * @property string|null $observacao
 * @property-read \App\Models\AtaReuniao $AtaReuniao
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AtaReuniaoTipo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AtaReuniaoTipo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AtaReuniaoTipo query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AtaReuniaoTipo whereAtaReuniaoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AtaReuniaoTipo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AtaReuniaoTipo whereObservacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AtaReuniaoTipo whereTipo($value)
 * @mixin \Eloquent
 */
class AtaReuniaoTipo extends Model
{
    protected $fillable = [
        'ata_reuniao_id',
        'tipo',
        'observacao'
    ];



    public $timestamps = false;

    public function AtaReuniao()
    {
        return $this->hasOne(AtaReuniao::class, 'id', 'ata_reuniao_id');
    }
}
