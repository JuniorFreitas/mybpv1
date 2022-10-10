<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;

class DocumentoSsma extends Model
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

    protected $table = 'documento_ssmas';

    protected $fillable = [
        'documentos_ssma',
        'empresa_id',
        'contrato_id',
        'tipo_ssma',
        'ativo'
    ];

    protected $casts = [
        'documentos_ssma' => 'json',
        'empresa_id' => 'int',
        'contrato_id' => 'int',
        'tipo_ssma' => 'boolean',
        'ativo' => 'boolean'
    ];

    public function getDocumentosSsmaAttribute($value)
    {
        return json_decode($value);
    }

    public function Anexo()
    {
        return $this->belongsToMany(Arquivo::class, 'documento_legais_ssmas_anexos', 'id', 'arquivo_id');

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
