<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequisicaoVagaCustomCampo extends Model
{
    use HasFactory;
    use TenantTrait;

    protected $table = 'requisicao_vaga_custom_campos';

    public const TIPO_SIM_NAO = 'sim_nao';
    public const TIPO_TEXTO = 'texto';
    public const TIPO_TEXTAREA = 'textarea';
    public const TIPO_SELECT = 'select';

    public const TIPOS = [
        self::TIPO_SIM_NAO,
        self::TIPO_TEXTO,
        self::TIPO_TEXTAREA,
        self::TIPO_SELECT,
    ];

    protected $fillable = [
        'empresa_id',
        'label',
        'tipo',
        'opcoes',
        'obrigatorio',
        'ordem',
    ];

    protected $casts = [
        'empresa_id' => 'integer',
        'opcoes' => 'array',
        'obrigatorio' => 'boolean',
        'ordem' => 'integer',
    ];

    public function Empresa()
    {
        return $this->belongsTo(Cliente::class, 'empresa_id');
    }

    /**
     * Campos custom da empresa ordenados.
     */
    public static function porEmpresa(int $empresaId)
    {
        return static::where('empresa_id', $empresaId)->orderBy('ordem')->orderBy('id');
    }
}
