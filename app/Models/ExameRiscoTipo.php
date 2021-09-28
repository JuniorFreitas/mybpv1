<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ExameRiscoTipo
 *
 * @property int $id
 * @property string $label
 * @property bool $ativo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ExameRiscoTipo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExameRiscoTipo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExameRiscoTipo query()
 * @method static \Illuminate\Database\Eloquent\Builder|ExameRiscoTipo whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameRiscoTipo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameRiscoTipo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameRiscoTipo whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameRiscoTipo whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ExameRiscoTipo extends Model
{
    use HasFactory;
    protected $fillable = [

        'label',
        'ativo'
    ];
    protected $casts = [
        'id' => 'int',
        'label' => 'string',
        'ativo' => 'boolean'
    ];

}
