<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;

/**
 * App\Models\UsuarioDependente
 *
 * @property int $id
 * @property int $user_id
 * @property string $tipo
 * @property string|null $outro_tipo
 * @property string $nome
 * @property string|null $cpf
 * @property \Illuminate\Support\Carbon|null $nascimento
 * @property string|null $observacao
 * @method static \Illuminate\Database\Eloquent\Builder|UsuarioDependente newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UsuarioDependente newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UsuarioDependente query()
 * @method static \Illuminate\Database\Eloquent\Builder|UsuarioDependente whereCpf($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsuarioDependente whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsuarioDependente whereNascimento($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsuarioDependente whereNome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsuarioDependente whereObservacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsuarioDependente whereOutroTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsuarioDependente whereTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsuarioDependente whereUserId($value)
 * @mixin \Eloquent
 */
class UsuarioDependente extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'tipo',
        'outro_tipo',
        'nome',
        'cpf',
        'nascimento',
        'observacao',
    ];

    protected $casts = [
        'id' => 'int',
        'user_id' => 'int',
        'tipo' => 'string',
        'outro_tipo' => 'string',
        'nome' => 'string',
        'cpf' => 'string',
        'nascimento' => 'date:d/m/Y',
        'observacao' => 'string',
    ];

    const TIPO_CONJUGE = 'conjuge';
    const TIPO_FILHO = 'filho';
    const TIPO_OUTRO = 'outro';

    public const TIPOS_DEPENDENTES = ['conjuge' => 'Cônjuge', 'filho' => 'Filho', 'outro' => 'Outro'];

    //Acessor ->nascimento
    public function getNascimentoAttribute($value)
    {
        $data = new DataHora($this->attributes['nascimento']);
        return is_null($this->attributes['nascimento']) ? null : $data->dataCompleta();
    }

    //Modificador ->nascimento
    public function setNascimentoAttribute($value)
    {
        $data = new DataHora($value);
        $this->attributes['nascimento'] = is_null($value) ? null : $data->dataInsert();
    }
}
