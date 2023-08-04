<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\AtaReuniaoAssunto
 *
 * @property int $id
 * @property int $ata_reuniao_id
 * @property string $assunto
 * @property-read \App\Models\AtaReuniao|null $AtaReuniao
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAssunto newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAssunto newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAssunto query()
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAssunto whereAssunto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAssunto whereAtaReuniaoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAssunto whereId($value)
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
