<?php

namespace App\Jobs\Rotinas;

use App\Models\Ferias;
use App\Models\Sistema;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MasterTag\DataHora;

class JobCalculoAvos implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 3;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    public function __construct()
    {

    }

    public function __invoke()
    {
        $this->handle();
    }

    public function handle()
    {
        try {
            $ano_atual = (int) date('Y');
            $mes_hoje = (int) date('m');
            $hoje = (new DataHora())->dataInsert();
            $primeiro_dia_ano = $ano_atual.'-01-01';

            $admitidos = \DB::select("SELECT
                    a.id as admissao_id, a.feedback_id, a.data_admissao
                FROM admissoes a
                    INNER JOIN feedback_curriculos fc on a.feedback_id = fc.id
                WHERE a.data_admissao >= $primeiro_dia_ano
                AND a.feedback_id not in (SELECT
                        feedback_id
                    FROM demissaos)
                AND fc.deleted_at is null
                ORDER BY a.data_admissao ASC");

            $todosPeriodosAquisitivos = \DB::select("SELECT * FROM periodos_aquisitivos WHERE ano_inicial >= $ano_atual");
            $periodo_aquisitivo = [];
            foreach ($todosPeriodosAquisitivos as $pa){
                $periodo_aquisitivo[$pa->ano_inicial] = [
                    'id' => $pa->id,
                    'label' => $pa->label,
                    'ano_inicial' => $pa->ano_inicial,
                    'ano_final' => $pa->ano_final,
                ];
            }

            $periodo_aquisitivo_atual = $periodo_aquisitivo[$ano_atual]['id'];

            foreach ($admitidos as $a){
                $data_admissao = (new DataHora($a->data_admissao));

                $avos = \DB::select("SELECT fca.id, fca.historico, fca.total_avos, fca.ultima_atualizacao
                    FROM ferias_calculo_avos fca
                        WHERE fca.admissao_id = $a->admissao_id
                        AND fca.periodo_aquisitivo_id = $periodo_aquisitivo_atual
                        AND fca.total_avos > 0
                    ORDER BY ultima_atualizacao DESC");

                if(count($avos) > 0) {
                    foreach ($avos as $avo){
                        $historico = json_decode($avo->historico, true);
                        $ultima_atualizacao = end($historico);
                        $data_mes = $ultima_atualizacao['data_mes'];
                        $total_avos = $ultima_atualizacao['total_avos'];
                        $dia_admissao = $data_admissao->dia();
                        $mes_admissao = $data_admissao->mes();
                        $ano_admissao = $data_admissao->ano();

                        $historico_avos = \App\Models\FeriasCalculoAvos::somaAvosSchedule($dia_admissao, $mes_admissao, $ano_admissao, $periodo_aquisitivo, $data_mes, $total_avos);

                        if(!empty($historico_avos)){
                            $historico = json_decode($avo->historico, JSON_UNESCAPED_SLASHES);
                            $ultimo_total_avos_admissao = $historico_avos[$ano_admissao]['total_avos'];
                            unset($historico_avos[$ano_admissao]['total_avos']);
                            $novo_historico = json_decode(json_encode(array_values(json_decode(json_encode($historico_avos[$ano_admissao]), true))));
                            $historico_avos_cad_admissao = array_merge($historico, $novo_historico);
                            $historico_avos_cad_admissao = json_encode($historico_avos_cad_admissao, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);

                            $calculo_avos_admissao = [
                                'total_avos' => $ultimo_total_avos_admissao,
                                'historico' => $historico_avos_cad_admissao,
                                'ultima_atualizacao' => (new DataHora())->dataHoraInsert(),
                            ];

                            \DB::table('ferias_calculo_avos')->where('id', $avo->id) ->update($calculo_avos_admissao);
                        }
                    }
                }
            }


//            $ferias_gozando_rh = \DB::table('ferias')
//                ->where('status_aprovacao_rh', Ferias::STATUS_APROVADO)
//                ->where('data_saida','<=',(new DataHora())->dataInsert())
//                ->where('data_retorno','>=',(new DataHora())->dataInsert())
//                ->update([
//                    'status_ferias' => Ferias::STATUS_GOZANDO,
//                    'data_status_ferias' => (new DataHora())->dataInsert()
//                ]);
//
//            $ferias_gozadas_sistema = \DB::table('ferias')
//                ->where('status_aprovacao_gestor', Ferias::STATUS_APROVADO)
//                ->where('aprovado_via_script', true)
//                ->where('data_retorno','<',(new DataHora())->dataInsert())
//                ->update([
//                    'status_ferias' => Ferias::STATUS_GOZADA,
//                    'data_status_ferias' => (new DataHora())->dataInsert()
//                ]);
//
//            $ferias_gozadas_rh = \DB::table('ferias')
//                ->where('status_aprovacao_rh', Ferias::STATUS_APROVADO)
//                ->where('data_retorno','<',(new DataHora())->dataInsert())
//                ->update([
//                    'status_ferias' => Ferias::STATUS_GOZADA,
//                    'data_status_ferias' => (new DataHora())->dataInsert()
//                ]);


            // NAO FAZER AGORA - AGUARDANDO DEFINICAO DE REGRAS DE NEGOCIO (DANY)
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
        }

    }
}
