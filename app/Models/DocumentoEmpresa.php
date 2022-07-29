<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;

class DocumentoEmpresa extends Model
{
    use HasFactory, TenantTrait;

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = "";
    }

    protected $table = 'documento_empresas';

    protected $fillable = [
        'documentos_empresa',
        'empresa_id',
        'contrato_id',
        'tipo_empresa',
        'ativo'
    ];

    protected $casts = [
        'documentos_empresa' => 'json',
        'empresa_id' => 'int',
        'contrato_id' => 'int',
        'tipo_empresa' => 'boolean',
        'ativo' => 'boolean'
    ];

    public function Logo()
    {
        return $this->belongsToMany(Arquivo::class, 'empresa_logotipo', 'cliente_id', 'arquivo_id');
    }

    public function EmpresaConfig()
    {
        return$this->hasMany(EmpresaConfig::class, 'empresa_id','documento_empresas.id');
    }
}
