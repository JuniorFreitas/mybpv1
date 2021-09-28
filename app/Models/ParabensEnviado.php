<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ParabensEnviado
 *
 * @property int $id
 * @property int|null $curriculo_id
 * @property int|null $cliente_id
 * @property int $ano
 * @method static \Illuminate\Database\Eloquent\Builder|ParabensEnviado newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ParabensEnviado newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ParabensEnviado query()
 * @method static \Illuminate\Database\Eloquent\Builder|ParabensEnviado whereAno($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParabensEnviado whereClienteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParabensEnviado whereCurriculoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParabensEnviado whereId($value)
 * @mixin \Eloquent
 */
class ParabensEnviado extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'curriculo_id',
        'ano'
    ];

    protected $table = 'parabens_enviados';

    public $timestamps = false;

}
