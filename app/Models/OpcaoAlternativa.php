<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\OpcaoAlternativa
 *
 * @property int $id
 * @property int $alternativa_id
 * @property string $label
 * @property bool $selecionado
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|OpcaoAlternativa newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OpcaoAlternativa newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OpcaoAlternativa query()
 * @method static \Illuminate\Database\Eloquent\Builder|OpcaoAlternativa whereAlternativaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OpcaoAlternativa whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OpcaoAlternativa whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OpcaoAlternativa whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OpcaoAlternativa whereSelecionado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OpcaoAlternativa whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class OpcaoAlternativa extends Model
{
    use HasFactory;

    protected $fillable = [
        'alternativa_id',
        'label',
        'selecionado',
    ];

    protected $casts = [
        'alternativa_id' => 'int',
        'label' => 'string',
        'selecionado' => 'boolean',
    ];
}
