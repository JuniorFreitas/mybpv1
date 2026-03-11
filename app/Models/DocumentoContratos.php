<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;

/**
 * App\Models\DocumentoContratos
 *
 * @property int $id
 * @property int $empresa_id
 * @property array $dados_cadastrais
 * @property bool $ativo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $Anexo
 * @property-read int|null $anexo_count
 * @property-read \App\Models\Cliente|null $Empresa
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentoContratos newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentoContratos newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentoContratos query()
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentoContratos whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentoContratos whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentoContratos whereDadosCadastrais($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentoContratos whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentoContratos whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentoContratos whereUpdatedAt($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @mixin \Eloquent
 */
class DocumentoContratos extends Model
{
    use LogsActivity, HasActivitylogOptions, HasFactory, TenantTrait;

    protected static $logName = 'DocumentoContratos';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = "";
    }

    protected $table = 'documento_contratos';

    protected $fillable = [
        'dados_cadastrais',
        'empresa_id',
        'ativo'
    ];

    protected $casts = [
        'dados_cadastrais' => 'json',
        'empresa_id' => 'int',
        'ativo' => 'boolean'
    ];

    public const TIPO_DOCUMENTOS_CONTRATO = 'Documentos Contrato';
    public const TIPO_DOCUMENTOS_EMPRESA = 'Documentos Empresa';
    public const TIPO_DOCUMENTOS_SSMA = 'Documentos SSMA';
    public const TIPO_PESSOA_JURIDICA = 'Pessoa Jurídica';
    public const TIPO_PESSOA_FISICA = 'Pessoa Física';

    public function getDadosCadastraisAttribute($value)
    {
        return json_decode($value);
    }

    public function getEndereco()
    {
        $endereco = $this->logradouro;
        $bairro = $this->bairro;
        $cep = $this->cep;
        $numero = $this->numero ? $this->numero : 'S/N';
        $complemento = $this->complemento;

        if ($complemento) {
            $endereco_completo = "{$endereco}, {$complemento}, {$numero}, {$bairro}, {$cep}, {$this->municipio}-{$this->uf}";
        } else {
            $endereco_completo = "{$endereco}, {$numero}, {$bairro}, {$cep}, {$this->municipio}-{$this->uf}";
        }

        return $endereco_completo;
    }

    public static function getEnderecoCompleto($endereco)
    {
        $logradouro = $endereco->logradouro;
        $bairro = $endereco->bairro;
        $cep = $endereco->cep;
        $numero = $endereco->numero ? $endereco->numero : 'S/N';
        $complemento = $endereco->complemento ? $endereco->complemento : '';

        if ($complemento) {
            $endereco_completo = "{$logradouro}, {$complemento}, {$numero}, {$bairro}, {$cep}, {$endereco->municipio}-{$endereco->uf}";
        } else {
            $endereco_completo = "{$logradouro}, {$numero}, {$bairro}, {$cep}, {$endereco->municipio}-{$endereco->uf}";
        }

        return $endereco_completo;
    }

    public function Anexo()
    {
        return $this->belongsToMany(Arquivo::class, 'documento_legais_contratos_anexos', 'id', 'arquivo_id');
    }

    public function Empresa()
    {
        return $this->hasOne(Cliente::class, 'id', 'empresa_id');
    }

    public static function getContrato($id){
        return self::find($id);
    }

}
