<?php

use App\Models\Ferias;
use MasterTag\DataHora;

function sdd()
{
    $data = new \MasterTag\DataHora();
    $nomeUser = exec("whoami", $user);
    $dataCompleta = $data->dataCompleta() . ' às ' . $data->horaCompleta();

    if ($data->hora() >= 05 && $data->hora() < 12) {
        $saudacao = "Bom dia {$nomeUser}";
    }
    if ($data->hora() >= 12 && $data->hora() < 18) {
        $saudacao = "Boa tarde {$nomeUser}";
    }
    if ($data->hora() >= 18 && $data->hora() <= 24 || $data->hora() >= 00 && $data->hora() < 05) {
        $saudacao = "Boa noite, {$nomeUser}! você esta executando o comando de DUMP no dia {$dataCompleta}";
    }

    return [
        'saudacao' => $saudacao,
        'user' => $nomeUser,
        'data' => $dataCompleta
    ];
}

Artisan::command('mybp:dump', function () {
    $this->comment(sdd()['saudacao']);
    $this->comment("Aguarde enquanto estamos criando o dump");
    exec("ssh root@159.89.154.53 'sh /var/www/shell/npmdump.sh'", $output);
    $this->comment(implode(PHP_EOL, $output));
})->describe("Dump do banco de dados e envio para o S3");

Artisan::command('mybp:model {nome}', function () {
    $tipodemodel = $this->choice('Qual tipo da aplicação?', ['web', 'api'], 'web');
    if ($tipodemodel == 'web') {
        $choice = $this->choice('Você quer criar o controller "JuniorController" com Resourse e Migration? ', ['sim', 'não'], 'sim');
        if ($choice == 'sim') {
            $this->info("Criando a Model {$this->argument('nome')}");
            exec("php artisan make:model {$this->argument('nome')} -m");
            $this->info("Model Criada com sucesso {$this->argument('nome')}");
            $this->info("Criando o Controller {$this->argument('nome')}Controller com Resource");
            exec("php artisan make:controller {$this->argument('nome')}Controller --resource --model=Models/{$this->argument('nome')}");
            $this->info("Controller Criado com sucesso {$this->argument('nome')}");
        } else {
            $this->info("Criando a Model {$this->argument('nome')}");
            exec("php artisan make:model {$this->argument('nome')} -m");
            $this->info("Model Criada com sucesso {$this->argument('nome')}");
            $this->info("Criando o Controller {$this->argument('nome')}Controller");
            exec("php artisan make:controller {$this->argument('nome')}Controller --model=Models/{$this->argument('nome')}");
            $this->info("Controller Criado com sucesso {$this->argument('nome')}");
        }
    } else {
//        $choice = $this->choice('Você quer criar o controller "Api/JuniorController" com Resourse e Migration? ', ['sim', 'não'], 'sim');
//        if ($choice == 'sim') {
//            $this->info("Criando a Model {$this->argument('nome')}");
//            exec("php artisan make:model {$this->argument('nome')} -m");
//            $this->info("Model Criada com sucesso {$this->argument('nome')}");
//            $this->info("Criando o Controller {$this->argument('nome')}Controller com Resource");
//            exec("php artisan make:controller {$this->argument('nome')}Controller --resource --model=Models/{$this->argument('nome')}");
//            $this->info("Controller Criado com sucesso {$this->argument('nome')}");
//        } else {
//            $this->info("Criando a Model {$this->argument('nome')}");
//            exec("php artisan make:model {$this->argument('nome')} -m");
//            $this->info("Model Criada com sucesso {$this->argument('nome')}");
//            $this->info("Criando o Controller {$this->argument('nome')}Controller");
//            exec("php artisan make:controller {$this->argument('nome')}Controller --model=Models/{$this->argument('nome')}");
//            $this->info("Controller Criado com sucesso {$this->argument('nome')}");
//        }
    }
});

Artisan::command('mybp:deploy', function () {
    $ambiente = $this->choice('Qual ambiente ?', ['Desenvolvimento', 'Produção'], 'Desenvolvimento');
    $this->info($ambiente);
    if ($ambiente == 'Desenvolvimento') {
//        $this->line('Display this on the screen');
//        $this->info('Display this on the screen');
//        $this->comment('Display this on the screen');
//        $this->question('Display this on the screen');
//        $this->error('Something went wrong!');

//        exec('ssh  ubuntu@100.24.12.79 cd /home/ubuntu/www/mybp');
    }
    if ($ambiente == 'Produção') {

    }

    \App\Models\Sistema::telegram("[MyBP] -- Deploy no ambiente de [{$ambiente}] solicitado por " . sdd()['user'] . ' no dia ' . sdd()['data']);
})->describe("Deploy em ambiente de Desenvolvimento ou Produção");

Artisan::command('mybp:syncfuncionarios', function () {
    $this->info('Sincronizando Funcionarios');
    \App\Models\Sistema::syncFuncionarios();
})->describe("Sincronizando Funcionarios");

Artisan::command('mybp:grupoClinicaExame {empresa_id?}', function () {
    $this->info('Sincronizando Clinica');
    $empresa_id = (int)$this->arguments()['empresa_id'];
    \App\Models\Sistema::grupoClinicaExame($empresa_id);
})->describe("Sincronizando Funcionarios");

Artisan::command('mybp:syncAso', function () {
    $this->info('Sincronizando data ASOs');
    \App\Models\Sistema::syncAso();
})->describe("Sincronizando data ASOs");

Artisan::command('mybp:ferias', function () {
    try {
        $ferias_gozando_sistema = \DB::table('ferias')
            ->where('status_aprovacao_gestor', Ferias::STATUS_APROVADO)
            ->where('aprovado_via_script', true)
            ->where('data_saida', '<=', (new DataHora())->dataInsert())
            ->where('data_retorno', '>=', (new DataHora())->dataInsert())
            ->update([
                'status_ferias' => Ferias::STATUS_GOZANDO,
                'data_status_ferias' => (new DataHora())->dataInsert()
            ]);
        Log::info("Ferias gozando");

        $ferias_gozando_rh = \DB::table('ferias')
            ->where('status_aprovacao_rh', Ferias::STATUS_APROVADO)
            ->where('data_saida', '<=', (new DataHora())->dataInsert())
            ->where('data_retorno', '>=', (new DataHora())->dataInsert())
            ->update([
                'status_ferias' => Ferias::STATUS_GOZANDO,
                'data_status_ferias' => (new DataHora())->dataInsert()
            ]);
        Log::info("Ferias gozando rh");

        $ferias_gozadas_sistema = \DB::table('ferias')
            ->where('status_aprovacao_gestor', Ferias::STATUS_APROVADO)
            ->where('aprovado_via_script', true)
            ->where('data_retorno', '<', (new DataHora())->dataInsert())
            ->update([
                'status_ferias' => Ferias::STATUS_GOZADA,
                'data_status_ferias' => (new DataHora())->dataInsert()
            ]);

        Log::info("Ferias gozadas");

        $ferias_gozadas_rh = \DB::table('ferias')
            ->where('status_aprovacao_rh', Ferias::STATUS_APROVADO)
            ->where('data_retorno', '<', (new DataHora())->dataInsert())
            ->update([
                'status_ferias' => Ferias::STATUS_GOZADA,
                'data_status_ferias' => (new DataHora())->dataInsert()
            ]);

        Log::info("Ferias gozando rh");

        // NAO FAZER AGORA - AGUARDANDO DEFINICAO DE REGRAS DE NEGOCIO (DANY)
    } catch (\Exception $e) {
        \Log::error($e->getMessage());
    }
})->describe("Sincronizando Ferias");

Artisan::command('mybp:calculoAvos', function () {
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
})->describe("calculoAvos");
