<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\CihTag
 *
 * @property int $id
 * @property string $label
 * @property bool $ativo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|CihTag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CihTag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CihTag query()
 * @method static \Illuminate\Database\Eloquent\Builder|CihTag whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CihTag whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CihTag whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CihTag whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CihTag whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CihTag extends Model
{
    use HasFactory;

    protected $table = 'cih_tags';
    protected $fillable = ['label','ativo'];
    protected $casts = ['label' => 'string','ativo' => 'boolean'];
}
