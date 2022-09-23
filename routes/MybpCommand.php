<?php
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
    $empresa_id = (int) $this->arguments()['empresa_id'];
    \App\Models\Sistema::grupoClinicaExame($empresa_id);
})->describe("Sincronizando Funcionarios");

Artisan::command('mybp:syncAso', function () {
    $this->info('Sincronizando data ASOs');
    \App\Models\Sistema::syncAso();
})->describe("Sincronizando data ASOs");
