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
use Illuminate\Support\Facades\Validator;


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
\Excel::import($import, base_path('scripts/xls/2023-11-26_importacao_safemed.xlsx'));

$empresa_id = 66216;
$user_id = $empresa_id;
Auth::loginUsingId($user_id);
$count = 0;


$ignoreCpf = ['020.988.633-14', '444.841.603-82', '563.713.903-25', '019.527.053-39', '610.413.423-81', '056.314.293-65'];
//dd($import->dados);

//$dados = $import->dados->whereNotIn($ignoreCpf)->map(function ($line) use ($empresa_id, &$count) {


$dados = $import->dados->map(function ($line) use ($empresa_id, &$count) {
    /*    DB::beginTransaction();
        $cadastraCargo = firstOrCreateCargo($line['vaga'], $empresa_id);
        $vagaAberta = firstOrCreateVagaAberta($cadastraCargo->id, $line['vaga_cidade'], $empresa_id, $cadastraCargo->nome);

        $centro_custo = firstOrCreateCentroCusto($line['centro_custo'], $empresa_id);

        if ($line['filial'] == 'sim') {
            $filial = firstFilial($line['cnpj']);
            $centro_custo_filial = firstOrCreateCentroCustoFilial($empresa_id, $centro_custo->id, $filial->id);
        }
        $count++;
        echo "Linha: " . $count . " CNPJ: {$line['cnpj']} - NOME: {$line['nome']}" . PHP_EOL;

        DB::commit();*/


    $vagaAberta = VagasAbertas::select(['id', 'vaga_id', 'municipio_id'])->with(
        'Vaga:id,nome',
        'Municipio:id,nome,uf'
    )->whereId($line['vaga'])->first()->toArray();


    $arrayDados = ["curriculo" => [
        'cpf' => Sistema::mascaraCpf($line['cpf']),
        "nome" => (string)$line['nome'],
        "naturalidade" => null,
        "email" => (string)mb_strtolower(trim($line['email'])) ?? Sistema::EMAILPADRAO,
        "cnh" => null,
        "cnh_vencimento" => null,
        "estado_civil" => null,
        "rg" => null,
        "rg_data_emissao" => null,
        "nascimento" => (new \MasterTag\DataHora($line['nascimento']))->dataInsert(),
//            "nascimento" => '2001-01-01',
        "sexo" => null,
        "filiacao_pai" => null,
        "filiacao_mae" => null,
        "pcd" => null,
        "cid" => null,
        "vaga_pretendida" => intval($vagaAberta['id']),
        "vaga_id" => intval($vagaAberta['vaga_id']),
        'uf_vaga' => $vagaAberta['municipio']['uf'],
        'municipio_id' => $vagaAberta['municipio']['id'],
        "telefone" => [
            "whatsapp" => "celular",
            "numero" => Sistema::mascaraTelefone($line['telefone_numero']),
        ],
        "endereco" => [
            "cep" => Sistema::mascaraCep($line['cep']) ?? null,
            "logradouro" => (string)$line['endereco'] ?? null,
            "numero" => (string)$line['numero'] ?? null,
            "complemento" => null,
            "bairro" => (string)$line['bairro'] ?? null,
            "municipio" => (string)$line['municipio'] ?? null,
            "uf" => (string)$line['uf'] ?? null,
        ],
    ],
        "admissao" => [
            "cargo" => $vagaAberta['vaga']['nome'],
            "funcao" => $vagaAberta['vaga']['nome'],
            "area_etiqueta_id" => null,
            "centro_custo_id" => $line['centro_custo_id'],
            "filial" => $line['filial'] == 'sim',
            "centro_custo_filial_id" => $line['filial'] == 'sim' ? $line['centro_custo_filial_id'] : null,
            "data_entrega_area" => (new \MasterTag\DataHora($line['data_admissao']))->dataInsert(),
            "salario" => number_format(0.00, 2, ',', '.'),
            "pis" => null,
            "ctps_numero" => null,
            "ctps_serie" => null,
            "ctps_data_emissao" => null,
            "titulo_eleitor_numero" => null,
            "titulo_eleitor_sessao" => null,
            "titulo_eleitor_zona" => null,
            "tipo_admissao" => Admissao::TIPO_ADMISSAO_FIXO,
            "data_admissao" => (new \MasterTag\DataHora($line['data_admissao']))->dataInsert(),
            "data_aso" => (new \MasterTag\DataHora($line['data_admissao']))->dataInsert(),
            "admissao_encerramento" => null,
            "prazo_experiencia" => Admissao::QUARENTAECINCO_MAIS_QUARENTAECINCO,
            "encaminhado_documento" => null,
            "encaminhado_documento_data" => null,
            "encaminhado_exame" => null,
            "encaminhado_exame_data" => null,
            "encaminhado_treinamento" => null,
            "encaminhado_treinamento_data" => null,
            "numero_cracha" => (string)$line['numero_cracha'],
            "matricula" => (string)$line['matricula'],
            "banco" => [
                "nome" => null,
                "agencia" => null,
                "conta" => null,
                "pix" => false,
                "pix_tipo_chave" => null,
                "pix_chave" => null
            ]
        ]
    ];


    return $arrayDados;

})->filter(function ($item) {
    return $item['curriculo']['cpf'] != '';
})->unique('curriculo.cpf');


if ($dados->count() == 0) {
    return response()->json([
        'msg' => 'Nenhum registro encontrado',
        "status" => 'error'
    ], 400);
};

$dados = $dados->toArray();


$outputFile = base_path('scripts/xls/logs_import/' . (new \MasterTag\DataHora())->dataInsert() . '_importacao_safemed_log.txt');
$outputHandle = fopen($outputFile, 'w');

$inserido = 1;

$dadosCollection = collect($dados)->chunk(200)->each(function ($records) use ($empresa_id, $count, $outputHandle) {
    foreach ($records as $record) {
        $data = (array)$record;

        $validation = Validator::make($data, [
            'curriculo.cpf' => ['required',
                'min:14',
                'regex:/^\d{3}\.\d{3}\.\d{3}\-\d{2}$/',
                new CpfValidoEmpresaRules($empresa_id),
                new VerificaCpfEmpresaRules($empresa_id, true)
            ],
            'curriculo.nome' => 'required|max:255',
//            'curriculo.nascimento' => 'required|date_format:d/m/Y|regex:/^\d{2}\/\d{2}\/\d{4}$/',
//            'curriculo.rg' => 'nullable|max:200',
//            'curriculo.rg_data_emissao' => 'nullable|max:10|regex:/^\d{2}\/\d{2}\/\d{4}$/',
//            'curriculo.filiacao_pai' => 'max:255',
//            'curriculo.filiacao_mae' => 'required|max:255',
//            'curriculo.pcd' => 'required|boolean',
//            'curriculo.cid' => 'required_if:curriculo.pcd,true',
            'curriculo.vaga_pretendida' => ['required', new VagaAbertaEmpresaRules($empresa_id)],
            'curriculo.endereco.cep' => 'required|min:9',
            'curriculo.endereco.logradouro' => 'required|max:255',
            'curriculo.endereco.numero' => 'nullable|max:10',
            'curriculo.endereco.complemento' => 'nullable|max:255',
            'curriculo.endereco.bairro' => 'required|max:255',
            'curriculo.endereco.municipio' => 'required|max:255',
            'curriculo.endereco.uf' => 'required|max:2|regex:/^[A-Z]{2}$/',
//            'curriculo.telefone.whatsapp' => 'required|in:' . implode(",", TelefoneCurriculo::TIPOS),
            'curriculo.telefone.numero' => 'required|max:16',
//            'admissao.area_etiqueta_id' => ['required', new AreaEmpresaRules($empresa_id)],
//            'admissao.data_entrega_area' => 'nullable|date_format:d/m/Y|regex:/^\d{2}\/\d{2}\/\d{4}$/',
            'admissao.salario' => 'max:100',
//            'admissao.pis' => 'nullable|max:200',
//            'admissao.ctps_numero' => 'nullable|max:200',
//            'admissao.ctps_serie' => 'nullable|max:200',
//            'admissao.ctps_data_emissao' => 'nullable|date_format:d/m/Y|regex:/^\d{2}\/\d{2}\/\d{4}$/',
//            'admissao.titulo_eleitor_numero' => 'nullable|max:200',
//            'admissao.titulo_eleitor_sessao' => 'nullable|max:200',
//            'admissao.titulo_eleitor_zona' => 'nullable|max:200',
//            'admissao.data_aso' => 'required|date_format:d/m/Y|regex:/^\d{2}\/\d{2}\/\d{4}$/',
//            'admissao.data_admissao' => 'required|date_format:d/m/Y|regex:/^\d{2}\/\d{2}\/\d{4}$/',
            'admissao.tipo_admissao' => "required|in:" . implode(",", Admissao::TODOS_TIPOS_ADMISSAO),
//            'admissao.admissao_encerramento' => [
//                function ($attribute, $value, $fail) use ($data) {
//                    if (in_array($data['admissao']['tipo_admissao'], [Admissao::TIPO_ADMISSAO_INTERMITENTE, Admissao::TIPO_ADMISSAO_DETERMINADO, Admissao::TIPO_ADMISSAO_TEMPORARIO])
//                        && is_null($value)
//                        && preg_match("/^\d{2}\/\d{2}\/\d{4}$/", $value) == 0
//                    ) {
//                        $fail("O {$attribute} deve ser preenchido com formato da data dd/mm/aaaa");
//                    }
//                }],
//            'admissao.prazo_experiencia' => [function ($attribute, $value, $fail) use ($data) {
//                $i = (int)explode('.', $attribute)[0];
//                if ($data['admissao']['tipo_admissao'] == Admissao::TIPO_ADMISSAO_FIXO && !in_array($value, Admissao::TODOS_PRAZOS)) {
//                    $fail("A linha {$attribute} sÃ³ pode ser um dos tipos de prazo: " . implode(',', Admissao::TODOS_PRAZOS));
//                }
//            }],
            'admissao.banco.nome' => 'nullable|max:200',
            'admissao.banco.agencia' => 'nullable|max:200',
            'admissao.banco.conta' => 'nullable|max:200',
//            'admissao.banco.pix' => 'boolean',
            'admissao.banco.pix_tipo_chave' => 'required_if:admissao.banco.pix,true|max:200',
            'admissao.banco.pix_chave' => 'required_if:admissao.banco.pix,true|max:200',
        ]);

        if ($validation->fails()) {
            $msg = 'Erro ao fazer validacao para importaÃ§Ã£o - do CPF: ' . $record['curriculo']['cpf'];
            fwrite($outputHandle, $msg);
            print_r([
                'msg' => 'Erro ao fazer importaÃ§Ã£o',
                'erros' => $validation->errors() . ' - do CPF: ' . $record['curriculo']['cpf'],
            ]);
        }

        if ($validation->valid()) {
            try {
                DB::beginTransaction();

                $usuario = User::where('empresa_id', $empresa_id)->whereHas('Curriculo', function ($q) use ($record) {
                    $q->where('cpf', $record['curriculo']['cpf']);
                });

                $dadosUser = [
                    'nome' => $record['curriculo']['nome'],
                    'login' => $record['curriculo']['email'],
                    'password' => Sistema::SenhaCpf($record['curriculo']['cpf']),
                    'tipo' => User::FUNCIONARIO,
                    'ativo' => true,
                    'temp' => false,
                    'termos' => false,
                    'empresa_id' => $empresa_id
                ];

                if ($usuario->count() == 0) {
                    echo "Criando o Colaborador - " . $record['curriculo']['nome'] . "  \n";
                    $novoUsuario = true;
                    $usuario = User::create($dadosUser);
                } else {
                    echo "Atualizando do Colaborador - " . $record['curriculo']['nome'] . "  \n";
                    $novoUsuario = false;
                    $usuario = $usuario->first();
                    $usuario->update($dadosUser);
                }

                //Cria ou atualiza os dados bancarios
                $dadosConta = [
                    'banco' => $record['admissao']['banco']['nome'],
                    'agencia' => $record['admissao']['banco']['agencia'],
                    'conta' => $record['admissao']['banco']['conta'],
                    'pix' => $record['admissao']['banco']['pix'],
                    'tipochavepix' => $record['admissao']['banco']['pix_tipo_chave'],
                    'chavepix' => $record['admissao']['banco']['pix_chave'],
                ];


                $usuario->BancoConta ? $usuario->BancoConta->update($dadosConta) : $usuario->BancoConta()->create($dadosConta);

                //Cria ou atualiza o Curriculo
                $dadosCurriculo = [
                    'id' => $usuario->id,
                    'cpf' => $record['curriculo']['cpf'],
                    'nome' => $record['curriculo']['nome'],
                    'estado_civil' => $record['curriculo']['estado_civil'],
                    'cnh' => $record['curriculo']['cnh'],
                    'cnh_vencimento' => $record['curriculo']['cnh_vencimento'],
                    'email' => $record['curriculo']['email'],
                    'nascimento' => $record['curriculo']['nascimento'],
                    'naturalidade' => $record['curriculo']['naturalidade'],
                    'logradouro' => $record['curriculo']['endereco']['logradouro'],
                    'end_numero' => $record['curriculo']['endereco']['numero'],
                    'complemento' => $record['curriculo']['endereco']['complemento'],
                    'bairro' => $record['curriculo']['endereco']['bairro'],
                    'municipio' => $record['curriculo']['endereco']['municipio'],
                    'uf' => $record['curriculo']['endereco']['uf'],
                    'cep' => $record['curriculo']['endereco']['cep'],
                    'uf_vaga' => $record['curriculo']['uf_vaga'],
                    'municipio_id' => $record['curriculo']['municipio_id'],
//                    'uf_vaga' => VagasAbertas::find($record['curriculo']['vaga_pretendida'])->Municipio->uf,
//                    'municipio_id' => VagasAbertas::find($record['curriculo']['vaga_pretendida'])->Municipio->id,
                    'rg' => $record['curriculo']['rg'],
                    'rg_data_emissao' => $record['curriculo']['rg_data_emissao'],
                    'filiacao_pai' => $record['curriculo']['filiacao_pai'],
                    'filiacao_mae' => $record['curriculo']['filiacao_mae'],
                    'sexo' => $record['curriculo']['sexo'],
                    'pcd' => $record['curriculo']['pcd'],
                    'cid' => $record['curriculo']['cid'],
                    'vaga_pretendida' => $record['curriculo']['vaga_pretendida']
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
                    'tipo' => $record['curriculo']['telefone']['whatsapp'],
                    'pais' => "55",
                    'numero' => $record['curriculo']['telefone']['numero'],
                    'principal' => true,
                ];

                $telefone_id = $curriculo->Telefones()->updateOrCreate([
                    'curriculo_id' => $curriculo->id
                ], $dadosTel)->id;

                //Cria ou atualiza o Feedback
                $curriculo->Feedback()->updateOrCreate(
                    [
                        'curriculo_id' => $curriculo->id,
                        'cliente_id' => $empresa_id,
                        'empresa_id' => $empresa_id,
                        'deleted_at' => null
                    ],
                    [
                        'curriculo_id' => $curriculo->id,
                        'selecionado' => 'sim',
                        'vaga_id' => $record['curriculo']['vaga_id'],
                        'cliente_id' => $empresa_id,
                        'empresa_id' => $empresa_id,
                        'interesse' => true,
                        'contato_realizado' => true,
                        'telefone_id' => $telefone_id,
                        'vagas_abertas_id' => $record['curriculo']['vaga_pretendida']
                    ]);


                //CriaÃ§Ãµes de entrevistas
                $curriculo->Feedback->parecerRh()->updateOrCreate(['nota' => 9]);
                $curriculo->Feedback->parecerRota()->updateOrCreate([]);
                $curriculo->Feedback->parecerTecnica()->updateOrCreate([]);
                $curriculo->Feedback->parecerTeste()->updateOrCreate([]);
                $curriculo->Feedback->individualRh()->updateOrCreate([]);
                $curriculo->Feedback->gestorRh()->updateOrCreate([]);
                $curriculo->Feedback->entrevistaRh()->updateOrCreate([]);

                //CriaÃ§Ãµes de resultado integrado
                $curriculo->Feedback->ResultadoIntegrado()->updateOrCreate(
                    [
                        'feedback_id' => $curriculo->Feedback->id,
                    ],
                    [
                        'responsavel_envio' => 'importacao',
                        'documentos_entregue' => false,
                        'encaminhado_exame' => (bool)$record['admissao']['encaminhado_exame'],
                        'encaminhado_exame_data' => $record['admissao']['encaminhado_exame_data'],
                        'encaminhado_treinamento' => (bool)$record['admissao']['encaminhado_treinamento'],
                        'encaminhado_treinamento_data' => $record['admissao']['encaminhado_treinamento_data'],
                    ]);

//                dd($record['admissao']['data_admissao']);
                //CriaÃ§Ãµes de admissao
                $curriculo->Feedback->Admissao()->updateOrCreate([
                    'feedback_id' => $curriculo->Feedback->id,
                    'deleted_at' => null
                ], [
                    'centro_custo_id' => $record['admissao']['centro_custo_id'],
                    'area_etiqueta_id' => $record['admissao']['area_etiqueta_id'],
                    'data_entrega_area' => $record['admissao']['data_entrega_area'],
                    'data_admissao' => (new \MasterTag\DataHora($record['admissao']['data_admissao']))->dataInsert(),
                    'cargo' => $record['admissao']['cargo'],
                    'funcao' => $record['admissao']['funcao'],
                    'status' => Admissao::STATUS_ADMISSAO_ADMITIDO,
                    'salario' => $record['admissao']['salario'],
                    'pis' => $record['admissao']['pis'],
                    'tipo_admissao' => $record['admissao']['tipo_admissao'],
                    'prazo_experiencia' => $record['admissao']['prazo_experiencia'],
                    'data_encerramento' => $record['admissao']['admissao_encerramento'],
                    'usuario_id' => auth()->user()->id,
                ]);


                Admissao::tipoAdmissaoAvalNoventaCriarAtualizar($curriculo->Feedback->id, $record['admissao']['tipo_admissao'], $record['admissao']['prazo_experiencia'], $record['admissao']['data_admissao'], $record['admissao']['admissao_encerramento']);
                AdmissaoAso::criarAtualizar($curriculo->Feedback->Admissao->id, $empresa_id, $record['admissao']['data_aso']);

                //DadosAdmissoes
                $curriculo->Feedback->Admissao->DadosAdmissoes()->updateOrCreate(
                    [
                        'admissao_id' => $curriculo->Feedback->Admissao->id,
                    ],
                    [
                        'ctps_numero' => $record['admissao']['ctps_numero'],
                        'ctps_serie' => $record['admissao']['ctps_serie'],
                        'ctps_data_emissao' => $record['admissao']['ctps_data_emissao'],
                        'titulo_eleitor_numero' => $record['admissao']['titulo_eleitor_numero'],
                        'titulo_eleitor_sessao' => $record['admissao']['titulo_eleitor_sessao'],
                        'titulo_eleitor_zona' => $record['admissao']['titulo_eleitor_zona'],
                    ]);
                DB::commit();
                $msg = $novoUsuario ? 'Novo Colaborador ' : 'Atualizado Colaborador ' . 'Importacao realizada com sucesso do CPF: ' . $record['curriculo']['cpf'] . ' - ' . (new \MasterTag\DataHora())->dataHoraCompleta() . PHP_EOL;
                fwrite($outputHandle, $msg);

//                print_r([
//                    'msg' => 'Importacao realizada com sucesso do CPF: ' . $record['curriculo']['cpf'],
//                    'Usuario:' => $novoUsuario ? 'Novo' : 'Atualizado',
//                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                $msg = 'Erro ao importar do CPF: ' . $e->getMessage() . ' - ' . $record['curriculo']['cpf'] . ' - ' . (new \MasterTag\DataHora())->dataHoraCompleta() . PHP_EOL;
                fwrite($outputHandle, $msg);
                print_r([
                    'msg' => 'Erro ao importar',
                    'do CPF: ' . $record['curriculo']['cpf'],
                    'erro' => $e->getMessage(),
//                    'data' => print_r($data, 1)
                ]);
            }
        }

    }
});

fclose($outputHandle);


function firstOrCreateCargo($nome, $empresa_id, $ativo = true)
{
    $cargo = \App\Models\Vaga::firstOrCreate([
        'nome' => $nome,
        'empresa_id' => $empresa_id,
        'ativo' => $ativo
    ]);

    return $cargo;
}

function firstOrCreateVagaAberta($vaga_id, $municipio_id, $empresa_id, $titulo, $descricao = '', $ativo_sistema = true, $ativo = true)
{
    $vaga = VagasAbertas::firstOrCreate([
        'vaga_id' => $vaga_id,
        'municipio_id' => $municipio_id,
        'empresa_id' => $empresa_id,
        'titulo' => $titulo,
        'descricao' => $descricao,
        'ativo_sistema' => $ativo_sistema,
        'ativo' => $ativo
    ]);

    return $vaga;
}

function firstOrCreateCentroCusto($nome, $empresa_id, $ativo = true)
{
    $centro_custo = \App\Models\CentroCusto::firstOrCreate([
        'label' => $nome,
        'gestor_id' => null,
        'empresa_id' => $empresa_id,
        'ativo' => $ativo
    ]);

    return $centro_custo;
}

function firstOrCreateCentroCustoFilial($empresa_id, $centro_custo_id, $filial_id, $ativo = true)
{
    $centro_custo_filial = \App\Models\CentroCustoFilial::firstOrCreate([
        'empresa_id' => $empresa_id,
        'centro_custo_id' => $centro_custo_id,
        'cliente_filial_id' => $filial_id,
        'ativo' => $ativo
    ]);

    return $centro_custo_filial;
}

function firstFilial($cnpj)
{
    $filial = \App\Models\ClienteFilial::whereJsonContains('dados->cnpj', $cnpj)->first();
    return $filial;
}

//dd(Date::excelToDateTimeObject($import->dados[100]['data_aso'])->format('Y-m-d'));

function convertExcelDate($date, $format = 'd-m-Y')
{
    try {
        $date = Date::excelToDateTimeObject($date);
        return $date->format($format);
    } catch (\Exception $e) {
        dd($e->getMessage());
    }
}
