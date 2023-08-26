<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\Cliente
 *
 * @property int $id
 * @property string $tipo_cliente
 * @property string|null $cnpj
 * @property string|null $cpf
 * @property string|null $nome
 * @property string|null $apelido
 * @property string $tipo
 * @property string|null $razao_social
 * @property string|null $nome_fantasia
 * @property int $area_id
 * @property string|null $ramo
 * @property string|null $cep
 * @property string|null $logradouro
 * @property string|null $numero
 * @property string|null $complemento
 * @property string|null $bairro
 * @property string|null $municipio
 * @property string|null $uf
 * @property string|null $contato
 * @property string|null $email
 * @property string|null $tel_principal
 * @property string|null $aniversario
 * @property string|null $como_conheceu
 * @property string|null $como_conheceu_outro
 * @property string|null $politica_ehs
 * @property bool $ativo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $missao
 * @property string|null $visao
 * @property string|null $valores
 * @property string|null $politica_gq
 * @property-read \App\Models\Area|null $Area
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AreaEtiqueta> $AreasEtiquetas
 * @property-read int|null $areas_etiquetas_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Vaga> $Cargos
 * @property-read int|null $cargos_count
 * @property-read \App\Models\CarteiraAssinatura|null $CarteiraAssinatura
 * @property-read \App\Models\ClienteConfig|null $ClienteConfig
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $EmpresaFuncionarios
 * @property-read int|null $empresa_funcionarios_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ClienteFilial> $Filiais
 * @property-read int|null $filiais_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $Funcionarios
 * @property-read int|null $funcionarios_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $Logo
 * @property-read int|null $logo_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $Mascote
 * @property-read int|null $mascote_count
 * @property-read \App\Models\Papel|null $Papel
 * @property-read \App\Models\ParabensEnviado|null $Parabens
 * @property-read \App\Models\PesquisaClimaCliente|null $PesquisaClimaCliente
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ServicosCliente> $ServicosCliente
 * @property-read int|null $servicos_cliente_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ServicosProspects> $ServicosProspect
 * @property-read int|null $servicos_prospect_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ClienteTelefone> $Telefones
 * @property-read int|null $telefones_count
 * @property-read \App\Models\EmpresaTemporaria|null $Temporaria
 * @property-read \App\Models\User|null $Usuario
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VagasAbertas> $VagasAbertas
 * @property-read int|null $vagas_abertas_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read mixed $endereco_completo
 * @property mixed $inicio
 * @method static Builder|Cliente newModelQuery()
 * @method static Builder|Cliente newQuery()
 * @method static Builder|Cliente query()
 * @method static Builder|Cliente whereAniversario($value)
 * @method static Builder|Cliente whereApelido($value)
 * @method static Builder|Cliente whereAreaId($value)
 * @method static Builder|Cliente whereAtivo($value)
 * @method static Builder|Cliente whereBairro($value)
 * @method static Builder|Cliente whereCep($value)
 * @method static Builder|Cliente whereCnpj($value)
 * @method static Builder|Cliente whereComoConheceu($value)
 * @method static Builder|Cliente whereComoConheceuOutro($value)
 * @method static Builder|Cliente whereComplemento($value)
 * @method static Builder|Cliente whereContato($value)
 * @method static Builder|Cliente whereCpf($value)
 * @method static Builder|Cliente whereCreatedAt($value)
 * @method static Builder|Cliente whereEmail($value)
 * @method static Builder|Cliente whereId($value)
 * @method static Builder|Cliente whereLogradouro($value)
 * @method static Builder|Cliente whereMissao($value)
 * @method static Builder|Cliente whereMunicipio($value)
 * @method static Builder|Cliente whereNome($value)
 * @method static Builder|Cliente whereNomeFantasia($value)
 * @method static Builder|Cliente whereNumero($value)
 * @method static Builder|Cliente wherePoliticaEhs($value)
 * @method static Builder|Cliente wherePoliticaGq($value)
 * @method static Builder|Cliente whereRamo($value)
 * @method static Builder|Cliente whereRazaoSocial($value)
 * @method static Builder|Cliente whereTelPrincipal($value)
 * @method static Builder|Cliente whereTipo($value)
 * @method static Builder|Cliente whereTipoCliente($value)
 * @method static Builder|Cliente whereUf($value)
 * @method static Builder|Cliente whereUpdatedAt($value)
 * @method static Builder|Cliente whereValores($value)
 * @method static Builder|Cliente whereVisao($value)
 * @mixin \Eloquent
 */
class Cliente extends Model
{
    use HasFactory, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'cliente';
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

    protected $table = 'clientes';

    protected $fillable = [
        'id',
        'tipo_cliente',
        'cnpj',
        'cpf',
        'nome',
        'tipo',
        'razao_social',
        'nome_fantasia',
        'area_id',
        'ramo',
        'cep',
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'municipio',
        'uf',
        'contato',
        'email',
        'aniversario',
        'como_conheceu',
        'como_conheceu_outro',
        'apelido',
        'tel_principal',
        'politica_ehs',
        'missao',
        'visao',
        'valores',
        'politica_gq',
        'ativo',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'int',
        'tipo_cliente' => 'string',
        'cnpj' => 'string',
        'cpf' => 'string',
        'nome' => 'string',
        'tipo' => 'string',
        'razao_social' => 'string',
        'nome_fantasia' => 'string',
        'area_id' => 'int',
        'ramo' => 'string',
        'cep' => 'string',
        'logradouro' => 'string',
        'numero' => 'string',
        'complemento' => 'string',
        'bairro' => 'string',
        'municipio' => 'string',
        'uf' => 'string',
        'contato' => 'string',
        'email' => 'string',
        'aniversario' => 'string',
        'como_conheceu' => 'string',
        'como_conheceu_outro' => 'string',
        'apelido' => 'string',
        'tel_principal' => 'string',
        'politica_ehs' => 'string',
        'missao' => 'string',
        'visao' => 'string',
        'valores' => 'string',
        'politica_gq' => 'string',
        'ativo' => 'boolean',
        'created_at' => 'date:d/m/Y',
        'updated_at' => 'date:d/m/Y',
    ];

    public const BPSE = 1; // Empresa BPSE

    protected $appends = ['endereco_completo'];

    public const TIPO_PESSOA_FISICA = 'Pessoa Física';
    public const TIPO_PESSOA_JURIDICA = 'Pessoa Jurídica';

    public const TIPO_CONTRATO_FIXO = 'Fixo';
    public const TIPO_CONTRATO_SPOT = 'Spot';
    public const TIPO_CONTRATO_PROPOSTA = 'Proposta';

//    public function getRazaoSocialAttribute()
//    {
//        return "BPSE";
//    }

//    public function getCnpjAttribute()
//    {
//        $cnpj = $this->attributes['cnpj'];
//        $pt1 = substr($cpf, 4, 3);
//        $pt2 = substr($cpf, 8, 3);
//
//        return "XXX.{$pt1}.{$pt2}-XX";
////        return $pt1 . '.XXX.XXX-' . $pt2;
//    }

    public function getEnderecoCompletoAttribute()
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


    //Acessor ->inicio
    public function getInicioAttribute($value)
    {
        $data = new DataHora($this->attributes['inicio']);
        return $data->dataCompleta();
    }

    //Modificador ->inicio
    public function setInicioAttribute($value)
    {
        $data = new DataHora($value);
        $this->attributes['inicio'] = $data->dataInsert();
    }

    //Acessor ->aniversario
    public function getAniversarioAttribute($value)
    {
        if (!is_null($value)) {
            $data = new DataHora($this->attributes['aniversario']);
            return $data->dia() . '/' . $data->mes();
        } else {
            $this->attributes['aniversario'] = null;
        }
    }

    //Modificador ->aniversario
    public function setAniversarioAttribute($value)
    {
        if (!is_null($value)) {
            $data = $value . '/' . date('Y H:i:s');
            $this->attributes['aniversario'] = (new DataHora($data))->dataInsert();
        } else {
            $this->attributes['aniversario'] = null;
        }
    }

    //Acessor ->aniversario
    public function getCreatedAtAttribute($value)
    {
        if (!is_null($value)) {
            $data = new DataHora($this->attributes['created_at']);
            return $data->dataHoraInsert();
//            return $data->dataCompleta() . ' às ' . $data->hora() . ':' . $data->minuto();
        } else {
            $this->attributes['created_at'] = null;
        }
    }

    //Modificador ->created_at
    public function setCreatedAtAttribute($value)
    {
        if (!is_null($value)) {
            $this->attributes['created_at'] = (new DataHora($value))->dataHoraInsert();
        } else {
            $this->attributes['created_at'] = null;
        }
    } //Acessor ->aniversario

    public function getUpdatedAtAttribute($value)
    {
        if (!is_null($value)) {
            $data = new DataHora($this->attributes['updated_at']);
            return $data->dataHoraInsert();
//            return $data->dataCompleta() . ' às ' . $data->hora() . ':' . $data->minuto();
        } else {
            $this->attributes['updated_at'] = null;
        }
    }

    //Modificador ->updated_at
    public function setUpdatedAtAttribute($value)
    {
        if (!is_null($value)) {
            $this->attributes['updated_at'] = (new DataHora($value))->dataHoraInsert();
        } else {
            $this->attributes['updated_at'] = null;
        }
    }

    public function Usuario()
    {
        return $this->hasOne(User::class, 'id', 'id');
    }

    public function Area()
    {
        return $this->hasOne(Area::class, 'id', 'area_id');
    }

    public function Telefones()
    {
        return $this->hasMany(ClienteTelefone::class, 'cliente_id', 'id');
    }

    public function ServicosCliente()
    {
        return $this->hasMany(ServicosCliente::class, 'cliente_id', 'id')->orderByDesc('id');
    }

    public function ServicosProspect()
    {
        return $this->hasMany(ServicosProspects::class, 'cliente_id', 'id');
    }

    public function PesquisaClimaCliente()
    {
        return $this->hasOne(PesquisaClimaCliente::class, 'cliente_id', 'id');
    }

    public function Logo()
    {
        return $this->belongsToMany(Arquivo::class, 'cliente_logotipo', 'cliente_id', 'arquivo_id');
    }

    public function Mascote()
    {
        return $this->belongsToMany(Arquivo::class, 'cliente_mascote', 'cliente_id', 'arquivo_id');
    }

    public function AreasEtiquetas()
    {
        return $this->belongsToMany(AreaEtiqueta::class, 'cliente_area_etiquetas', 'cliente_id', 'area_etiqueta_id')->withPivot(['numero_supervisor']);
    }

    public function Funcionarios()
    {
        return $this->belongsToMany(User::class, 'cliente_funcionarios', 'cliente_id', 'funcionario_id');
    }

    public function Parabens()
    {
        return $this->hasOne(ParabensEnviado::class, 'cliente_id', 'id');
    }

    public function Cargos()
    {
        return $this->belongsToMany(Vaga::class, 'cliente_cargo', 'cliente_id', 'cargo_id');
    }

    public function VagasAbertas()
    {
        return $this->hasMany(VagasAbertas::class, 'empresa_id', 'id');
    }

    public function ClienteConfig()
    {
        return $this->hasOne(ClienteConfig::class, 'cliente_id', 'id');
    }

    public function EmpresaFuncionarios()
    {
        return $this->belongsToMany(User::class, 'empresa_funcionarios', 'empresa_id', 'funcionario_id');
    }

    public function Temporaria()
    {
        return $this->hasOne(EmpresaTemporaria::class, 'empresa_id', 'id');
    }

    public function TemporariaAtiva()
    {
        return $this->Temporaria()->whereAtivo(true);
    }

    public function CarteiraAssinatura()
    {
        return $this->hasOne(CarteiraAssinatura::class, 'empresa_id', 'id');
    }

    public function CarteiraAssinaturaGestorRh()
    {
        return $this->CarteiraAssinatura()->whereAtivo(true)->whereTipo(CarteiraAssinatura::TIPO_GERENTE_OU_RH)->with('Anexos')->first();
    }

    public function CarteiraAssinaturaSesmt()
    {
        return $this->CarteiraAssinatura()->whereAtivo(true)->whereTipo(CarteiraAssinatura::TIPO_SESMT)->with('Anexos')->first();
    }

    public function Papel()
    {
        return $this->hasOne(Papel::class, 'empresa_id', 'id')->where('master', true);
    }

    public function Filiais()
    {
        return $this->hasMany(ClienteFilial::class, 'empresa_id', 'id');
    }

//    public function EmpresaHabilidades()
//    {
//        return $this->hasMany(EmpresaHabilidade::class, 'empresa_id', 'id');
//    }

    protected static function booted()
    {
        static::updating(function ($model) {
            if ($model->tipo == self::TIPO_PESSOA_JURIDICA) {
                $model->Usuario->find($model->id)->update([
                    'nome' => $model->razao_social,
                    'login' => $model->email,
                    'ativo' => $model->ativo
                ]);
            } else {
                $model->Usuario->find($model->id)->update([
                    'nome' => $model->nome,
                    'login' => $model->email,
                    'ativo' => $model->ativo
                ]);
            }
        });

        static::addGlobalScope('scopeCliente', function (Builder $builder) {
            $builder->whereIn('id', auth()->user()->ClientesEmpresa->pluck('id'));
        });
    }

}
