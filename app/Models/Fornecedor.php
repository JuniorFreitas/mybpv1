<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Fornecedor extends Model
{
    use  LogsActivity, TenantTrait;

    protected static $logFillable = true;
    protected static $logName = 'fornecedores';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    protected $table = 'fornecedores';

    protected $fillable = [
        'empresa_id',
        'tipo',
        'cnpj',
        'cpf',
        'nome',
        'tipo_pessoa',
        'razao_social',
        'nome_fantasia',
        'cep',
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'municipio',
        'uf',
        'contato',
        'aniversario',
        'email',
        'ativo',
    ];

    protected $casts = [
        'empresa_id' => 'int',
        'ativo' => 'boolean',
    ];

    protected $appends = ['endereco_completo'];

    // Constantes
    public const PESSOA_FISICA = 'pessoa_física';
    public const PESSOA_JURIDICA = 'pessoa_jurídica';

    public const TIPO_FORNECEDOR = 'Fornecedor';
    public const TIPO_TERCEIRO = 'Terceiro';
    public const TIPO_PARCEIRO = 'Parceiro';

    public const VENCIMENTO_MENSAL = 'mensal';
    public const VENCIMENTO_TRIMESTRAL = 'trimestral';
    public const VENCIMENTO_SEMESTRAL = 'semestral';
    public const VENCIMENTO_ANUAL = 'anual';


    // Accessor
    public function getEnderecoCompletoAttribute()
    {
        $componentes = [
            $this->logradouro,
            $this->complemento,
            $this->numero ?: 'S/N',
            $this->bairro,
            $this->cep,
            "{$this->municipio}-{$this->uf}"
        ];

        return implode(', ', array_filter($componentes));
    }

    // Relacionamentos
    public function Usuario()
    {
        return $this->hasOne(User::class, 'id', 'id');
    }

    public function Servicos()
    {
        return $this->hasMany(FornecedorServico::class, 'fornecedor_id', 'id')
            ->orderByDesc('id');
    }

    public function Telefones()
    {
        return $this->hasMany(UsuarioTelefone::class, 'user_id', 'id');
    }

    public function Anexos()
    {
        return $this->belongsToMany(Arquivo::class, 'fornecedor_anexos', 'fornecedor_id', 'arquivo_id');
    }

    // Events
    protected static function booted()
    {
        static::updating(function ($model) {
            $userData = [
                'nome' => $model->tipo_pessoa === self::PESSOA_JURIDICA
                    ? $model->razao_social
                    : $model->nome,
                'login' => $model->email,
                'ativo' => $model->ativo,
            ];

            if ($model->tipo_pessoa === self::PESSOA_JURIDICA) {
                $userData['tipo'] = User::FORNECEDOR;
            }

            $model->usuario?->update($userData);
        });
    }

    // Activity Log
    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }
}
