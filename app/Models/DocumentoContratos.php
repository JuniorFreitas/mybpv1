<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;

class DocumentoContratos extends Model
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

    public function Logo()
    {
        return $this->belongsToMany(Arquivo::class, 'cliente_logotipo', 'cliente_id', 'arquivo_id');
    }

    public function Empresa()
    {
        return $this->hasOne(Cliente::class, 'id', 'empresa_id');
    }

}
