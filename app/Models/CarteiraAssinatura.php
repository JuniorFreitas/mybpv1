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
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $Anexos
 * @property-read int|null $anexos_count
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

    const TIPO_GERENTE_OU_RH = 'Gerente/Rh';
    const TIPO_SESMT = 'Técnico de Segurança';

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

    public function Anexos()
    {
        return $this->belongsToMany(Arquivo::class, 'carteira_assinaturas_anexos', 'carteira_assinatura_id', 'arquivo_id');
    }

    public function AssinaturaSesmt()
    {
        $assinatura = $this->whereTipo(self::TIPO_SESMT)->first();
//        $assinatura->
    }

    public function AssinaturaGerente()
    {
        return $this->whereTipo(self::TIPO_GERENTE_OU_RH)->with('Anexos')->first();
    }
}
