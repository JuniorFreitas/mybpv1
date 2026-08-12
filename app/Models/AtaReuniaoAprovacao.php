<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AtaReuniaoAprovacao extends Model
{
    use SoftDeletes, TenantTrait;

    protected $table = 'ata_reuniao_aprovacoes';

    protected $fillable = [
        'empresa_id',
        'ata_reuniao_id',
        'aprovador_id',
        'versao',
        'status',
        'decisao',
        'comentario',
        'respondido_em',
    ];

    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'ata_reuniao_id' => 'int',
        'aprovador_id' => 'int',
        'versao' => 'string',
        'status' => 'string',
        'decisao' => 'string',
        'comentario' => 'string',
        'respondido_em' => 'datetime',
    ];

    public function Aprovador()
    {
        return $this->hasOne(User::class, 'id', 'aprovador_id');
    }
}
