<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\SetoresFormulario
 *
 * @property int $id
 * @property int|null $empresa_id
 * @property string $nome
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\AlternativaFormulario[] $Alternativas
 * @property-read int|null $alternativas_count
 * @method static \Illuminate\Database\Eloquent\Builder|SetoresFormulario newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SetoresFormulario newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SetoresFormulario query()
 * @method static \Illuminate\Database\Eloquent\Builder|SetoresFormulario whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SetoresFormulario whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SetoresFormulario whereNome($value)
 * @mixin \Eloquent
 */
class SetoresFormulario extends Model
{
    use HasFactory;

    protected $table = 'setores_formularios';
    protected $fillable = ['nome'];
    protected $casts = ['id' => 'int', 'nome' => 'string'];

    public function usesTimestamps(): bool
    {
        return false;
    }

    public function Alternativas()
    {
        return $this->belongsToMany(AlternativaFormulario::class, 'setor_alternativas', 'setor_id', 'alternativa_id')
            ->withPivot([
                'obrigatorio',
                'min',
                'max',
                'ordem',
                'class_especial'
            ])->orderBy('ordem');
    }
}
