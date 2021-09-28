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
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|RespostaAlternativas newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RespostaAlternativas newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RespostaAlternativas query()
 * @method static \Illuminate\Database\Eloquent\Builder|RespostaAlternativas whereAlternativaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RespostaAlternativas whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RespostaAlternativas whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RespostaAlternativas whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RespostaAlternativas whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int|null $selecionado Para os checkbox vir marcado
 * @method static \Illuminate\Database\Eloquent\Builder|RespostaAlternativas whereSelecionado($value)
 * @property int $ordem
 * @method static \Illuminate\Database\Eloquent\Builder|RespostaAlternativas whereOrdem($value)
 * @property int|null $link_id
 * @method static \Illuminate\Database\Eloquent\Builder|RespostaAlternativas whereLinkId($value)
 * @property int|null $value
 * @method static \Illuminate\Database\Eloquent\Builder|RespostaAlternativas whereValue($value)
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
