<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use function Symfony\Component\String\s;

/**
 * App\Models\FeriasCalculoAvos
 *
 * @property int $id
 * @property int $empresa_id
 * @property int $admissao_id
 * @property int $periodo_aquisitivo_id
 * @property float $total_avos
 * @property array|null $historico
 * @property bool $atualizado_via_script
 * @property \Illuminate\Support\Carbon $ultima_atualizacao
 * @property-read \App\Models\Admissao|null $Admissao
 * @property-read \App\Models\User|null $Empresa
 * @property-read \App\Models\PeriodoAquisitivo|null $PeriodoAquisitivo
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-write mixed $ultima_atualiazao
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasCalculoAvos newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasCalculoAvos newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasCalculoAvos query()
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasCalculoAvos whereAdmissaoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasCalculoAvos whereAtualizadoViaScript($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasCalculoAvos whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasCalculoAvos whereHistorico($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasCalculoAvos whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasCalculoAvos wherePeriodoAquisitivoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasCalculoAvos whereTotalAvos($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasCalculoAvos whereUltimaAtualizacao($value)
 * @mixin \Eloquent
 */
class FeriasCalculoAvos extends Model
{
    use HasFactory, LogsActivity, TenantTrait;

    protected static $logFillable = true;
    protected static $logName = 'ferias_calculo_avos';
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

    protected $table = 'ferias_calculo_avos';
    public $timestamps = false;

    protected $fillable = [
        'admissao_id',
        'empresa_id',
        'periodo_aquisitivo_id',
        'total_avos',
        'historico',
        'atualizado_via_script',
        'ultima_atualizacao',
    ];

    protected $casts = [
        'id' => 'int',
        'admissao_id' => 'int',
        'empresa_id' => 'int',
        'periodo_aquisitivo_id' => 'int',
        'total_avos' => 'float',
        'historico' => 'json',
        'atualizado_via_script' => 'boolean',
        'ultima_atualizacao' => 'date:d/m/Y',
    ];

    public function setUltimaAtualiazaoAttribute($value)
    {
        $data = new DataHora($value);
        $this->attributes['ultima_atualizacao'] = $data->dataInsert();
    }

    public function Empresa()
    {
        return $this->hasOne(User::class, 'id', 'empresa_id');
    }

    public function PeriodoAquisitivo()
    {
        return $this->hasOne(PeriodoAquisitivo::class, 'id', 'periodo_aquisitivo_id');
    }


    public function Admissao()
    {
        return $this->hasOne(Admissao::class, 'id', 'admissao_id');
    }

    public static function somaAvosScript($dia_admissao, $mes_admissao, $ano_admissao, $periodo_aquisitivo)
    {

        $ano_atual = (int)date('Y');
        $mes_hoje = (int)date('m');
        $hoje = (new DataHora())->dataInsert();

        $avos = 2.5;
        $total_avos_admissao = 0;
        $ultima_data = "";
        $data_admissao = $ano_admissao . '-' . $mes_admissao . '-' . $dia_admissao;
        $data_admissao = (new DataHora($data_admissao))->dataInsert();

        for ($i = (int)$mes_admissao; $i <= 12; $i++) {
            if ((int)$mes_admissao == $i) {
                $data_mes_base = (new DataHora($data_admissao));
            } else {
                $data_mes_base = (new DataHora($ultima_data));
            }

            $data_mes = $data_mes_base->dataInsert();
            $ultimoDiaMes = (int)(new DataHora($data_mes_base->dataInsert()))->ultimoDiaMes();

            $mes_data_mes = $i;
            if ($mes_data_mes < 10) {
                $mes_data_mes = '0' . $mes_data_mes;
            }

            switch ((int)$ultimoDiaMes) {
                case 31:
                    if ($ano_admissao == ($ano_atual - 1)) {
                        if ((int)$mes_admissao == (int)$mes_data_mes) {
                            if ($dia_admissao <= 16) {
                                $total_avos_admissao += 2.5;
                                $historico[$ano_admissao][] = [
                                    'data_mes' => $data_mes,
                                    'data_admissao' => $data_admissao,
                                    'mes' => (new DataHora($data_mes))->mesExtM() . '/' . $data_mes_base->ano(),
                                    'avos' => $avos,
                                    'total_avos' => $total_avos_admissao,
                                    'periodo_aquisitivo' => $periodo_aquisitivo[$ano_admissao]['label'],
                                    'data_atualizacao' => (new DataHora())->dataHoraInsert(),
                                ];
                            } else {
                                if ((int)$mes_admissao == 12 && (int)$mes_data_mes == 12) {
                                    $data_mes = $ano_atual . '-01-' . $dia_admissao;
                                    $historico[$ano_admissao][] = [
                                        'data_mes' => $data_mes,
                                        'data_admissao' => $data_admissao,
                                        'mes' => (new DataHora($data_mes))->mesExtM() . '/' . $ano_atual,
                                        'avos' => $avos,
                                        'total_avos' => 2.5,
                                        'periodo_aquisitivo' => $periodo_aquisitivo[$ano_admissao]['label'],
                                        'data_atualizacao' => (new DataHora())->dataHoraInsert(),
                                    ];
                                } else {
                                    if ($mes_data_mes < 12) {
                                        $data_mes = $data_mes_base->addMes(1);
                                        $data_mes = (new DataHora($data_mes))->dataInsert();
                                        $total_avos_admissao += 2.5;
                                        $historico[$ano_admissao][] = [
                                            'data_mes' => $data_mes,
                                            'data_admissao' => $data_admissao,
                                            'mes' => (new DataHora($data_mes))->mesExtM() . '/' . $data_mes_base->ano(),
                                            'avos' => $avos,
                                            'total_avos' => $total_avos_admissao,
                                            'periodo_aquisitivo' => $periodo_aquisitivo[$ano_admissao]['label'],
                                            'data_atualizacao' => (new DataHora())->dataHoraInsert(),
                                        ];
                                    }
                                }
                            }
                        } else {
                            $total_avos_admissao += 2.5;
                            $historico[$ano_admissao][] = [
                                'data_mes' => $data_mes,
                                'data_admissao' => $data_admissao,
                                'mes' => (new DataHora($data_mes))->mesExtM() . '/' . $data_mes_base->ano(),
                                'avos' => $avos,
                                'total_avos' => $total_avos_admissao,
                                'periodo_aquisitivo' => $periodo_aquisitivo[$ano_admissao]['label'],
                                'data_atualizacao' => (new DataHora())->dataHoraInsert(),
                            ];
                        }
                    }
                    break;
                case 30:
                    if ($ano_admissao == ($ano_atual - 1)) {
                        if ((int)$mes_admissao == (int)$mes_data_mes) {
                            if ($dia_admissao <= 15) {
                                $total_avos_admissao += 2.5;
                                $historico[$ano_admissao][] = [
                                    'data_mes' => $data_mes,
                                    'data_admissao' => $data_admissao,
                                    'mes' => (new DataHora($data_mes))->mesExtM() . '/' . $data_mes_base->ano(),
                                    'avos' => $avos,
                                    'total_avos' => $total_avos_admissao,
                                    'periodo_aquisitivo' => $periodo_aquisitivo[$ano_admissao]['label'],
                                    'data_atualizacao' => (new DataHora())->dataHoraInsert(),
                                ];
                            } else {
                                $data_mes = $data_mes_base->addMes(1);
                                $data_mes = (new DataHora($data_mes))->dataInsert();
                                $total_avos_admissao += 2.5;
                                $historico[$ano_admissao][] = [
                                    'data_mes' => $data_mes,
                                    'data_admissao' => $data_admissao,
                                    'mes' => (new DataHora($data_mes))->mesExtM() . '/' . $data_mes_base->ano(),
                                    'avos' => $avos,
                                    'total_avos' => $total_avos_admissao,
                                    'periodo_aquisitivo' => $periodo_aquisitivo[$ano_admissao]['label'],
                                    'data_atualizacao' => (new DataHora())->dataHoraInsert(),
                                ];
                            }
                        } else {
                            $total_avos_admissao += 2.5;
                            $historico[$ano_admissao][] = [
                                'data_mes' => $data_mes,
                                'data_admissao' => $data_admissao,
                                'mes' => (new DataHora($data_mes))->mesExtM() . '/' . $data_mes_base->ano(),
                                'avos' => $avos,
                                'total_avos' => $total_avos_admissao,
                                'periodo_aquisitivo' => $periodo_aquisitivo[$ano_admissao]['label'],
                                'data_atualizacao' => (new DataHora())->dataHoraInsert(),
                            ];
                        }
                    }
                    break;
                case 28 :
                case 29 :
                    if ($ano_admissao == ($ano_atual - 1)) {
                        if ((int)$mes_admissao == (int)$mes_data_mes) {
                            if ($dia_admissao <= 14) {
                                $total_avos_admissao += 2.5;
                                $historico[$ano_admissao][] = [
                                    'data_mes' => $data_mes,
                                    'data_admissao' => $data_admissao,
                                    'mes' => (new DataHora($data_mes))->mesExtM() . '/' . $data_mes_base->ano(),
                                    'avos' => $avos,
                                    'total_avos' => $total_avos_admissao,
                                    'periodo_aquisitivo' => $periodo_aquisitivo[$ano_admissao]['label'],
                                    'data_atualizacao' => (new DataHora())->dataHoraInsert(),
                                ];
                            } else {
                                $data_mes = $data_mes_base->addMes(1);
                                $data_mes = (new DataHora($data_mes))->dataInsert();
                                $total_avos_admissao += 2.5;
                                $historico[$ano_admissao][] = [
                                    'data_mes' => $data_mes,
                                    'data_admissao' => $data_admissao,
                                    'mes' => (new DataHora($data_mes))->mesExtM() . '/' . $data_mes_base->ano(),
                                    'avos' => $avos,
                                    'total_avos' => $total_avos_admissao,
                                    'periodo_aquisitivo' => $periodo_aquisitivo[$ano_admissao]['label'],
                                    'data_atualizacao' => (new DataHora())->dataHoraInsert(),
                                ];
                            }
                        } else {
                            $total_avos_admissao += 2.5;
                            $historico[$ano_admissao][] = [
                                'data_mes' => $data_mes,
                                'data_admissao' => $data_admissao,
                                'mes' => (new DataHora($data_mes))->mesExtM() . '/' . $data_mes_base->ano(),
                                'avos' => $avos,
                                'total_avos' => $total_avos_admissao,
                                'periodo_aquisitivo' => $periodo_aquisitivo[$ano_admissao]['label'],
                                'data_atualizacao' => (new DataHora())->dataHoraInsert(),
                            ];
                        }
                    }
                    break;
                default:
                    break;
            }
            $ultima_data = (new DataHora($data_mes))->addMes(1);
        }

        for ($j = 1; $j <= (int)$mes_hoje; $j++) {

            if ($total_avos_admissao < 30) {
                if ((int)$mes_admissao == $j) {
                    $data_mes_base = (new DataHora($data_admissao));
                } else {
                    $data_mes_base = (new DataHora($ultima_data));
                }

                $data_mes = $data_mes_base->dataInsert();
                $ultimoDiaMes = (int)(new DataHora($data_mes_base->dataInsert()))->ultimoDiaMes();
                $mes_data_mes = $j;

                if ($mes_data_mes < 10) {
                    $mes_data_mes = '0' . $mes_data_mes;
                }

                switch ((int)$ultimoDiaMes) {
                    case 31:
                        if ((int)$mes_admissao == (int)$mes_data_mes && $ano_admissao == $ano_atual) {
                            if ($dia_admissao <= 16) {
                                $total_avos_admissao += 2.5;
                                $historico[$ano_admissao][] = [
                                    'data_mes' => $data_mes,
                                    'data_admissao' => $data_admissao,
                                    'mes' => (new DataHora($data_mes))->mesExtM() . '/' . $data_mes_base->ano(),
                                    'avos' => $avos,
                                    'total_avos' => $total_avos_admissao,
                                    'periodo_aquisitivo' => $periodo_aquisitivo[$ano_admissao]['label'],
                                    'data_atualizacao' => (new DataHora())->dataHoraInsert(),
                                ];
                            } else {
                                if ((int)$mes_admissao == 12 && (int)$mes_data_mes == 12) {
                                    $data_mes = $ano_atual . '-01-' . $dia_admissao;
                                    $historico[$ano_admissao][] = [
                                        'data_mes' => $data_mes,
                                        'data_admissao' => $data_admissao,
                                        'mes' => (new DataHora($data_mes))->mesExtM() . '/' . ($ano_atual + 1),
                                        'avos' => $avos,
                                        'total_avos' => 2.5,
                                        'periodo_aquisitivo' => $periodo_aquisitivo[$ano_admissao]['label'],
                                        'data_atualizacao' => (new DataHora())->dataHoraInsert(),
                                    ];
                                } else {
                                    if ($mes_data_mes < 12) {
                                        $data_mes = $data_mes_base->addMes(1);
                                        $data_mes = (new DataHora($data_mes))->dataInsert();
                                        $total_avos_admissao += 2.5;
                                        $historico[$ano_admissao][] = [
                                            'data_mes' => $data_mes,
                                            'data_admissao' => $data_admissao,
                                            'mes' => (new DataHora($data_mes))->mesExtM() . '/' . $data_mes_base->ano(),
                                            'avos' => $avos,
                                            'total_avos' => $total_avos_admissao,
                                            'periodo_aquisitivo' => $periodo_aquisitivo[$ano_admissao]['label'],
                                            'data_atualizacao' => (new DataHora())->dataHoraInsert(),
                                        ];
                                    }
                                }
                            }
                        } else {
                            if ($data_mes <= $hoje) {
                                $total_avos_admissao += 2.5;
                                $historico[$ano_admissao][] = [
                                    'data_mes' => $data_mes,
                                    'data_admissao' => $data_admissao,
                                    'mes' => (new DataHora($data_mes))->mesExtM() . '/' . $data_mes_base->ano(),
                                    'avos' => $avos,
                                    'total_avos' => $total_avos_admissao,
                                    'periodo_aquisitivo' => $periodo_aquisitivo[$ano_admissao]['label'],
                                    'data_atualizacao' => (new DataHora())->dataHoraInsert(),
                                ];
                            }
                        }
                        break;
                    case 30:
                        if ((int)$mes_admissao == (int)$mes_data_mes && $ano_admissao == $ano_atual) {
                            if ($dia_admissao <= 15) {
                                $total_avos_admissao += 2.5;
                                $historico[$ano_admissao][] = [
                                    'data_mes' => $data_mes,
                                    'data_admissao' => $data_admissao,
                                    'mes' => (new DataHora($data_mes))->mesExtM() . '/' . $data_mes_base->ano(),
                                    'avos' => $avos,
                                    'total_avos' => $total_avos_admissao,
                                    'periodo_aquisitivo' => $periodo_aquisitivo[$ano_admissao]['label'],
                                    'data_atualizacao' => (new DataHora())->dataHoraInsert(),
                                ];
                            } else {
                                $data_mes = $data_mes_base->addMes(1);
                                $data_mes = (new DataHora($data_mes))->dataInsert();
                                $total_avos_admissao += 2.5;
                                $historico[$ano_admissao][] = [
                                    'data_mes' => $data_mes,
                                    'data_admissao' => $data_admissao,
                                    'mes' => (new DataHora($data_mes))->mesExtM() . '/' . $data_mes_base->ano(),
                                    'avos' => $avos,
                                    'total_avos' => $total_avos_admissao,
                                    'periodo_aquisitivo' => $periodo_aquisitivo[$ano_admissao]['label'],
                                    'data_atualizacao' => (new DataHora())->dataHoraInsert(),
                                ];
                            }
                        } else {
                            if ($data_mes <= $hoje) {
                                $total_avos_admissao += 2.5;
                                $historico[$ano_admissao][] = [
                                    'data_mes' => $data_mes,
                                    'data_admissao' => $data_admissao,
                                    'mes' => (new DataHora($data_mes))->mesExtM() . '/' . $data_mes_base->ano(),
                                    'avos' => $avos,
                                    'total_avos' => $total_avos_admissao,
                                    'periodo_aquisitivo' => $periodo_aquisitivo[$ano_admissao]['label'],
                                    'data_atualizacao' => (new DataHora())->dataHoraInsert(),
                                ];
                            }
                        }
                        break;
                    case 28 :
                    case 29 :
                        if ((int)$mes_admissao == (int)$mes_data_mes && $ano_admissao == $ano_atual) {
                            if ($dia_admissao <= 14) {
                                $total_avos_admissao += 2.5;
                                $historico[$ano_admissao][] = [
                                    'data_mes' => $data_mes,
                                    'data_admissao' => $data_admissao,
                                    'mes' => (new DataHora($data_mes))->mesExtM() . '/' . $data_mes_base->ano(),
                                    'avos' => $avos,
                                    'total_avos' => $total_avos_admissao,
                                    'periodo_aquisitivo' => $periodo_aquisitivo[$ano_admissao]['label'],
                                    'data_atualizacao' => (new DataHora())->dataHoraInsert(),
                                ];
                            } else {
                                $data_mes = $data_mes_base->addMes(1);
                                $data_mes = (new DataHora($data_mes))->dataInsert();
                                $total_avos_admissao += 2.5;
                                $historico[$ano_admissao][] = [
                                    'data_mes' => $data_mes,
                                    'data_admissao' => $data_admissao,
                                    'mes' => (new DataHora($data_mes))->mesExtM() . '/' . $data_mes_base->ano(),
                                    'avos' => $avos,
                                    'total_avos' => $total_avos_admissao,
                                    'periodo_aquisitivo' => $periodo_aquisitivo[$ano_admissao]['label'],
                                    'data_atualizacao' => (new DataHora())->dataHoraInsert(),
                                ];
                            }
                        } else {
                            if ($data_mes <= $hoje) {
                                $total_avos_admissao += 2.5;
                                $historico[$ano_admissao][] = [
                                    'data_mes' => $data_mes,
                                    'data_admissao' => $data_admissao,
                                    'mes' => (new DataHora($data_mes))->mesExtM() . '/' . $data_mes_base->ano(),
                                    'avos' => $avos,
                                    'total_avos' => $total_avos_admissao,
                                    'periodo_aquisitivo' => $periodo_aquisitivo[$ano_admissao]['label'],
                                    'data_atualizacao' => (new DataHora())->dataHoraInsert(),
                                ];
                            }
                        }
                        break;
                    default:
                        break;
                }
            }
            $ultima_data = (new DataHora($data_mes))->addMes(1);
        }

        $historico[($ano_admissao)]['total_avos'] = $total_avos_admissao;
        return $historico;

    }


    public static function somaAvosScriptNew($dia_admissao, $mes_admissao, $ano_admissao, $periodo_aquisitivo)
    {

        $ano_atual = (int)date('Y');
        $mes_hoje = (int)date('m');
        $hoje = (new DataHora())->dataInsert();

        $avos = 2.5;
        $total_avos_admissao = 0;
        $ultima_data = "";
        $data_admissao = $ano_admissao . '-' . $mes_admissao . '-' . $dia_admissao;
        $data_admissao = (new DataHora($data_admissao))->dataInsert();
        $historico = [];


        for ($a = $ano_admissao; $a <= $ano_atual; $a++) {
            $avos = 2.5;
            $total_avos_admissao = 0;
            if ((int)$ano_admissao == $a) {
                for ($i = (int)$mes_admissao; $i <= 12; $i++) {
                    if ((int)$mes_admissao == $i && (int)$ano_admissao == $a) {
                        $data_mes_base = (new DataHora($data_admissao));
                    } else {
                        $data_mes_base = (new DataHora($ultima_data));
                    }

                    $data_mes = $data_mes_base->dataInsert();
                    $ultimoDiaMes = (int)(new DataHora($data_mes_base->dataInsert()))->ultimoDiaMes();

                    $mes_data_mes = $i;
                    if ($mes_data_mes < 10) {
                        $mes_data_mes = '0' . $mes_data_mes;
                    }

                    switch ((int)$ultimoDiaMes) {
                        case 31:
                            if ($ano_admissao == $a) {
                                if ((int)$mes_admissao == (int)$mes_data_mes) {
                                    if ($dia_admissao <= 16) {
                                        $total_avos_admissao += 2.5;
                                        $historico[$a][] = [
                                            'data_mes' => $data_mes,
                                            'data_admissao' => $data_admissao,
                                            'mes' => (new DataHora($data_mes))->mesExtM() . '/' . $data_mes_base->ano(),
                                            'avos' => $avos,
                                            'total_avos' => $total_avos_admissao,
                                            'periodo_aquisitivo' => $periodo_aquisitivo[$a]['label'],
                                            'data_atualizacao' => (new DataHora())->dataHoraInsert(),
                                        ];
                                    } else {
                                        if ((int)$mes_admissao == 12 && (int)$mes_data_mes == 12) {
                                            $data_mes = ($a + 1) . '-01-' . $dia_admissao;
                                            $historico[$a][] = [
                                                'data_mes' => $data_mes,
                                                'data_admissao' => $data_admissao,
                                                'mes' => (new DataHora($data_mes))->mesExtM() . '/' . ($a + 1),
                                                'avos' => $avos,
                                                'total_avos' => 2.5,
                                                'periodo_aquisitivo' => $periodo_aquisitivo[$a]['label'],
                                                'data_atualizacao' => (new DataHora())->dataHoraInsert(),
                                            ];
                                        } else {
                                            if ($mes_data_mes < 12) {
                                                $data_mes = $data_mes_base->addMes(1);
                                                $data_mes = (new DataHora($data_mes))->dataInsert();
                                                $total_avos_admissao += 2.5;
                                                $historico[$a][] = [
                                                    'data_mes' => $data_mes,
                                                    'data_admissao' => $data_admissao,
                                                    'mes' => (new DataHora($data_mes))->mesExtM() . '/' . $data_mes_base->ano(),
                                                    'avos' => $avos,
                                                    'total_avos' => $total_avos_admissao,
                                                    'periodo_aquisitivo' => $periodo_aquisitivo[$a]['label'],
                                                    'data_atualizacao' => (new DataHora())->dataHoraInsert(),
                                                ];
                                            }
                                        }
                                    }
                                } else {
                                    $total_avos_admissao += 2.5;
                                    $historico[$a][] = [
                                        'data_mes' => $data_mes,
                                        'data_admissao' => $data_admissao,
                                        'mes' => (new DataHora($data_mes))->mesExtM() . '/' . $data_mes_base->ano(),
                                        'avos' => $avos,
                                        'total_avos' => $total_avos_admissao,
                                        'periodo_aquisitivo' => $periodo_aquisitivo[$a]['label'],
                                        'data_atualizacao' => (new DataHora())->dataHoraInsert(),
                                    ];
                                }
                            }
                            break;
                        case 30:
                            if ($ano_admissao == $a) {
                                if ((int)$mes_admissao == (int)$mes_data_mes) {
                                    if ($dia_admissao <= 15) {
                                        $total_avos_admissao += 2.5;
                                        $historico[$a][] = [
                                            'data_mes' => $data_mes,
                                            'data_admissao' => $data_admissao,
                                            'mes' => (new DataHora($data_mes))->mesExtM() . '/' . $data_mes_base->ano(),
                                            'avos' => $avos,
                                            'total_avos' => $total_avos_admissao,
                                            'periodo_aquisitivo' => $periodo_aquisitivo[$a]['label'],
                                            'data_atualizacao' => (new DataHora())->dataHoraInsert(),
                                        ];
                                    } else {
                                        $data_mes = $data_mes_base->addMes(1);
                                        $data_mes = (new DataHora($data_mes))->dataInsert();
                                        $total_avos_admissao += 2.5;
                                        $historico[$a][] = [
                                            'data_mes' => $data_mes,
                                            'data_admissao' => $data_admissao,
                                            'mes' => (new DataHora($data_mes))->mesExtM() . '/' . $data_mes_base->ano(),
                                            'avos' => $avos,
                                            'total_avos' => $total_avos_admissao,
                                            'periodo_aquisitivo' => $periodo_aquisitivo[$a]['label'],
                                            'data_atualizacao' => (new DataHora())->dataHoraInsert(),
                                        ];
                                    }
                                } else {
                                    $total_avos_admissao += 2.5;
                                    $historico[$a][] = [
                                        'data_mes' => $data_mes,
                                        'data_admissao' => $data_admissao,
                                        'mes' => (new DataHora($data_mes))->mesExtM() . '/' . $data_mes_base->ano(),
                                        'avos' => $avos,
                                        'total_avos' => $total_avos_admissao,
                                        'periodo_aquisitivo' => $periodo_aquisitivo[$a]['label'],
                                        'data_atualizacao' => (new DataHora())->dataHoraInsert(),
                                    ];
                                }
                            }
                            break;
                        case 28 :
                        case 29 :
                            if ($ano_admissao == $a) {
                                if ((int)$mes_admissao == (int)$mes_data_mes) {
                                    if ($dia_admissao <= 14) {
                                        $total_avos_admissao += 2.5;
                                        $historico[$a][] = [
                                            'data_mes' => $data_mes,
                                            'data_admissao' => $data_admissao,
                                            'mes' => (new DataHora($data_mes))->mesExtM() . '/' . $data_mes_base->ano(),
                                            'avos' => $avos,
                                            'total_avos' => $total_avos_admissao,
                                            'periodo_aquisitivo' => $periodo_aquisitivo[$a]['label'],
                                            'data_atualizacao' => (new DataHora())->dataHoraInsert(),
                                        ];
                                    } else {
                                        $data_mes = $data_mes_base->addMes(1);
                                        $data_mes = (new DataHora($data_mes))->dataInsert();
                                        $total_avos_admissao += 2.5;
                                        $historico[$a][] = [
                                            'data_mes' => $data_mes,
                                            'data_admissao' => $data_admissao,
                                            'mes' => (new DataHora($data_mes))->mesExtM() . '/' . $data_mes_base->ano(),
                                            'avos' => $avos,
                                            'total_avos' => $total_avos_admissao,
                                            'periodo_aquisitivo' => $periodo_aquisitivo[$a]['label'],
                                            'data_atualizacao' => (new DataHora())->dataHoraInsert(),
                                        ];
                                    }
                                } else {
                                    $total_avos_admissao += 2.5;
                                    $historico[$a][] = [
                                        'data_mes' => $data_mes,
                                        'data_admissao' => $data_admissao,
                                        'mes' => (new DataHora($data_mes))->mesExtM() . '/' . $data_mes_base->ano(),
                                        'avos' => $avos,
                                        'total_avos' => $total_avos_admissao,
                                        'periodo_aquisitivo' => $periodo_aquisitivo[$a]['label'],
                                        'data_atualizacao' => (new DataHora())->dataHoraInsert(),
                                    ];
                                }
                            }
                            break;
                        default:
                            break;
                    }
                    $ultima_data = (new DataHora($data_mes))->addMes(1);
                }

                for ($k = 1; $k <= (int)$mes_admissao; $k++) {
                    if ($total_avos_admissao < 30) {
                        if ((int)$mes_admissao == $k && (int)$ano_admissao == $a) {
                            $data_mes_base = (new DataHora($data_admissao));
                        } else {
                            $data_mes_base = (new DataHora($ultima_data));
                        }

                        $data_mes = $data_mes_base->dataInsert();
                        $ultimoDiaMes = (int)(new DataHora($data_mes_base->dataInsert()))->ultimoDiaMes();
                        $mes_data_mes = $k;

                        if ($mes_data_mes < 10) {
                            $mes_data_mes = '0' . $mes_data_mes;
                        }

                        $total_avos_admissao += 2.5;

                        if ($a != (int)$ano_atual) {
                            $historico[$a][] = [
                                'data_mes' => $data_mes,
                                'data_admissao' => $data_admissao,
                                'mes' => (new DataHora($data_mes))->mesExtM() . '/' . $data_mes_base->ano(),
                                'avos' => $avos,
                                'total_avos' => $total_avos_admissao,
                                'periodo_aquisitivo' => $periodo_aquisitivo[$a]['label'],
                                'data_atualizacao' => (new DataHora())->dataHoraInsert(),
                            ];
                        } else {
                            if ($data_mes <= $hoje) {
                                $historico[$a][] = [
                                    'data_mes' => $data_mes,
                                    'data_admissao' => $data_admissao,
                                    'mes' => (new DataHora($data_mes))->mesExtM() . '/' . $data_mes_base->ano(),
                                    'avos' => $avos,
                                    'total_avos' => $total_avos_admissao,
                                    'periodo_aquisitivo' => $periodo_aquisitivo[$a]['label'],
                                    'data_atualizacao' => (new DataHora())->dataHoraInsert(),
                                ];
                            }
                        }

                        $ultima_data = (new DataHora($data_mes))->addMes(1);
                    }
                }
            } else {
                for ($l = (int)$mes_admissao; $l <= 12; $l++) {
                    if ($total_avos_admissao < 30) {
                        if ((int)$mes_admissao == $l && (int)$ano_admissao == $a) {
                            $data_mes_base = (new DataHora($data_admissao));
                        } else {
                            $data_mes_base = (new DataHora($ultima_data));
                        }

                        $data_mes = $data_mes_base->dataInsert();
                        $ultimoDiaMes = (int)(new DataHora($data_mes_base->dataInsert()))->ultimoDiaMes();

                        $mes_data_mes = $l;
                        if ($mes_data_mes < 10) {
                            $mes_data_mes = '0' . $mes_data_mes;
                        }
                        if ((int)$data_mes_base->ano() < $ano_atual) {
                            $total_avos_admissao += 2.5;
                            $historico[$a][] = [
                                'data_mes' => $data_mes,
                                'data_admissao' => $data_admissao,
                                'mes' => (new DataHora($data_mes))->mesExtM() . '/' . $data_mes_base->ano(),
                                'avos' => $avos,
                                'total_avos' => $total_avos_admissao,
                                'periodo_aquisitivo' => $periodo_aquisitivo[$a]['label'],
                                'data_atualizacao' => (new DataHora())->dataHoraInsert(),
                            ];
                        } else {
                            if ($data_mes <= $hoje) {
                                $total_avos_admissao += 2.5;
                                $historico[$a][] = [
                                    'data_mes' => $data_mes,
                                    'data_admissao' => $data_admissao,
                                    'mes' => (new DataHora($data_mes))->mesExtM() . '/' . $data_mes_base->ano(),
                                    'avos' => $avos,
                                    'total_avos' => $total_avos_admissao,
                                    'periodo_aquisitivo' => $periodo_aquisitivo[$a]['label'],
                                    'data_atualizacao' => (new DataHora())->dataHoraInsert(),
                                ];
                            }
                        }
                        $ultima_data = (new DataHora($data_mes))->addMes(1);
                    }
                }

                for ($m = 1; $m <= (int)$mes_admissao; $m++) {
                    if ($total_avos_admissao < 30) {
                        if ((int)$mes_admissao == $m && (int)$ano_admissao == $a) {
                            $data_mes_base = (new DataHora($data_admissao));
                        } else {
                            $data_mes_base = (new DataHora($ultima_data));
                        }

                        $data_mes = $data_mes_base->dataInsert();
                        $ultimoDiaMes = (int)(new DataHora($data_mes_base->dataInsert()))->ultimoDiaMes();

                        $mes_data_mes = $m;
                        if ($mes_data_mes < 10) {
                            $mes_data_mes = '0' . $mes_data_mes;
                        }
                        if ((int)$data_mes_base->ano() < $ano_atual) {
                            $total_avos_admissao += 2.5;
                            $historico[$a][] = [
                                'data_mes' => $data_mes,
                                'data_admissao' => $data_admissao,
                                'mes' => (new DataHora($data_mes))->mesExtM() . '/' . $data_mes_base->ano(),
                                'avos' => $avos,
                                'total_avos' => $total_avos_admissao,
                                'periodo_aquisitivo' => $periodo_aquisitivo[$a]['label'],
                                'data_atualizacao' => (new DataHora())->dataHoraInsert(),
                            ];
                        } else {
                            if ($data_mes <= $hoje) {
                                $total_avos_admissao += 2.5;
                                $historico[$a][] = [
                                    'data_mes' => $data_mes,
                                    'data_admissao' => $data_admissao,
                                    'mes' => (new DataHora($data_mes))->mesExtM() . '/' . $data_mes_base->ano(),
                                    'avos' => $avos,
                                    'total_avos' => $total_avos_admissao,
                                    'periodo_aquisitivo' => $periodo_aquisitivo[$a]['label'],
                                    'data_atualizacao' => (new DataHora())->dataHoraInsert(),
                                ];
                            }
                        }
                        $ultima_data = (new DataHora($data_mes))->addMes(1);
                    }
                }
            }
            $historico[$a]['total_avos'] = $total_avos_admissao;
        }

        return $historico;
    }


    public static function somaAvosSchedule($dia_admissao, $mes_admissao, $ano_admissao, $periodo_aquisitivo, $data_ultima_atualizacao, $total_avos)
    {

        $ano_atual = (int)date('Y');
        $mes_hoje = (int)date('m');
        $hoje = (new DataHora())->dataInsert();

        $avos = 2.5;
        $total_avos_admissao = $total_avos;
        $ultima_data = (new DataHora($data_ultima_atualizacao))->addMes(1);
        $ultima_data = (new DataHora($ultima_data))->dataInsert();
        $data_admissao = $ano_admissao . '-' . $mes_admissao . '-' . $dia_admissao;
        $data_admissao = (new DataHora($data_admissao))->dataInsert();
        $mes_ultima_atualizacao = (new DataHora($ultima_data))->mes();

        $historico = [];

        if ($ultima_data <= $hoje) {
            for ($j = (int)$mes_ultima_atualizacao; $j <= (int)$mes_hoje; $j++) {

                if ($total_avos_admissao < 30) {
                    if ((int)$mes_admissao == $j) {
                        $data_mes_base = (new DataHora($data_admissao));
                    } else {
                        $data_mes_base = (new DataHora($ultima_data));
                    }

                    $data_mes = $data_mes_base->dataInsert();
                    $ultimoDiaMes = (int)(new DataHora($data_mes_base->dataInsert()))->ultimoDiaMes();
                    $mes_data_mes = $j;

                    if ($mes_data_mes < 10) {
                        $mes_data_mes = '0' . $mes_data_mes;
                    }

                    switch ((int)$ultimoDiaMes) {
                        case 31:
                            if ((int)$mes_admissao == (int)$mes_data_mes && $ano_admissao == $ano_atual) {
                                if ($dia_admissao <= 16) {
                                    $total_avos_admissao += 2.5;
                                    $historico[$ano_admissao][] = [
                                        'data_mes' => $data_mes,
                                        'data_admissao' => $data_admissao,
                                        'mes' => (new DataHora($data_mes))->mesExtM() . '/' . $data_mes_base->ano(),
                                        'avos' => $avos,
                                        'total_avos' => $total_avos_admissao,
                                        'periodo_aquisitivo' => $periodo_aquisitivo[$ano_admissao]['label'],
                                        'data_atualizacao' => (new DataHora())->dataHoraInsert(),
                                    ];
                                } else {
                                    if ((int)$mes_admissao == 12 && (int)$mes_data_mes == 12) {
                                        $data_mes = $ano_atual . '-01-' . $dia_admissao;
                                        $historico[$ano_admissao][] = [
                                            'data_mes' => $data_mes,
                                            'data_admissao' => $data_admissao,
                                            'mes' => (new DataHora($data_mes))->mesExtM() . '/' . ($ano_atual + 1),
                                            'avos' => $avos,
                                            'total_avos' => 2.5,
                                            'periodo_aquisitivo' => $periodo_aquisitivo[$ano_admissao]['label'],
                                            'data_atualizacao' => (new DataHora())->dataHoraInsert(),
                                        ];
                                    } else {
                                        if ($mes_data_mes < 12) {
                                            $data_mes = $data_mes_base->addMes(1);
                                            $data_mes = (new DataHora($data_mes))->dataInsert();
                                            $total_avos_admissao += 2.5;
                                            $historico[$ano_admissao][] = [
                                                'data_mes' => $data_mes,
                                                'data_admissao' => $data_admissao,
                                                'mes' => (new DataHora($data_mes))->mesExtM() . '/' . $data_mes_base->ano(),
                                                'avos' => $avos,
                                                'total_avos' => $total_avos_admissao,
                                                'periodo_aquisitivo' => $periodo_aquisitivo[$ano_admissao]['label'],
                                                'data_atualizacao' => (new DataHora())->dataHoraInsert(),
                                            ];
                                        }
                                    }
                                }
                            } else {
                                if ($data_mes <= $hoje) {
                                    $total_avos_admissao += 2.5;
                                    $historico[$ano_admissao][] = [
                                        'data_mes' => $data_mes,
                                        'data_admissao' => $data_admissao,
                                        'mes' => (new DataHora($data_mes))->mesExtM() . '/' . $data_mes_base->ano(),
                                        'avos' => $avos,
                                        'total_avos' => $total_avos_admissao,
                                        'periodo_aquisitivo' => $periodo_aquisitivo[$ano_admissao]['label'],
                                        'data_atualizacao' => (new DataHora())->dataHoraInsert(),
                                    ];
                                }
                            }
                            break;
                        case 30:
                            if ((int)$mes_admissao == (int)$mes_data_mes && $ano_admissao == $ano_atual) {
                                if ($dia_admissao <= 15) {
                                    $total_avos_admissao += 2.5;
                                    $historico[$ano_admissao][] = [
                                        'data_mes' => $data_mes,
                                        'data_admissao' => $data_admissao,
                                        'mes' => (new DataHora($data_mes))->mesExtM() . '/' . $data_mes_base->ano(),
                                        'avos' => $avos,
                                        'total_avos' => $total_avos_admissao,
                                        'periodo_aquisitivo' => $periodo_aquisitivo[$ano_admissao]['label'],
                                        'data_atualizacao' => (new DataHora())->dataHoraInsert(),
                                    ];
                                } else {
                                    $data_mes = $data_mes_base->addMes(1);
                                    $data_mes = (new DataHora($data_mes))->dataInsert();
                                    $total_avos_admissao += 2.5;
                                    $historico[$ano_admissao][] = [
                                        'data_mes' => $data_mes,
                                        'data_admissao' => $data_admissao,
                                        'mes' => (new DataHora($data_mes))->mesExtM() . '/' . $data_mes_base->ano(),
                                        'avos' => $avos,
                                        'total_avos' => $total_avos_admissao,
                                        'periodo_aquisitivo' => $periodo_aquisitivo[$ano_admissao]['label'],
                                        'data_atualizacao' => (new DataHora())->dataHoraInsert(),
                                    ];
                                }
                            } else {
                                if ($data_mes <= $hoje) {
                                    $total_avos_admissao += 2.5;
                                    $historico[$ano_admissao][] = [
                                        'data_mes' => $data_mes,
                                        'data_admissao' => $data_admissao,
                                        'mes' => (new DataHora($data_mes))->mesExtM() . '/' . $data_mes_base->ano(),
                                        'avos' => $avos,
                                        'total_avos' => $total_avos_admissao,
                                        'periodo_aquisitivo' => $periodo_aquisitivo[$ano_admissao]['label'],
                                        'data_atualizacao' => (new DataHora())->dataHoraInsert(),
                                    ];
                                }
                            }
                            break;
                        case 28 :
                        case 29 :
                            if ((int)$mes_admissao == (int)$mes_data_mes && $ano_admissao == $ano_atual) {
                                if ($dia_admissao <= 14) {
                                    $total_avos_admissao += 2.5;
                                    $historico[$ano_admissao][] = [
                                        'data_mes' => $data_mes,
                                        'data_admissao' => $data_admissao,
                                        'mes' => (new DataHora($data_mes))->mesExtM() . '/' . $data_mes_base->ano(),
                                        'avos' => $avos,
                                        'total_avos' => $total_avos_admissao,
                                        'periodo_aquisitivo' => $periodo_aquisitivo[$ano_admissao]['label'],
                                        'data_atualizacao' => (new DataHora())->dataHoraInsert(),
                                    ];
                                } else {
                                    $data_mes = $data_mes_base->addMes(1);
                                    $data_mes = (new DataHora($data_mes))->dataInsert();
                                    $total_avos_admissao += 2.5;
                                    $historico[$ano_admissao][] = [
                                        'data_mes' => $data_mes,
                                        'data_admissao' => $data_admissao,
                                        'mes' => (new DataHora($data_mes))->mesExtM() . '/' . $data_mes_base->ano(),
                                        'avos' => $avos,
                                        'total_avos' => $total_avos_admissao,
                                        'periodo_aquisitivo' => $periodo_aquisitivo[$ano_admissao]['label'],
                                        'data_atualizacao' => (new DataHora())->dataHoraInsert(),
                                    ];
                                }
                            } else {
                                if ($data_mes <= $hoje) {
                                    $total_avos_admissao += 2.5;
                                    $historico[$ano_admissao][] = [
                                        'data_mes' => $data_mes,
                                        'data_admissao' => $data_admissao,
                                        'mes' => (new DataHora($data_mes))->mesExtM() . '/' . $data_mes_base->ano(),
                                        'avos' => $avos,
                                        'total_avos' => $total_avos_admissao,
                                        'periodo_aquisitivo' => $periodo_aquisitivo[$ano_admissao]['label'],
                                        'data_atualizacao' => (new DataHora())->dataHoraInsert(),
                                    ];
                                }
                            }
                            break;
                        default:
                            break;
                    }
                }
                $ultima_data = (new DataHora($data_mes))->addMes(1);
            }

            $historico[($ano_admissao)]['total_avos'] = $total_avos_admissao;
        }

        return $historico;

    }
}
