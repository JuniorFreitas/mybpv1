<?php

use App\Classes\ZapNotificacao;
use App\Imports\Admissaoimport;
use App\Models\Admissao;
use App\Models\AdmissaoAso;
use App\Models\Curriculo;
use App\Models\Sistema;
use App\Models\TelefoneCurriculo;
use App\Models\User;
use App\Models\VagasAbertas;
use App\Rules\AreaEmpresaRules;
use App\Rules\CpfValidoEmpresaRules;
use App\Rules\VagaAbertaEmpresaRules;
use App\Rules\VerificaCpfEmpresaRules;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Shared\Date;

require dirname(__DIR__) . '/vendor/autoload.php';

$app = require_once dirname(__DIR__) . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$kernel->terminate($request, $response);

ini_set('memory_limit', '-1');
ini_set('max_execution_time', '-1');

unset($argv[0]);
$import = new Admissaoimport;
\Excel::import($import, public_path('montisol_import_ok.xlsx'));
$empresa_id = 63122;
$user_id = \auth()->loginUsingId($empresa_id);
//$dados = $import->dados->map(function ($line) {
//    $file = fopen( public_path('log_montisol.txt'), 'w');
//
//
//    $getCpf = Curriculo::where('cpf', Sistema::mascaraCpf($line['cpf']))->first();
//    if ($getCpf){
//        $getCpf->update([
//            'nome' => (string)$line['nome'],
//            'naturalidade' => (string)$line['naturalidade'],
//            'email' => (string)mb_strtolower(trim($line['email'])) ?? Sistema::EMAILPADRAO,
//            'cnh' => (string)$line['cnh'],
//            'cnh_vencimento' => $line['cnh_vencimento'] ? Date::excelToDateTimeObject($line['cnh_vencimento'])->format('d/m/Y') : null,
//            'estado_civil' => (string)$line['estado_civil'],
//            'rg' => (string)preg_replace("/[^0-9]/", "", $line['rg']),
//            'rg_data_emissao' => $line['rg_emissao'] ? Date::excelToDateTimeObject($line['rg_emissao'])->format('d/m/Y') : null,
//            'nascimento' => $line['nascimento'] ? Date::excelToDateTimeObject($line['nascimento'])->format('d/m/Y') : null,
//            'sexo' => mb_strtoupper($line['sexo']) == "M" ? "Masculino" : "Feminino",
//            'filiacao_pai' => (string)$line['pai'],
//            'filiacao_mae' => (string)$line['mae'],
//            'pcd' => mb_strtolower(trim($line['pcd'])) == "sim",
//            'cid' => (string)$line['cid'],
//            'vaga_pretendida' => intval($line['cod_vaga']),
//        ]);
//      echo $getCpf->id.' - Achei o CPF'."\n";
//
//    }else{
//        echo 'Não achei o CPF '.Sistema::mascaraCpf($line['cpf']) ."\n";
//        fwrite($file,  'CPF não encontrado: '.Sistema::mascaraCpf($line['cpf']) ."\n");
//    }
//    fclose($file);
//});
$file = fopen(public_path('log_montisol.txt'), 'w');
foreach ($import->dados as $key => $line) {
    $getCpf = Curriculo::where('cpf', Sistema::mascaraCpf($line['cpf']))->first();
    if ($getCpf) {
        echo $getCpf->id . ' - Achei o CPF' . "\n";
    } else {
        echo "CPF DIGITADO " . $line['cpf']. PHP_EOL;
        $linha = $key+2;
        fwrite($file, "Linha - {$linha} - CPF não encontrado: " . $line['cpf']." - ".$line['nome']. PHP_EOL);
    }
}
fclose($file);
//print_r($dados);
die();
$import->dados->map(function ($line) {
    return [
        "curriculo" => [
            'cpf' => Sistema::mascaraCpf($line['cpf']),
            "nome" => (string)$line['nome'],
            "naturalidade" => (string)$line['naturalidade'],
            "email" => (string)mb_strtolower(trim($line['email'])) ?? Sistema::EMAILPADRAO,
            "cnh" => (string)$line['cnh'],
            "cnh_vencimento" => $line['cnh_vencimento'] ? Date::excelToDateTimeObject($line['cnh_vencimento'])->format('d/m/Y') : null,
            "estado_civil" => (string)$line['estado_civil'],
            "rg" => (string)preg_replace("/[^0-9]/", "", $line['rg']),
            "rg_data_emissao" => $line['rg_emissao'] ? Date::excelToDateTimeObject($line['rg_emissao'])->format('d/m/Y') : null,
            "nascimento" => $line['nascimento'] ? Date::excelToDateTimeObject($line['nascimento'])->format('d/m/Y') : null,
            "sexo" => mb_strtoupper($line['sexo']) == "M" ? "Masculino" : "Feminino",
            "filiacao_pai" => (string)$line['pai'],
            "filiacao_mae" => (string)$line['mae'],
            "pcd" => mb_strtolower(trim($line['pcd'])) == "sim",
            "cid" => (string)$line['cid'],
            "vaga_pretendida" => intval($line['cod_vaga']),
            "telefone" => [
                "whatsapp" => mb_strtolower(trim($line['whatsapp'])) == "sim" ? "whatsapp" : "celular",
                "numero" => Sistema::mascaraTelefone($line['telefone_numero']),
            ],
            "endereco" => [
                "cep" => Sistema::mascaraCep($line['cep']),
                "logradouro" => (string)$line['endereco'],
                "numero" => (string)$line['numero'],
                "complemento" => (string)$line['complemento'],
                "bairro" => (string)$line['bairro'],
                "municipio" => (string)$line['municipio'],
                "uf" => (string)$line['uf'],
            ],
        ],
        "admissao" => [
            "area_etiqueta_id" => $line['cod_area'],
            "centro_custo_id" => $line['centro_custo'],
            "filial" => $line['filial'] == 's',
            "centro_custo_filial_id" => $line['centro_custo_filial_id'] ?? null,
            "data_entrega_area" => $line['data_entrega_area'] ? Date::excelToDateTimeObject($line['data_entrega_area'])->format('d/m/Y') : null,
            "salario" => number_format(floatval($line['salario']), 2, ',', '.'),
            "pis" => (string)$line['pis'],
            "ctps_numero" => (string)$line['ctps_numero'],
            "ctps_serie" => (string)$line['ctps_serie'],
            "ctps_data_emissao" => $line['ctps_data_emissao'] ? Date::excelToDateTimeObject($line['ctps_data_emissao'])->format('d/m/Y') : null,
            "titulo_eleitor_numero" => (string)$line['titulo_eleitor_numero'],
            "titulo_eleitor_sessao" => (string)$line['titulo_eleitor_sessao'],
            "titulo_eleitor_zona" => (string)$line['titulo_eleitor_zona'],
            "tipo_admissao" => mb_strtoupper($line['tipo_admissao']),
            "data_admissao" => Date::excelToDateTimeObject(trim((string)$line['data_admissao']))->format('d/m/Y'),
            "data_aso" => Date::excelToDateTimeObject(trim((string)$line['data_aso']))->format('d/m/Y'),
            "admissao_encerramento" => $line['admissao_encerramento'] ? Date::excelToDateTimeObject($line['admissao_encerramento'])->format('d/m/Y') : null,
            "prazo_experiencia" => ucfirst(trim($line['prazo_experiencia'])),
            "encaminhado_documento" => mb_strtolower(trim($line['encaminhado_documento'])) == "sim",
            "encaminhado_documento_data" => $line['encaminhado_documento_data'] ? Date::excelToDateTimeObject($line['encaminhado_documento_data'])->format('d/m/Y') : null,
            "encaminhado_exame" => mb_strtolower(trim($line['encaminhado_exame'])) == "sim",
            "encaminhado_exame_data" => $line['encaminhado_exame_data'] ? Date::excelToDateTimeObject($line['encaminhado_exame_data'])->format('d/m/Y') : null,
            "encaminhado_treinamento" => mb_strtolower(trim($line['encaminhado_treinamento'])) == "sim",
            "encaminhado_treinamento_data" => $line['encaminhado_treinamento_data'] ? Date::excelToDateTimeObject($line['encaminhado_treinamento_data'])->format('d/m/Y') : null,
//            "numero_cracha" => (string)$line['numero_cracha'],
            "numero_cracha" => $jayParsedAry->filter(function ($item) use ($line) {
                    return $item['cpf'] == $line['cpf'];
                })->first()['numero_cracha'] ?? (string)$line['numero_cracha'],
            "matricula" => (string)$line['matricula'],
            "banco" => [
                "nome" => (string)$line['banco'],
                "agencia" => (string)$line['agencia'],
                "conta" => (string)$line['conta'],
                "pix" => mb_strtolower(trim($line['pix'])) == "sim",
                "pix_tipo_chave" => $line['pix_tipo_chave'],
                "pix_chave" => (string)$line['pix_chave']
            ]
        ]
    ];
})->filter(function ($item) {
    return $item['curriculo']['cpf'] != '';
})->unique('curriculo.cpf');

if ($dados->count() == 0) {
    return response()->json([
        'msg' => 'Nenhum registro encontrado',
        "status" => 'error'
    ], 400);
}

$dados = $dados->toArray();


try {
    $count = 0;
    DB::beginTransaction();
    foreach ($dados as $item) {
        Auth::loginUsingId($user_id);

        $usuario = User::where('empresa_id', $empresa_id)->whereHas('Curriculo', function ($q) use ($item) {
            $q->where('cpf', $item['curriculo']['cpf']);
        });

        $dadosUser = [
            'nome' => $item['curriculo']['nome'],
            'login' => $item['curriculo']['email'],
            'password' => Sistema::SenhaCpf($item['curriculo']['cpf']),
            'tipo' => User::FUNCIONARIO,
            'ativo' => true,
            'temp' => false,
            'termos' => false,
            'empresa_id' => $empresa_id
        ];


        if ($usuario->count() == 0) {
//            \Log::info("Iniciando criação do Colaborador - " . $item['curriculo']['nome']);
            echo "Criando o Colaborador - " . $item['curriculo']['nome'] . "  \n";
            $usuario = User::create($dadosUser);
        } else {
            echo "Atualizando do Colaborador - " . $item['curriculo']['nome'] . "  \n";
            $usuario = $usuario->first();
            $usuario->update($dadosUser);
        }

        //Cria ou atualiza os dados bancarios
        $dadosConta = [
            'banco' => $item['admissao']['banco']['nome'],
            'agencia' => $item['admissao']['banco']['agencia'],
            'conta' => $item['admissao']['banco']['conta'],
            'pix' => $item['admissao']['banco']['pix'],
            'tipochavepix' => $item['admissao']['banco']['pix_tipo_chave'],
            'chavepix' => $item['admissao']['banco']['pix_chave'],
        ];

        $usuario->BancoConta ? $usuario->BancoConta->update($dadosConta) : $usuario->BancoConta()->create($dadosConta);

        //Cria ou atualiza o Curriculo
        $dadosCurriculo = [
            'id' => $usuario->id,
            'cpf' => $item['curriculo']['cpf'],
            'nome' => $item['curriculo']['nome'],
            'estado_civil' => $item['curriculo']['estado_civil'],
            'cnh' => $item['curriculo']['cnh'],
            'cnh_vencimento' => $item['curriculo']['cnh_vencimento'],
            'email' => $item['curriculo']['email'],
            'nascimento' => $item['curriculo']['nascimento'],
            'naturalidade' => $item['curriculo']['naturalidade'],
            'logradouro' => $item['curriculo']['endereco']['logradouro'],
            'end_numero' => $item['curriculo']['endereco']['numero'],
            'complemento' => $item['curriculo']['endereco']['complemento'],
            'bairro' => $item['curriculo']['endereco']['bairro'],
            'municipio' => $item['curriculo']['endereco']['municipio'],
            'uf' => $item['curriculo']['endereco']['uf'],
            'cep' => $item['curriculo']['endereco']['cep'],
            'uf_vaga' => VagasAbertas::find($item['curriculo']['vaga_pretendida'])->Municipio->uf,
            'municipio_id' => VagasAbertas::find($item['curriculo']['vaga_pretendida'])->Municipio->id,
            'rg' => $item['curriculo']['rg'],
            'rg_data_emissao' => $item['curriculo']['rg_data_emissao'],
            'filiacao_pai' => $item['curriculo']['filiacao_pai'],
            'filiacao_mae' => $item['curriculo']['filiacao_mae'],
            'sexo' => $item['curriculo']['sexo'],
            'pcd' => $item['curriculo']['pcd'],
            'cid' => $item['curriculo']['cid'],
            'vaga_pretendida' => $item['curriculo']['vaga_pretendida']
        ];

        $curriculo = Curriculo::find($usuario->id);

        if (is_null($curriculo)) {
            $curriculo = Curriculo::create($dadosCurriculo);
        } else {
            $curriculo->update($dadosCurriculo);
        }

        //Cria ou atualiza o Telefone
        $dadosTel = [
            'curriculo_id' => $curriculo->id,
            'tipo' => $item['curriculo']['telefone']['whatsapp'],
            'pais' => "55",
            'numero' => $item['curriculo']['telefone']['numero'],
            'principal' => true,
        ];

        $telefone_id = $curriculo->Telefones()->updateOrCreate($dadosTel)->id;

        //Cria ou atualiza o Feedback
        $curriculo->Feedback()->updateOrCreate([
            'curriculo_id' => $curriculo->id,
            'selecionado' => 'sim',
            'vaga_id' => $item['curriculo']['vaga_pretendida'],
            'cliente_id' => $empresa_id,
            'empresa_id' => $empresa_id,
            'interesse' => true,
            'contato_realizado' => true,
            'telefone_id' => $telefone_id,
            'vagas_abertas_id' => $item['curriculo']['vaga_pretendida']
        ]);

        //Criações de entrevistas
        $curriculo->Feedback->parecerRh()->updateOrCreate(['nota' => 9]);
        $curriculo->Feedback->parecerRota()->updateOrCreate([]);
        $curriculo->Feedback->parecerTecnica()->updateOrCreate([]);
        $curriculo->Feedback->parecerTeste()->updateOrCreate([]);
        $curriculo->Feedback->individualRh()->updateOrCreate([]);
        $curriculo->Feedback->gestorRh()->updateOrCreate([]);
        $curriculo->Feedback->entrevistaRh()->updateOrCreate([]);

        //Criações de resultado integrado
        $curriculo->Feedback->ResultadoIntegrado()->updateOrCreate([
            'responsavel_envio' => 'importacao',
            'documentos_entregue' => false,
            'encaminhado_exame' => (bool)$item['admissao']['encaminhado_exame'],
            'encaminhado_exame_data' => $item['admissao']['encaminhado_exame_data'],
            'encaminhado_treinamento' => (bool)$item['admissao']['encaminhado_treinamento'],
            'encaminhado_treinamento_data' => $item['admissao']['encaminhado_treinamento_data'],
        ]);

        //Criações de admissao
        $curriculo->Feedback->Admissao()->updateOrCreate([
            'centro_custo_id' => $item['admissao']['centro_custo_id'],
            'area_etiqueta_id' => $item['admissao']['area_etiqueta_id'],
            'data_entrega_area' => $item['admissao']['data_entrega_area'],
            'data_admissao' => $item['admissao']['data_admissao'],
            'cargo' => VagasAbertas::find($item['curriculo']['vaga_pretendida'])->Vaga->nome,
            'funcao' => VagasAbertas::find($item['curriculo']['vaga_pretendida'])->Vaga->nome,
            'status' => Admissao::STATUS_ADMISSAO_ADMITIDO,
            'salario' => $item['admissao']['salario'],
            'pis' => $item['admissao']['pis'],
            'tipo_admissao' => $item['admissao']['tipo_admissao'],
            'prazo_experiencia' => $item['admissao']['prazo_experiencia'],
            'data_encerramento' => $item['admissao']['admissao_encerramento'],
            'usuario_id' => auth()->user()->id,
        ]);

        Admissao::tipoAdmissaoAvalNoventaCriarAtualizar($curriculo->Feedback->id, $item['admissao']['tipo_admissao'], $item['admissao']['prazo_experiencia'], $item['admissao']['data_admissao'], $item['admissao']['admissao_encerramento']);
        AdmissaoAso::criarAtualizar($curriculo->Feedback->Admissao->id, $empresa_id, $item['admissao']['data_aso']);

        //DadosAdmissoes
        $curriculo->Feedback->Admissao->DadosAdmissoes()->updateOrCreate([
            'ctps_numero' => $item['admissao']['ctps_numero'],
            'ctps_serie' => $item['admissao']['ctps_serie'],
            'ctps_data_emissao' => $item['admissao']['ctps_data_emissao'],
            'titulo_eleitor_numero' => $item['admissao']['titulo_eleitor_numero'],
            'titulo_eleitor_sessao' => $item['admissao']['titulo_eleitor_sessao'],
            'titulo_eleitor_zona' => $item['admissao']['titulo_eleitor_zona'],
        ]);
        DB::commit();
    }

    $empresa = User::select(['nome'])->find($empresa_id);
    \Log::info('Importação realizada com sucesso da Empresa ' . $empresa->nome);
    (new ZapNotificacao())->enviar([
        'enviado_id' => $user_id,
        'telefone' => '5598999023762',
        'mensagem' => 'Importação realizada com sucesso da Empresa ' . $empresa->nome . ' - ' . $empresa_id
    ]);
    return response()->json(['msg' => 'Importação realizada com sucesso'], 201);
} catch (\Exception $e) {
    DB::rollback();
    \Log::error($e->getMessage() . ' - ' . $e->getLine());

    echo $e->getMessage() . ' - ' . $e->getLine() . "\n";
//    return response()->json(['error' => $e->getMessage() . ' - ' . $e->getLine()], 500);
}
