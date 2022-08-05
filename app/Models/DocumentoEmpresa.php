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

    public function Anexo()
    {
        return $this->belongsToMany(Arquivo::class, 'documento_legais_empresas_anexos', 'id', 'arquivo_id');
    }

    public function Empresa()
    {
        return $this->hasOne(Cliente::class, 'id', 'empresa_id');
    }

    public function Contrato()
    {
        return $this->hasOne(DocumentoContratos::class, 'id', 'contrato_id');
    }
}
