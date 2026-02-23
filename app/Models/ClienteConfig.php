<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ClienteConfig
 *
 * @property int $id
 * @property int|null $verifica_mes_vencimento
 * @property int|null $cliente_id
 * @property bool|null $envia_whatsapp
 * @property int|null $vencimento_aso
 * @property string $modelo_cih
 * @property bool $supervisor_etiqueta_bloqueio
 * @property bool $schedule_avaliacao_experiencia Habilitar (true) ou desabilitar (false) o schedule de Avaliação de Experiência para esta empresa
 * @property-read \App\Models\Cliente|null $Cliente
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteConfig newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteConfig newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteConfig query()
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteConfig whereClienteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteConfig whereEnviaWhatsapp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteConfig whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteConfig whereModeloCih($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteConfig whereSupervisorEtiquetaBloqueio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteConfig whereVencimentoAso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteConfig whereScheduleAvaliacaoExperiencia($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteConfig whereVerificaMesVencimento($value)
 * @mixin \Eloquent
 */
class ClienteConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'envia_whatsapp',
        'verifica_mes_vencimento',
        'cliente_id',
        'vencimento_aso',
        'modelo_cih',
        'supervisor_etiqueta_bloqueio',
        'schedule_avaliacao_experiencia'
    ];

    protected $casts = [
        'envia_whatsapp' => 'boolean',
        'verifica_mes_vencimento' => 'int',
        'cliente_id' => 'int',
        'vencimento_aso' => 'int',
        'modelo_cih' => 'string',
        'supervisor_etiqueta_bloqueio' => 'boolean',
        'schedule_avaliacao_experiencia' => 'boolean'
    ];

    public $timestamps = false;

    public function Cliente()
    {
        return $this->hasOne(Cliente::class, 'id', 'cliente_id');
    }

    const TRINTA_DIAS = '30 dias';
    const QUARENTA_E_CINCO_DIAS = '45 dias';
    const SESSENTA_DIAS = '60 dias';
    const NOVENTA_DIAS = '90 dias';
    const CENTO_E_VINTE_DIAS = '120 dias';

    const CENTRO_DE_CUSTO = 'centro_de_custo';
    const AREA = 'area';
    public const MODELO_CIH = ['centro_de_custo' => 'Centro de Custo', 'area' => 'Área'];


    const LISTA_VENCIMENTOS = [
        1 => self::TRINTA_DIAS,
        5 => self::QUARENTA_E_CINCO_DIAS,
        2 => self::SESSENTA_DIAS,
        3 => self::NOVENTA_DIAS,
        4 => self::CENTO_E_VINTE_DIAS,
    ];


}
