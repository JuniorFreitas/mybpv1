<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarteiraAssinatura extends Model
{
    use HasFactory, TenantTrait;

    protected $fillable = [
        'empresa_id',
        'arquivo_id',
        'nome',
        'tipo',
        'ativo',
    ];

    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'arquivo_id' => 'int',
        'nome' => 'string',
        'tipo' => 'string',
        'ativo' => 'boolean',
    ];

    const TIPO_GERENTE_OU_RH = 'GERENTE OU RH';
    const TIPO_SESMT = 'SESMT';

    const TIPOS = [
        self::TIPO_GERENTE_OU_RH,
        self::TIPO_SESMT,
    ];

    public function Arquivo()
    {
        return $this->belongsTo(Arquivo::class);
    }

    public function Empresa()
    {
        return $this->belongsTo(Cliente::class, 'empresa_id');
    }
}
