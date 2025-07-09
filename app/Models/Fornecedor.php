<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\Fornecedor
 *
 * @property int $id
 * @property int|null $empresa_id
 * @property string $tipo Fornecedor, Terceiro, Parceiro
 * @property string|null $cnpj
 * @property string|null $cpf
 * @property string|null $nome
 * @property string $tipo_pessoa
 * @property string|null $razao_social
 * @property string|null $nome_fantasia
 * @property string|null $cep
 * @property string|null $logradouro
 * @property string|null $numero
 * @property string|null $complemento
 * @property string|null $bairro
 * @property string|null $municipio
 * @property string|null $uf
 * @property string|null $contato
 * @property string|null $email
 * @property string|null $aniversario
 * @property bool $ativo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $Anexos
 * @property-read int|null $anexos_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FornecedorServico> $Servicos
 * @property-read int|null $servicos_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UsuarioTelefone> $Telefones
 * @property-read int|null $telefones_count
 * @property-read \App\Models\User|null $Usuario
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read mixed $endereco_completo
 * @method static \Illuminate\Database\Eloquent\Builder|Fornecedor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Fornecedor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Fornecedor query()
 * @method static \Illuminate\Database\Eloquent\Builder|Fornecedor whereAniversario($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fornecedor whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fornecedor whereBairro($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fornecedor whereCep($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fornecedor whereCnpj($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fornecedor whereComplemento($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fornecedor whereContato($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fornecedor whereCpf($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fornecedor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fornecedor whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fornecedor whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fornecedor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fornecedor whereLogradouro($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fornecedor whereMunicipio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fornecedor whereNome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fornecedor whereNomeFantasia($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fornecedor whereNumero($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fornecedor whereRazaoSocial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fornecedor whereTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fornecedor whereTipoPessoa($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fornecedor whereUf($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fornecedor whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Fornecedor extends Model
{
    use  LogsActivity, TenantTrait;

    protected static $logFillable = true;
    protected static $logName = 'fornecedores';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = "";
    }

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


}
