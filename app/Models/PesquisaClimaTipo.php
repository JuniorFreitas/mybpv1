<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PesquisaClimaTipo
 *
 * @property int $id
 * @property string $nome
 * @property int $ativo
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PesquisaClimaPergunta[] $PesquisaClimaPergunta
 * @property-read int|null $pesquisa_clima_pergunta_count
 * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaTipo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaTipo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaTipo query()
 * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaTipo whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaTipo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaTipo whereNome($value)
 * @mixin \Eloquent
 */
class PesquisaClimaTipo extends Model
{
    protected $fillable = [
        'nome',
        'ativo'
    ];

    public $timestamps = false;


    public function PesquisaClimaPergunta()
    {
        return $this->hasMany(PesquisaClimaPergunta::class, 'tipo_id', 'id');
    }

}
