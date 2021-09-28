<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\AtaReuniaoAssunto
 *
 * @property int $id
 * @property int $ata_reuniao_id
 * @property string $assunto
 * @property-read \App\Models\AtaReuniao $AtaReuniao
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AtaReuniaoAssunto newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AtaReuniaoAssunto newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AtaReuniaoAssunto query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AtaReuniaoAssunto whereAssunto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AtaReuniaoAssunto whereAtaReuniaoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AtaReuniaoAssunto whereId($value)
 * @mixin \Eloquent
 */
class AtaReuniaoAssunto extends Model
{
    protected $fillable = [
        'ata_reuniao_id',
        'assunto',
    ];

    public $timestamps = false;

    public function AtaReuniao()
    {
        return $this->hasOne(AtaReuniao::class, 'id', 'ata_reuniao_id');
    }
}
