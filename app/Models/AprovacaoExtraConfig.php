<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\AprovacaoExtraConfig
 *
 * @property int $id
 * @property int $empresa_id
 * @property string $tipo_processo
 * @property string $nome_aprovacao
 * @property bool $ativo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Cliente|null $Empresa
 * @method static \Illuminate\Database\Eloquent\Builder|AprovacaoExtraConfig newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AprovacaoExtraConfig newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AprovacaoExtraConfig query()
 * @method static \Illuminate\Database\Eloquent\Builder|AprovacaoExtraConfig whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AprovacaoExtraConfig whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AprovacaoExtraConfig whereTipoProcesso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AprovacaoExtraConfig whereNomeAprovacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AprovacaoExtraConfig whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AprovacaoExtraConfig whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AprovacaoExtraConfig whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class AprovacaoExtraConfig extends Model
{
    use HasFactory, TenantTrait;

    protected $fillable = [
        'empresa_id',
        'tipo_processo',
        'nome_aprovacao',
        'usuarios_autorizados',
        'ativo',
    ];

    protected $casts = [
        'empresa_id' => 'int',
        'tipo_processo' => 'string',
        'nome_aprovacao' => 'string',
        'usuarios_autorizados' => 'array',
        'ativo' => 'boolean',
        'created_at' => 'datetime:d/m/Y à\s H:i:s',
        'updated_at' => 'datetime:d/m/Y à\s H:i:s',
    ];

    // Tipos de processo disponíveis
    const TIPO_DEMISSAO = 'demissao';
    const TIPO_FERIAS = 'ferias';
    const TIPO_MUDANCA_CARGO = 'mudanca_cargo';
    const TIPO_TRANSFERENCIA = 'transferencia';
    const TIPO_INTERMITENTE_FIXO = 'intermitente_fixo';
    const TIPO_VALOR_EXTRA = 'valor_extra';
    const TIPO_REQUISICAO_VAGA = 'requisicao_vaga';
    const TIPO_ADMISSAO = 'admissao';

    const TIPOS_PROCESSO = [
        self::TIPO_DEMISSAO => 'Demissão',
        self::TIPO_FERIAS => 'Férias',
        self::TIPO_MUDANCA_CARGO => 'Mudança de Cargo',
        self::TIPO_TRANSFERENCIA => 'Transferência',
        self::TIPO_INTERMITENTE_FIXO => 'Intermitente para Fixo',
        self::TIPO_VALOR_EXTRA => 'Valor Extra',
        self::TIPO_REQUISICAO_VAGA => 'Requisição de Vaga',
        self::TIPO_ADMISSAO => 'Admissão Prevista',
    ];

    /**
     * Relacionamento com empresa
     */
    public function Empresa()
    {
        return $this->belongsTo(Cliente::class, 'empresa_id', 'id');
    }

    /**
     * Scope para buscar apenas configurações ativas
     */
    public function scopeAtivo($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Scope para buscar por tipo de processo
     */
    public function scopeTipoProcesso($query, $tipo)
    {
        return $query->where('tipo_processo', $tipo);
    }

    /**
     * Busca a configuração ativa para um tipo de processo específico
     */
    public static function getConfigAtiva($empresaId, $tipoProcesso)
    {
        return self::withoutGlobalScopes()
            ->select('id', 'empresa_id', 'tipo_processo', 'nome_aprovacao', 'usuarios_autorizados', 'ativo')
            ->where('empresa_id', $empresaId)
            ->where('tipo_processo', $tipoProcesso)
            ->where('ativo', true)
            ->first();
    }

    /**
     * Verifica se um usuário pode aprovar como aprovação extra
     * Usuários com privilegio_gestao_rh ou privilegio_aprovar_por_rh sempre podem aprovar
     * Ou se estiver na lista de usuarios_autorizados
     */
    public function podeAprovar($userId)
    {
        // Garante que $userId seja um inteiro
        $userId = is_numeric($userId) ? (int) $userId : null;

        if (!$userId) {
            return false;
        }

        // Verificar se está na lista de usuários autorizados
        if (is_array($this->usuarios_autorizados) && in_array($userId, $this->usuarios_autorizados)) {
            return true;
        }

        // Checagem direta no banco para privilégios (sem carregar usuário/relacionamentos)
        return \App\Models\User::query()
            ->selectRaw('1')
            ->join('papeis', 'users.grupo_id', '=', 'papeis.id')
            ->join('papeis_habilidades', 'papeis.id', '=', 'papeis_habilidades.papel_id')
            ->join('habilidades', 'papeis_habilidades.habilidade_id', '=', 'habilidades.id')
            ->where('users.id', $userId)
            ->where('users.empresa_id', $this->empresa_id)
            ->where('users.ativo', true)
            ->where('users.tipo', '<>', 'Empresa')
            ->whereIn('habilidades.nome', ['privilegio_gestao_rh', 'privilegio_aprovar_por_rh'])
            ->limit(1)
            ->exists();
    }

    /**
     * Relacionamento com usuários autorizados
     */
    public function UsuariosAutorizados()
    {
        if (is_array($this->usuarios_autorizados)) {
            return \App\Models\User::select('id', 'login', 'tipo', 'empresa', 'ativo')
                ->where('ativo', true)
                ->whereIn('id', $this->usuarios_autorizados)->get();
        }
        return collect();
    }
}
