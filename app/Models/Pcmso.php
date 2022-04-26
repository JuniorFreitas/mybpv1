<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pcmso extends Model
{
    use HasFactory, TenantTrait;

    public $timestamps = false;

    protected $fillable = [
        'empresa_id',
        'label',
        'ativo'
    ];

    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'label' => 'string',
        'ativo' => 'boolean'
    ];

    public function Empresa()
    {
        return $this->belongsTo(Cliente::class, 'empresa_id');
    }
}
