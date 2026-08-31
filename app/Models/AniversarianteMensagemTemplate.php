<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Model;

class AniversarianteMensagemTemplate extends Model
{
    use TenantTrait;

    protected $table = 'aniversariante_mensagem_templates';

    protected $fillable = [
        'empresa_id',
        'conteudo_html',
        'criado_por',
        'atualizado_por',
    ];

    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'conteudo_html' => 'string',
        'criado_por' => 'int',
        'atualizado_por' => 'int',
    ];
}
