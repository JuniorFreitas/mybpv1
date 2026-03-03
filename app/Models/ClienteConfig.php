<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

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
 * @property bool $schedule_treinamento_vencimento Habilitar (true) ou desabilitar (false) o schedule de Treinamento Vencimento para esta empresa
 * @property bool $assinatura_digital_habilitada Habilita a funcionalidade de assinatura digital para a empresa
 * @property int|null $limite_assinaturas_mensal Limite de documentos de assinatura digital por mês (null = sem limite)
 * @property array|null $assinatura_alerta_user_ids IDs de usuários que recebem alerta de cota
 * @property array|null $assinatura_alerta_grupo_ids IDs de grupos (papeis) que recebem alerta de cota
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
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteConfig whereScheduleTreinamentoVencimento($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteConfig whereVerificaMesVencimento($value)
 * @mixin \Eloquent
 */
class ClienteConfig extends Model
{
    use LogsActivity, HasActivitylogOptions, HasFactory;

    protected static $logName = 'ClienteConfig';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

    protected $fillable = [
        'envia_whatsapp',
        'verifica_mes_vencimento',
        'cliente_id',
        'vencimento_aso',
        'modelo_cih',
        'supervisor_etiqueta_bloqueio',
        'schedule_avaliacao_experiencia',
        'schedule_treinamento_vencimento',
        'assinatura_digital_habilitada',
        'limite_assinaturas_mensal',
        'assinatura_alerta_user_ids',
        'assinatura_alerta_grupo_ids',
        'assinatura_exibir_ip_completo',
        'assinatura_exibir_cpf_completo',
    ];

    protected $casts = [
        'envia_whatsapp' => 'boolean',
        'verifica_mes_vencimento' => 'int',
        'cliente_id' => 'int',
        'vencimento_aso' => 'int',
        'modelo_cih' => 'string',
        'supervisor_etiqueta_bloqueio' => 'boolean',
        'schedule_avaliacao_experiencia' => 'boolean',
        'schedule_treinamento_vencimento' => 'boolean',
        'assinatura_digital_habilitada' => 'boolean',
        'limite_assinaturas_mensal' => 'int',
        'assinatura_alerta_user_ids' => 'array',
        'assinatura_alerta_grupo_ids' => 'array',
        'assinatura_exibir_ip_completo' => 'boolean',
        'assinatura_exibir_cpf_completo' => 'boolean',
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
