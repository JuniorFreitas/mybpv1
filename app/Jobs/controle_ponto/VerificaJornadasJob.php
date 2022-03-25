<?php

namespace App\Jobs\controle_ponto;

use App\Models\Feriado;
use App\Models\OcorrenciaJornada;
use App\Models\PontoEletronico;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MasterTag\DataHora;

class VerificaJornadasJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct() {

    }

    //Comentar esse metodo se for usar com Job
    public function __invoke() {
        $this->handle();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {

        try{
            \DB::beginTransaction();
            $fim = new DataHora();
            //$fim = new DataHora('01/06/2021 23:59:59');
            $fim->setSegundo(59);
            $fim->setMinuto(59);
            $fim->setHora(23);
            $fim->subtrairDia(1);

            $inicio = new DataHora($fim->dataHoraInsert());
            $inicio->setSegundo(0);
            $inicio->setMinuto(0);
            $inicio->setHora(0);

            $listaDeEmprasaID = \DB::table('users')->where('ativo', 1)->where('temp', 0)
                ->selectRaw('DISTINCT empresa_id')->get(['empresa_id'])->pluck('empresa_id')->toArray();

            foreach ($listaDeEmprasaID as $empresa_id) {

                $empresa = User::find($empresa_id);
                $config = null;
                $todosFuncionariosAtivos = $empresa->EmpresaFuncionarios()->whereAtivo(true)->whereTemp(false)->get();

                foreach ($todosFuncionariosAtivos as $funcionario) {
                    $escala = $funcionario->EscalasFuncionario()->withoutGlobalScopes()->first(); // sempre a primeira escala
                    $jornada = PontoEletronico::getJornadaAtual($escala->id, $fim->dataCompleta());
                    if ($config == null) {
                        $config = $funcionario->ConfigEmpresa;
                    }
                    //Funcinário tem essa jornada de ontem ?
                    $ocorrencia = $jornada->Ocorrencia;
                    $temFeriado = Feriado::whereEmpresaId($empresa_id)->whereData($fim->dataInsert())->first();
                    $nenhumPonto = PontoEletronico::withoutGlobalScopes()->whereEmpresaId($empresa_id)->whereFuncionarioId($funcionario->id)
                        ->whereJornadaId($jornada->id)->whereDate('created_at', $fim->dataInsert())->count() == 0 ? true : false;

                    if ($nenhumPonto) { // nao teve nenhum ponto aquele dia..
                        if ($ocorrencia->trabalhado) { //..era para trabalhar..
                            //pode ser feito aqui tambem ferias do funcionário,banco de horas.. etc
                            if ($temFeriado) {
                                //echo "Ia pegar falta mais foi feriado {$funcionario->nome} ({$funcionario->id}) com a ocorrencia {$ocorrencia->descricao} {$fim->dataHoraInsert()}\n";
                                $ponto = PontoEletronico::withoutGlobalScopes()->create([
                                    'empresa_id' => $empresa_id,
                                    'funcionario_id' => $funcionario->id,
                                    'jornada_id' => $jornada->id,
                                    'ocorrencia_id' => OcorrenciaJornada::FERIADO,
                                    'tempo_limite_falta' => $config->tempo_limite_falta,
                                    'tempo_limite_saida' => $config->tempo_limite_saida,
                                    'limite_tolerancia' => $config->limite_tolerancia,
                                    'verificado' => true,
                                    'justificativa' => "Feriado: {$temFeriado->descricao}",
                                    'duracao' => $jornada->getTotalMinutos(),
                                    'created_at' => $fim->dataInsert() . ' 23:59:59',
                                    'updated_at' => $fim->dataInsert() . ' 23:59:59',
                                ]);
                            } else {
                                //pegou falta mesmo
                                //echo "Pegou falta {$funcionario->nome} ({$funcionario->id})  com a ocorrencia {$ocorrencia->descricao} {$fim->dataHoraInsert()}\n";
                                $ponto = PontoEletronico::withoutGlobalScopes()->create([
                                    'empresa_id' => $empresa_id,
                                    'funcionario_id' => $funcionario->id,
                                    'jornada_id' => $jornada->id,
                                    'ocorrencia_id' => OcorrenciaJornada::FALTA,
                                    'tempo_limite_falta' => $config->tempo_limite_falta,
                                    'tempo_limite_saida' => $config->tempo_limite_saida,
                                    'limite_tolerancia' => $config->limite_tolerancia,
                                    'verificado' => true,
                                    'duracao' => $jornada->getTotalMinutos(),
                                    'created_at' => $fim->dataInsert() . ' 23:59:59',
                                    'updated_at' => $fim->dataInsert() . ' 23:59:59',
                                ]);
                                // atualizando as duracoes realizadas na saida
                                $ponto->recalcularDuracoes();
                            }
                        } else {
                            //senao era alguma folga...
                            //echo "Ocorrencia nao trabalhada para {$funcionario->nome} ({$funcionario->id}) com a ocorrencia {$ocorrencia->descricao} {$fim->dataHoraInsert()}\n";
                            $ponto = PontoEletronico::withoutGlobalScopes()->create([
                                'empresa_id' => $empresa_id,
                                'funcionario_id' => $funcionario->id,
                                'jornada_id' => $jornada->id,
                                'ocorrencia_id' => $ocorrencia->id,
                                'tempo_limite_falta' => $config->tempo_limite_falta,
                                'tempo_limite_saida' => $config->tempo_limite_saida,
                                'limite_tolerancia' => $config->limite_tolerancia,
                                'verificado' => true,
                                'duracao' => $jornada->Ocorrencia->conta_horas ? $jornada->getTotalMinutos() : 0,
                                'created_at' => $fim->dataInsert() . ' 23:59:59',
                                'updated_at' => $fim->dataInsert() . ' 23:59:59',
                            ]);
                        }
                    } else {
                        // se encontrou algum ponto que faltou dar saida.. finalizar com a saida 23:59:59. Quem tiver com ponto aberto bate novamente
                        /*$ponto = PontoEletronico::withoutGlobalScopes()->whereEmpresaId($empresa_id)->whereFuncionarioId($funcionario->id)
                            ->whereJornadaId($jornada->id)->whereOcorrenciaId($ocorrencia->id)
                            ->whereDate('created_at', $fim->dataInsert())
                            ->whereHas('PeriodosEmAberto')
                            ->first();
                        //if($ponto->PeriodosEmAberto()->count() > 0){
                        if ($ponto) {
                            $periodo = $ponto->PeriodosEmAberto()->first();
                            $periodo->saida = $fim->dataHoraInsert();
                            $periodo->facial_saida = false;
                            $periodo->lat_saida = null;
                            $periodo->long_saida = null;
                            $periodo->minutos = DataHora::diferencaMinutos($periodo->entrada, $fim->horaInsert());

                            $periodo->arquivo_id_saida = null;
                            $periodo->save();

                            echo "Fehcnado a ocorrencia de {$funcionario->nome}({$funcionario->id}) com a ocorrencia {$ocorrencia->descricao} {$fim->dataHoraInsert()}\n";
                        }*/
                    }
                }
            }

            //Verificar as jornadas que estão dentro que nao passaram dos limites de horario, ou ocorrencia nao trabalhadas (tudo certo com essas)
            PontoEletronico::withoutGlobalScopes()->where(function($q){
                $q->where(function($q){
                    $q->whereHas('Periodos',function($q){
                        $q->select(\DB::raw('sum(minutos) as duracao'))
                            ->whereNotNull('entrada')->whereNotNull('saida');
                        $q->havingRaw('duracao <= ponto_eletronicos.duracao + ponto_eletronicos.limite_tolerancia');
                    })
                        ->WhereHas('Periodos',function($q) {
                            $q->select(\DB::raw('sum(minutos) as duracao'))
                                ->whereNotNull('entrada')->whereNotNull('saida');
                            $q->havingRaw('duracao >= ponto_eletronicos.duracao - ponto_eletronicos.limite_tolerancia');
                        });
                })
                    ->orWhereHas('Ocorrencia',function($q){
                        $q->whereTrabalhado(false);
                    });
            })
                //->whereDate('created_at','<',$hoje->dataInsert())
                ->whereBetween('created_at',[$inicio->dataHoraInsert(),$fim->dataHoraInsert()])
                ->whereVerificado(false)
                ->update(['verificado'=>true]);

            \DB::commit();
        }catch (\Exception $e){
            \DB::rollBack();
            echo "deu um erro {$e->getMessage()}\n";
        }





    }
}
