<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecuperacaoSenha extends Model
{

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'token',
        'ip_solicitacao',
        'solicitacao',
        'expiracao',
        'ip_recuperacao',
        'recuperacao',
        'recuperado',
    ];

    protected $casts = [
        'user_id' => 'int',
        'token' => 'string',
        'ip_solicitacao' => 'string',
        'solicitacao' => 'date:d/m/Y H:i',
        'expiracao' => 'date:d/m/Y H:i',
        'ip_recuperacao' => 'string',
        'recuperacao' => 'date:d/m/Y H:i',
        'recuperado' => 'boolean'
    ];



    public function User()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

}
