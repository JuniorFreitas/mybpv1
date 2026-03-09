<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Cliente;

class CartaOfertaTemplate extends Model
{
    use HasFactory, TenantTrait;

    public const STATUS_RASCUNHO = 'rascunho';
    public const STATUS_PUBLICADO = 'publicado';

    protected $table = 'carta_oferta_templates';

    protected $fillable = [
        'empresa_id',
        'titulo',
        'conteudo_html',
        'status',
        'versao',
        'criado_por',
        'atualizado_por',
    ];

    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'titulo' => 'string',
        'conteudo_html' => 'string',
        'status' => 'string',
        'versao' => 'int',
        'criado_por' => 'int',
        'atualizado_por' => 'int',
    ];

    public function scopePublicado($query)
    {
        return $query->where('status', self::STATUS_PUBLICADO);
    }

    public function empresa()
    {
        return $this->hasOne(Cliente::class, 'id', 'empresa_id');
    }
}
