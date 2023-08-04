<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\AlternativaFormulario
 *
 * @property int $id
 * @property int|null $empresa_id
 * @property string $nome
 * @property string $tipo
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\RespostaAlternativas[] $Opcoes
 * @property-read int|null $opcoes_count
 * @method static \Illuminate\Database\Eloquent\Builder|AlternativaFormulario newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AlternativaFormulario newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AlternativaFormulario query()
 * @method static \Illuminate\Database\Eloquent\Builder|AlternativaFormulario whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AlternativaFormulario whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AlternativaFormulario whereNome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AlternativaFormulario whereTipo($value)
 * @mixin \Eloquent
 */
class AlternativaFormulario extends Model
{
    use HasFactory;

    protected $table = 'alternativa_formularios';
    protected $fillable = ['nome', 'tipo'];
    protected $casts = ['id' => 'int', 'nome' => 'string', 'tipo' => 'string'];

    public $timestamps = false;

    public function Opcoes()
    {
        return $this->hasMany(RespostaAlternativas::class,'alternativa_id','id')->orderBy('ordem');
    }
}
//\AlternativaFormulario::create(['nome' => 'teste', 'tipo' => 'text']);
