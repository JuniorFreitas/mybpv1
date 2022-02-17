<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PeriodoAquisitivo
 *
 * @property int $id
 * @property string $label
 * @property int $ano_inicial
 * @property int $ano_final
 * @method static \Illuminate\Database\Eloquent\Builder|PeriodoAquisitivo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PeriodoAquisitivo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PeriodoAquisitivo query()
 * @method static \Illuminate\Database\Eloquent\Builder|PeriodoAquisitivo whereAnoFinal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PeriodoAquisitivo whereAnoInicial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PeriodoAquisitivo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PeriodoAquisitivo whereLabel($value)
 * @mixin \Eloquent
 */
class PeriodoAquisitivo extends Model
{
    use HasFactory;

    protected $table = 'periodos_aquisitivos';

    protected $fillable = [
        'label',
        'ano_inicial',
        'ano_final',
    ];
    protected $casts = [
        'label' => 'string',
        'ano_inicial' => 'int',
        'ano_final' => 'int',
    ];

}
