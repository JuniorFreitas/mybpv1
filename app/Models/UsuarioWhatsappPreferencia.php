<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsuarioWhatsappPreferencia extends Model
{
    protected $table = 'usuario_whatsapp_preferencias';

    protected $fillable = [
        'user_id',
        'modulo',
        'receber',
    ];

    protected $casts = [
        'user_id' => 'int',
        'receber' => 'boolean',
    ];

    public function User()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
