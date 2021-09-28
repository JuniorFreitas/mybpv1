<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\IntermitenteTipo
 *
 * @property int $id
 * @property string $label
 * @property bool $ativo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteTipo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteTipo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteTipo query()
 * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteTipo whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteTipo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteTipo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteTipo whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteTipo whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class IntermitenteTipo extends Model
{
    protected $fillable = ['label', 'ativo'];
    protected $casts = ['label' => 'string', 'ativo' => 'boolean'];
}
