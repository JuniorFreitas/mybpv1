<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AniversarianteWhatsappMensagem extends Model
{
    protected $table = 'aniversariante_whatsapp_mensagens';

    protected $fillable = [
        'empresa_id',
        'dia',
        'corpo',
    ];

    protected $casts = [
        'empresa_id' => 'int',
        'dia' => 'int',
        'corpo' => 'string',
    ];
}
