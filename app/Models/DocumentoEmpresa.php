<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;

/**
 * App\Models\DocumentoEmpresa
 *
 * @property int $id
 * @property int $empresa_id
 * @property int|null $contrato_id
 * @property bool $tipo_empresa
 * @property array $documentos_empresa
 * @property bool $ativo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $Anexo
 * @property-read int|null $anexo_count
 * @property-read \App\Models\DocumentoContratos|null $Contrato
 * @property-read \App\Models\Cliente|null $Empresa
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentoEmpresa newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentoEmpresa newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentoEmpresa query()
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentoEmpresa whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentoEmpresa whereContratoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentoEmpresa whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentoEmpresa whereDocumentosEmpresa($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentoEmpresa whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentoEmpresa whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentoEmpresa whereTipoEmpresa($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentoEmpresa whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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

    public function getDocumentosEmpresaAttribute($value)
    {
        return json_decode($value);
    }

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
