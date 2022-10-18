<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;

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
        return is_null($this->attributes['nascimento']) ?: $data->dataCompleta();
    }

    //Modificador ->nascimento
    public function setNascimentoAttribute($value)
    {
        $data = new DataHora($value);
        $this->attributes['nascimento'] = is_null($value) ?: $data->dataInsert();
    }
}
