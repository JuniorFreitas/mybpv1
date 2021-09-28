<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\HabilidadeCloud
 *
 * @property int $id
 * @property string $nome
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\GrupoCloud[] $grupo
 * @property-read int|null $grupo_count
 * @method static \Illuminate\Database\Eloquent\Builder|HabilidadeCloud newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HabilidadeCloud newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HabilidadeCloud query()
 * @method static \Illuminate\Database\Eloquent\Builder|HabilidadeCloud whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HabilidadeCloud whereNome($value)
 * @mixin \Eloquent
 */
class HabilidadeCloud extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome'
    ];

    public function usesTimestamps(): bool
    {
        return false;
    }

    public function grupo()
    {
        return $this->belongsToMany(GrupoCloud::class, 'grupo_habilidade_cloud');
    }
}
