<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\CarteiraAssinatura
 *
 * @property int $id
 * @property int $empresa_id
 * @property int|null $arquivo_id
 * @property string $nome
 * @property string $tipo
 * @property bool $ativo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Arquivo|null $Arquivo
 * @property-read \App\Models\Cliente $Empresa
 * @method static \Illuminate\Database\Eloquent\Builder|CarteiraAssinatura newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CarteiraAssinatura newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CarteiraAssinatura query()
 * @method static \Illuminate\Database\Eloquent\Builder|CarteiraAssinatura whereArquivoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CarteiraAssinatura whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CarteiraAssinatura whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CarteiraAssinatura whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CarteiraAssinatura whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CarteiraAssinatura whereNome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CarteiraAssinatura whereTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CarteiraAssinatura whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CarteiraAssinatura extends Model
{
    use HasFactory, TenantTrait;

    protected $fillable = [
        'empresa_id',
        'arquivo_id',
        'nome',
        'tipo',
        'ativo',
    ];

    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'arquivo_id' => 'int',
        'nome' => 'string',
        'tipo' => 'string',
        'ativo' => 'boolean',
    ];

    const TIPO_GERENTE_OU_RH = 'GERENTE OU RH';
    const TIPO_SESMT = 'SESMT';

    const TIPOS = [
        self::TIPO_GERENTE_OU_RH,
        self::TIPO_SESMT,
    ];

    public function Arquivo()
    {
        return $this->belongsTo(Arquivo::class);
    }

    public function Empresa()
    {
        return $this->belongsTo(Cliente::class, 'empresa_id');
    }
}
