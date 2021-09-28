<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\AtaReuniaoParticipante
 *
 * @property int $id
 * @property int $ata_reuniao_id
 * @property string|null $nome
 * @property int|null $user_id
 * @property string $funcao
 * @property-read \App\Models\AtaReuniao $AtaReuniao
 * @property-read \App\Models\User $User
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AtaReuniaoParticipante newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AtaReuniaoParticipante newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AtaReuniaoParticipante query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AtaReuniaoParticipante whereAtaReuniaoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AtaReuniaoParticipante whereFuncao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AtaReuniaoParticipante whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AtaReuniaoParticipante whereNome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AtaReuniaoParticipante whereUserId($value)
 * @mixin \Eloquent
 */
class AtaReuniaoParticipante extends Model
{
    protected $fillable = [
        'ata_reuniao_id',
        'nome',
        'user_id',
        'funcao',
    ];

    public $timestamps = false;

    public function AtaReuniao()
    {
        return $this->hasOne(AtaReuniao::class, 'id', 'ata_reuniao_id');
    }

    public function User()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
