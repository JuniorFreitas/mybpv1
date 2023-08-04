<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\RespostaAlternativas
 *
 * @property int $id
 * @property int $alternativa_id
 * @property string $label
 * @property bool|null $selecionado Para os checkbox vir marcado
 * @property int|null $link_id
 * @property int $ordem
 * @property int|null $value
 * @method static \Illuminate\Database\Eloquent\Builder|RespostaAlternativas newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RespostaAlternativas newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RespostaAlternativas query()
 * @method static \Illuminate\Database\Eloquent\Builder|RespostaAlternativas whereAlternativaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RespostaAlternativas whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RespostaAlternativas whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RespostaAlternativas whereLinkId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RespostaAlternativas whereOrdem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RespostaAlternativas whereSelecionado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RespostaAlternativas whereValue($value)
 * @mixin \Eloquent
 */
class RespostaAlternativas extends Model
{
    use HasFactory;
    protected $fillable = [
        'alternativa_id',
        'label',
        'selecionado',
        'value'
    ];

    protected $casts = [
        'alternativa_id' => 'int',
        'label' => 'string',
        'selecionado' => 'boolean',
        'value' => 'int'
    ];
}
