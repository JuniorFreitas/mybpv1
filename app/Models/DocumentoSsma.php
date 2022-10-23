<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;

/**
 * App\Models\DocumentoSsma
 *
 * @property int $id
 * @property int $empresa_id
 * @property int|null $contrato_id
 * @property bool $tipo_ssma
 * @property array $documentos_ssma
 * @property bool $ativo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $Anexo
 * @property-read int|null $anexo_count
 * @property-read \App\Models\DocumentoContratos|null $Contrato
 * @property-read \App\Models\Cliente|null $Empresa
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentoSsma newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentoSsma newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentoSsma query()
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentoSsma whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentoSsma whereContratoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentoSsma whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentoSsma whereDocumentosSsma($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentoSsma whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentoSsma whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentoSsma whereTipoSsma($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentoSsma whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
