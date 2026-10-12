<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $empresa_id
 * @property string $titulo
 * @property string $conteudo_html
 * @property string $status
 * @property int $versao
 * @property int|null $criado_por
 * @property int|null $atualizado_por
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Cliente|null $empresa
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartaOfertaTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartaOfertaTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartaOfertaTemplate publicado()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartaOfertaTemplate query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartaOfertaTemplate whereAtualizadoPor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartaOfertaTemplate whereConteudoHtml($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartaOfertaTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartaOfertaTemplate whereCriadoPor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartaOfertaTemplate whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartaOfertaTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartaOfertaTemplate whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartaOfertaTemplate whereTitulo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartaOfertaTemplate whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartaOfertaTemplate whereVersao($value)
 * @mixin \Eloquent
 */
class CartaOfertaTemplate extends Model
{
    use HasFactory, TenantTrait;

    public const STATUS_RASCUNHO = 'rascunho';
    public const STATUS_PUBLICADO = 'publicado';

    protected $table = 'carta_oferta_templates';

    protected $fillable = [
        'empresa_id',
        'titulo',
        'conteudo_html',
        'status',
        'versao',
        'criado_por',
        'atualizado_por',
    ];

    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'titulo' => 'string',
        'conteudo_html' => 'string',
        'status' => 'string',
        'versao' => 'int',
        'criado_por' => 'int',
        'atualizado_por' => 'int',
    ];

    public function scopePublicado($query)
    {
        return $query->where('status', self::STATUS_PUBLICADO);
    }

    public function empresa()
    {
        return $this->hasOne(Cliente::class, 'id', 'empresa_id');
    }

    public function criador()
    {
        return $this->belongsTo(User::class, 'criado_por');
    }

    public static function htmlPadrao(): string
    {
        return <<<HTML
<p style="text-align: center; font-weight: bold; text-transform: uppercase; margin-bottom: 1cm;">CARTA OFERTA</p>
<p style="line-height: 22pt;">Prezado(a) <strong>{{colaborador.nome}}</strong>,</p>
<p><br></p>
<p style="text-align: justify; line-height: 22pt;">Conforme processo seletivo, temos o prazer de formalizar a presente carta oferta para o cargo de <strong>{{cargo}}</strong> no setor <strong>{{setor}}</strong>.</p>
<p><br></p>
{{bloco_salario}}
<p style="line-height: 22pt;">Data de início prevista: <strong>{{data_inicio}}</strong></p>
<p><br></p>
<p style="text-align: justify; line-height: 22pt;">Ao assinar este documento, você declara estar ciente e de acordo com as condições apresentadas.</p>
<p><br></p>
<p style="line-height: 22pt;">Data: {{data_emissao}}</p>
<p><br></p>
<p><br></p>
<hr>
<p style="text-align: center; line-height: 22pt;">{{empresa.razao_social}}</p>
HTML;
    }
}
