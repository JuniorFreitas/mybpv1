<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AtaReuniaoComentario extends Model
{
    use SoftDeletes, TenantTrait;

    protected $table = 'ata_reuniao_comentarios';

    protected $fillable = [
        'empresa_id',
        'ata_reuniao_id',
        'ata_reuniao_acao_id',
        'autor_id',
        'texto',
        'mencoes',
    ];

    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'ata_reuniao_id' => 'int',
        'ata_reuniao_acao_id' => 'int',
        'autor_id' => 'int',
        'texto' => 'string',
        'mencoes' => 'array',
    ];

    public function Autor()
    {
        return $this->hasOne(User::class, 'id', 'autor_id');
    }
}
