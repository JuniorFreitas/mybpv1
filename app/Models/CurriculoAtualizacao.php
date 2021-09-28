<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;

/**
 * App\Models\CurriculoAtualizacao
 *
 * @property-read mixed $created_at
 * @method static \Illuminate\Database\Eloquent\Builder|CurriculoAtualizacao newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CurriculoAtualizacao newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CurriculoAtualizacao query()
 * @mixin \Eloquent
 * @property int $curriculo_id
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|CurriculoAtualizacao whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CurriculoAtualizacao whereCurriculoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CurriculoAtualizacao whereUpdatedAt($value)
 */
class CurriculoAtualizacao extends Model
{
    use HasFactory;

    protected $fillable = ['curriculo_id'];
    protected $casts = ['curriculo_id' => 'int'];

    public function getCreatedAtAttribute($value)
    {
        $data = new DataHora($this->attributes['created_at']);
        return $data->dataCompleta() . ' ' . $data->hora() . ':' . $data->minuto() . 'h';
    }
}
