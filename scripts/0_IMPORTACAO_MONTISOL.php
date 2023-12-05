<?php

use App\Classes\ZapNotificacao;
use App\Imports\Admissaoimport;
use App\Models\Admissao;
use App\Models\AdmissaoAso;
use App\Models\Curriculo;
use App\Models\ExameFuncionario;
use App\Models\Formulario;
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
use MasterTag\DataHora;
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
\Excel::import($import, base_path('scripts/xls/planilha_final_montisol_04-12-2023_analisada.xlsx'));

$empresa_id = 63122;
$user_id = $empresa_id;
Auth::loginUsingId($user_id);
$count = 0;


$dados = $import->dados->whereNotIn('cpf', [
    '399.295.822-15'
])
//$dados = $import->dados->whereIn('cpf', [
//    '882.974.032-20',
//    '533.628.102-68',
//    '849.520.322-72',
//    '012.367.283-00',
//    '820.808.443-34',
//    '006.084.602-06',
//    '020.893.222-41',
//    '508.776.312-04',
//    '987.924.232-72',
//    '854.275.002-00',
//    '185.992.187-60',
//    '041.479.553-98',
//    '743.237.842-87',
//    '977.705.232-49',
//    '625.484.412-53',
//    '626.167.812-04'
//])
    ->map(function ($line) use ($empresa_id, &$count) {
        $count++;
        echo "Linha: " . $count . " - " . $line['nome'] . PHP_EOL;
        $cod_municipio = $line['filial'] == 'SIM' ? 4772 : 2743;

//    $vaga = VagasAbertas::whereHas('Vaga', function ($q) use ($line, $empresa_id) {
//        $q->where('nome', $line['cod_vaga'])->where('empresa_id', $empresa_id);
//    })->where('municipio_id', $cod_municipio)
//        ->where('empresa_id', $empresa_id)->first();


        $vaga = VagasAbertas::where('id', $line['cod_vaga'])->where('municipio_id', $cod_municipio)
            ->where('empresa_id', $empresa_id)->first();


        if (!$vaga) {
            $cargo = \App\Models\Vaga::firstOrCreate(['nome' => $line['cod_vaga'], 'empresa_id' => $empresa_id, 'ativo' => true], ['nome' => $line['cod_vaga'], 'empresa_id' => $empresa_id, 'ativo' => true]);
            $vaga = VagasAbertas::create([
                'vaga_id' => $cargo->id,
                'municipio_id' => $cod_municipio,
                'empresa_id' => $empresa_id,
                'titulo' => $line['cod_vaga'],
                'descricao' => '',
                'ativo_sistema' => true,
                'ativo' => true
            ]);
            echo "Vaga criada: " . $vaga->id . " - " . $vaga->titulo . PHP_EOL;
        }


        $cc_id = \App\Models\CentroCustoFilial::where('centro_custo_id', $line['centro_custo_id'])->where('empresa_id', $empresa_id)->first()->id;
        echo "Centro de Custo: " . $cc_id . PHP_EOL;

        $area_etiqueta_id = \App\Models\AreaEtiqueta::where('empresa_id', $empresa_id)->where('label', $line['cod_area'])->first()->id;

        return [
            "curriculo" => [
                'cpf' => Sistema::mascaraCpf($line['cpf']),
                "nome" => (string)$line['nome'],
                "naturalidade" => (string)$line['naturalidade'],
                "email" => (string)mb_strtolower(trim($line['email'])) ?? Sistema::EMAILPADRAO,
                "cnh" => (string)$line['cnh'],
                "cnh_vencimento" => $line['cnh_vencimento'] ? (new \MasterTag\DataHora($line['cnh_vencimento']))->dataCompleta() : null,
                "estado_civil" => (string)$line['estado_civil'],
                "rg" => (string)preg_replace("/[^0-9]/", "", $line['rg']),
                "rg_data_emissao" => $line['rg_emissao'] ? (new \MasterTag\DataHora($line['rg_emissao']))->dataCompleta() : null,
                "nascimento" => $line['nascimento'] ? (new \MasterTag\DataHora($line['nascimento']))->dataCompleta() : null,
                "sexo" => mb_strtoupper($line['sexo']) == "M" ? "Masculino" : "Feminino",
                "filiacao_pai" => (string)$line['pai'],
                "filiacao_mae" => (string)$line['mae'],
                "pcd" => mb_strtolower(trim($line['pcd'])) == "S",
                "cid" => (string)$line['cid'],
                "vaga_pretendida" => intval($vaga->id),
                "telefone" => [
                    "whatsapp" => mb_strtolower(trim($line['whatsapp'])) == "S" ? "whatsapp" : "celular",
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
                "area_etiqueta_id" => $area_etiqueta_id,
                "centro_custo_id" => $line['centro_custo_id'],
                "filial" => $line['filial'] == 'SIM',
                "centro_custo_filial_id" => $line['filial'] == 'SIM' ? \App\Models\CentroCustoFilial::where('centro_custo_id', $line['centro_custo_id'])->where('empresa_id', $empresa_id)->first()->id : null,
                "data_entrega_area" => $line['data_entrega_area'] ? (new \MasterTag\DataHora($line['data_entrega_area']))->dataCompleta() : null,
                "salario" => number_format(floatval($line['salario']), 2, ',', '.'),
                "pis" => (string)$line['pis'],
                "ctps_numero" => (string)$line['ctps_numero'],
                "ctps_serie" => (string)$line['ctps_serie'],
                "ctps_data_emissao" => $line['ctps_data_emissao'] ? (new \MasterTag\DataHora($line['ctps_data_emissao']))->dataCompleta() : null,
                "titulo_eleitor_numero" => (string)$line['titulo_eleitor_numero'],
                "titulo_eleitor_sessao" => (string)$line['titulo_eleitor_sessao'],
                "titulo_eleitor_zona" => (string)$line['titulo_eleitor_zona'],
                "tipo_admissao" => mb_strtoupper($line['tipo_admissao']),
                "data_admissao" => (new \MasterTag\DataHora(trim((string)$line['data_admissao'])))->dataCompleta(),
                "data_aso" => (new \MasterTag\DataHora($line['data_aso']))->dataCompleta(),
                "admissao_encerramento" => $line['admissao_encerramento'] ? (new \MasterTag\DataHora($line['admissao_encerramento']))->dataCompleta() : null,
                "prazo_experiencia" => ucfirst(trim($line['prazo_experiencia'])),
                "encaminhado_documento" => mb_strtolower(trim($line['encaminhado_documento'])) == "sim",
                "encaminhado_documento_data" => $line['encaminhado_documento_data'] ? (new \MasterTag\DataHora($line['encaminhado_documento_data']))->dataCompleta() : null,
                "encaminhado_exame" => mb_strtolower(trim($line['encaminhado_exame'])) == "sim",
                "encaminhado_exame_data" => $line['encaminhado_exame_data'] ? (new \MasterTag\DataHora($line['encaminhado_exame_data']))->dataCompleta() : null,
                "encaminhado_treinamento" => mb_strtolower(trim($line['encaminhado_treinamento'])) == "sim",
                "encaminhado_treinamento_data" => $line['encaminhado_treinamento_data'] ? (new \MasterTag\DataHora($line['encaminhado_treinamento_data']))->dataCompleta() : null,
                "numero_cracha" => (string)$line['numero_cracha'],
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


$outputFile = base_path("scripts/xls/logs_import/" . (new \MasterTag\DataHora())->dataInsert() . '_importacao_montisol_log.txt');
$outputHandle = fopen($outputFile, 'w');

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
            'curriculo.nascimento' => 'required|date_format:d/m/Y|regex:/^\d{2}\/\d{2}\/\d{4}$/',
            'curriculo.rg' => 'nullable|max:200',
            'curriculo.rg_data_emissao' => 'nullable|max:10|regex:/^\d{2}\/\d{2}\/\d{4}$/',
            'curriculo.filiacao_pai' => 'max:255',
            'curriculo.filiacao_mae' => 'required|max:255',
            'curriculo.pcd' => 'required|boolean',
            'curriculo.cid' => 'required_if:curriculo.pcd,true',
            'curriculo.vaga_pretendida' => ['required', new VagaAbertaEmpresaRules($empresa_id)],
            'curriculo.endereco.cep' => 'required|min:9',
            'curriculo.endereco.logradouro' => 'required|max:255',
            'curriculo.endereco.numero' => 'nullable|max:10',
            'curriculo.endereco.complemento' => 'nullable|max:255',
            'curriculo.endereco.bairro' => 'required|max:255',
            'curriculo.endereco.municipio' => 'required|max:255',
            'curriculo.endereco.uf' => 'required|max:2|regex:/^[A-Z]{2}$/',
            'curriculo.telefone.whatsapp' => 'required|in:' . implode(",", TelefoneCurriculo::TIPOS),
            'curriculo.telefone.numero' => 'required|max:16',
            'admissao.area_etiqueta_id' => ['required', new AreaEmpresaRules($empresa_id)],
            'admissao.data_entrega_area' => 'nullable|date_format:d/m/Y|regex:/^\d{2}\/\d{2}\/\d{4}$/',
            'admissao.salario' => 'max:100',
            'admissao.pis' => 'nullable|max:200',
            'admissao.ctps_numero' => 'nullable|max:200',
            'admissao.ctps_serie' => 'nullable|max:200',
            'admissao.ctps_data_emissao' => 'nullable|date_format:d/m/Y|regex:/^\d{2}\/\d{2}\/\d{4}$/',
            'admissao.titulo_eleitor_numero' => 'nullable|max:200',
            'admissao.titulo_eleitor_sessao' => 'nullable|max:200',
            'admissao.titulo_eleitor_zona' => 'nullable|max:200',
            'admissao.data_aso' => 'required|date_format:d/m/Y|regex:/^\d{2}\/\d{2}\/\d{4}$/',
            'admissao.data_admissao' => 'required|date_format:d/m/Y|regex:/^\d{2}\/\d{2}\/\d{4}$/',
            'admissao.tipo_admissao' => "required|in:" . implode(",", Admissao::TODOS_TIPOS_ADMISSAO),
            'admissao.admissao_encerramento' => [
                function ($attribute, $value, $fail) use ($data) {
                    if (in_array($data['admissao']['tipo_admissao'], [Admissao::TIPO_ADMISSAO_INTERMITENTE, Admissao::TIPO_ADMISSAO_DETERMINADO, Admissao::TIPO_ADMISSAO_TEMPORARIO])
                        && is_null($value)
                        && preg_match("/^\d{2}\/\d{2}\/\d{4}$/", $value) == 0
                    ) {
                        $fail("O {$attribute} deve ser preenchido com formato da data dd/mm/aaaa");
                    }
                }],
            'admissao.prazo_experiencia' => [function ($attribute, $value, $fail) use ($data) {
                $i = (int)explode('.', $attribute)[0];
                if ($data['admissao']['tipo_admissao'] == Admissao::TIPO_ADMISSAO_FIXO && !in_array($value, Admissao::TODOS_PRAZOS)) {
                    $fail("A linha {$attribute} só pode ser um dos tipos de prazo: " . implode(',', Admissao::TODOS_PRAZOS));
                }
            }],
            'admissao.banco.nome' => 'nullable|max:200',
            'admissao.banco.agencia' => 'nullable|max:200',
            'admissao.banco.conta' => 'nullable|max:200',
            'admissao.banco.pix' => 'boolean',
            'admissao.banco.pix_tipo_chave' => 'required_if:admissao.banco.pix,true|max:200',
            'admissao.banco.pix_chave' => 'required_if:admissao.banco.pix,true|max:200',
        ]);

        if ($validation->fails()) {
            $msg = 'Erro ao fazer validacao para importação - do CPF: ' . $record['curriculo']['cpf'];
            fwrite($outputHandle, $msg);
            print_r([
                'msg' => 'Erro ao fazer importação',
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
                $vagaSelecionada = VagasAbertas::find($record['curriculo']['vaga_pretendida']);

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
                    'uf_vaga' => $vagaSelecionada->Municipio->uf,
                    'municipio_id' => $vagaSelecionada->Municipio->id,
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

                $telefone_id = $curriculo->Telefones()->updateOrCreate($dadosTel)->id;

                //Cria ou atualiza o Feedback
                $curriculo->Feedback()->updateOrCreate([
                    'curriculo_id' => $curriculo->id
                ], [
                    'curriculo_id' => $curriculo->id,
                    'selecionado' => 'sim',
                    'vaga_id' => $vagaSelecionada->vaga_id,
                    'cliente_id' => $empresa_id,
                    'empresa_id' => $empresa_id,
                    'interesse' => true,
                    'contato_realizado' => true,
                    'telefone_id' => $telefone_id,
                    'vagas_abertas_id' => $vagaSelecionada->id
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
                    'encaminhado_exame' => (bool)$record['admissao']['encaminhado_exame'],
                    'encaminhado_treinamento' => (bool)$record['admissao']['encaminhado_treinamento'],
                ],
                    [
                        'responsavel_envio' => 'importacao',
                        'documentos_entregue' => true,
                        'documentos_entregue_data' => (new \MasterTag\DataHora())->dataInsert(),
                        'encaminhado_exame' => (bool)$record['admissao']['encaminhado_exame'],
                        'encaminhado_exame_data' => $record['admissao']['encaminhado_exame_data'],
                        'encaminhado_treinamento' => (bool)$record['admissao']['encaminhado_treinamento'],
                        'encaminhado_treinamento_data' => $record['admissao']['encaminhado_treinamento_data'],
                    ]);

                //Criações de admissao
                $curriculo->Feedback->Admissao()->updateOrCreate([
                    'centro_custo_id' => $record['admissao']['centro_custo_id'],
                    'area_etiqueta_id' => $record['admissao']['area_etiqueta_id'],
                ], [
                    'centro_custo_id' => $record['admissao']['centro_custo_id'],
                    'area_etiqueta_id' => $record['admissao']['area_etiqueta_id'],
                    'data_entrega_area' => $record['admissao']['data_entrega_area'],
                    'data_admissao' => $record['admissao']['data_admissao'],
                    'cargo' => $vagaSelecionada->Vaga->nome,
                    'funcao' => $vagaSelecionada->Vaga->nome,
                    'status' => Admissao::STATUS_ADMISSAO_ADMITIDO,
                    'salario' => $record['admissao']['salario'],
                    'pis' => $record['admissao']['pis'],
                    'tipo_admissao' => $record['admissao']['tipo_admissao'],
                    'prazo_experiencia' => Admissao::TRINTA_MAIS_QUINZE,
                    'data_encerramento' => $record['admissao']['admissao_encerramento'],
                    'usuario_id' => auth()->user()->id,

                    "filial" => $record['admissao']['filial'],
                    "centro_custo_filial_id" => $record['admissao']['centro_custo_filial_id'],
                    "ctps_numero" => $record['admissao']['ctps_numero'],
                    "ctps_serie" => $record['admissao']['ctps_serie'],
                    "ctps_data_emissao" => $record['admissao']['ctps_data_emissao'],
                    "titulo_eleitor_numero" => $record['admissao']['titulo_eleitor_numero'],
                    "titulo_eleitor_sessao" => $record['admissao']['titulo_eleitor_sessao'],
                    "titulo_eleitor_zona" => $record['admissao']['titulo_eleitor_zona'],

                    "numero_cracha" => $record['admissao']['numero_cracha'],
                    "matricula" => $record['admissao']['matricula'],
                ]);

                Admissao::tipoAdmissaoAvalNoventaCriarAtualizar($curriculo->Feedback->id, $record['admissao']['tipo_admissao'], $record['admissao']['prazo_experiencia'], $record['admissao']['data_admissao'], $record['admissao']['admissao_encerramento']);
                AdmissaoAso::criarAtualizar($curriculo->Feedback->Admissao->id, $empresa_id, $record['admissao']['data_aso']);

                //DadosAdmissoes
                $curriculo->Feedback->Admissao->DadosAdmissoes()->updateOrCreate([
                    'ctps_numero' => $record['admissao']['ctps_numero'],
                    'ctps_serie' => $record['admissao']['ctps_serie'],
                    'ctps_data_emissao' => (new \MasterTag\DataHora($record['admissao']['ctps_data_emissao']))->dataInsert(),
                    'titulo_eleitor_numero' => $record['admissao']['titulo_eleitor_numero'],
                    'titulo_eleitor_sessao' => $record['admissao']['titulo_eleitor_sessao'],
                    'titulo_eleitor_zona' => $record['admissao']['titulo_eleitor_zona'],
                ]);

                $feedbackId = $curriculo->Feedback->id;
                $addASO = addAso($feedbackId, $record['admissao']['data_admissao'], $empresa_id, $record['admissao']['data_aso']);


                DB::commit();
                $msg = $novoUsuario ? 'Novo Colaborador Importação realizada com sucesso do CPF: ' . $record['curriculo']['cpf'] . ' - ' . (new \MasterTag\DataHora())->dataHoraCompleta() . PHP_EOL : 'Atualizado Colaborador ' . 'Importação realizada com sucesso do CPF: ' . $record['curriculo']['cpf'] . ' - ' . (new \MasterTag\DataHora())->dataHoraCompleta() . PHP_EOL;
                fwrite($outputHandle, $msg);
                print_r([
                    'msg' => 'Importação realizada com sucesso do CPF: ' . $record['curriculo']['cpf'],
                    'Usuario:' => $novoUsuario ? 'Novo' : 'Atualizado',
                    'Aso' => print_r($addASO, true)
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                $msg = 'Erro ao importar do CPF: ' . $e->getMessage() . ' - ' . $record['curriculo']['cpf'] . ' - ' . (new \MasterTag\DataHora())->dataHoraCompleta() . PHP_EOL;
                fwrite($outputHandle, $msg);
                print_r([
                    'msg' => 'Erro ao importar',
                    'do CPF: ' . $record['curriculo']['cpf'],
                    'erro' => $e->getMessage()
                ]);
            }
        }

    }
});

fclose($outputHandle);


function getVagaAberta($empresa_id, $vaga_id)
{
    return VagasAbertas::select(['id', 'vaga_id', 'municipio_id'])->with(
        'Vaga:id,nome',
        'Municipio:id,nome,uf'
    )->whereId($vaga_id)
        ->where('empresa_id', $empresa_id)
        ->first()->toArray();
}

function addAso($feedback_id, $data_admissao, $empresa_id, $data_aso)
{
    $data_aso = (new \MasterTag\DataHora($data_aso))->dataInsert();

    $tipoExames = [
        'ADMISSIONAL' => 1,
        'PERIODICO' => 2,
    ];

    $DIFF_DIAS = DataHora::diferencaDias((new DataHora($data_admissao))->dataInsert(), (new DataHora())->dataInsert());

    $token = Sistema::uuid();
    $exame_tipo_id = $DIFF_DIAS <= 547 ? $tipoExames['ADMISSIONAL'] : $tipoExames['PERIODICO'];
    $encaminhamento_data = $data_aso;

    $empresaExameId = $resultadoIntegrado->ResultadoIntegrado->empresa_exame_id ?? \App\Models\EmpresaExame::withoutGlobalScopes()->where('empresa_id', $empresa_id)->first()->id;


    $formulario_id = Formulario::withoutGlobalScopes()->whereTitulo('Exames')->whereEmpresaId($empresa_id)->first()->id;
    $pcmso_id = $resultadoIntegrado->ResultadoIntegrado->pcmso_id ?? \App\Models\Pcmso::withoutGlobalScopes()->where('empresa_id', $empresa_id)->first()->id;

    $temExameFuncionario = ExameFuncionario::withoutGlobalScopes()
        ->whereFeedbackId($feedback_id)
        ->whereEmpresaExameId($empresaExameId)
        ->where('exame_tipo_id', $exame_tipo_id)
        ->where('pcmso_id', $pcmso_id)
        ->where('encaminhamento_data', '=', (new DataHora($encaminhamento_data))->dataInsert())->first();

    try {

        if (is_null($temExameFuncionario)) {
            $exameFuncionario = ExameFuncionario::withoutGlobalScopes()->updateOrCreate([
                'feedback_id' => $feedback_id,
                'empresa_id' => $empresa_id,
                'empresa_exame_id' => $empresaExameId,
                'formulario_id' => $formulario_id,
                'exame_tipo_id' => $exame_tipo_id,
            ], [
                'feedback_id' => $feedback_id,
                'empresa_id' => $empresa_id,
                'empresa_exame_id' => $empresaExameId,
                'formulario_id' => $formulario_id,
                'user_encaminhou_id' => $empresa_id,
                'respostas' => (object)[],
                'token' => $token,
                'pcmso' => true,
                'pcmso_id' => $pcmso_id,
                'exame_tipo_id' => $exame_tipo_id,
                'encaminhamento_data' => $encaminhamento_data
            ]);

            if ($exameFuncionario) {
                \App\Models\Examesesmt::withoutGlobalScopes()
                    ->whereFeedbackId($feedback_id)
                    ->whereEmpresaId($empresa_id)
                    ->update([
                        'atual' => 0
                    ]);

                $resultado = (object)[
                    "result" => "Apto",
                    "aprovado" => "Sim",
                    "pendencias" => "Não",
                    "observacoes" => null,
                    "trabalho_altura" => "Não se aplica",
                    "pendencias_quais" => null,
                    "espacao_confinado" => "Não se aplica"
                ];

                $exameSesmt = \App\Models\Examesesmt::withoutGlobalScopes()->updateOrCreate([
                    'feedback_id' => $feedback_id,
                    'empresa_id' => $empresa_id,
                    'exame_realizado' => 1,
                    'data_realizacao' => $data_aso,
                ], [
                    'feedback_id' => $feedback_id,
                    'empresa_id' => $empresa_id,
                    'exame_funcionario_id' => $exameFuncionario->id,
                    'exame_realizado' => 1,
                    'resultado' => $resultado,
                    'data_realizacao' => $data_aso,
                    'data_vencimento' => \Carbon\Carbon::create($data_aso)->addYear(1)->format('Y-m-d'),
                    'vencido' => 0,
                    'atual' => 1,
                    'user_id' => $empresa_id
                ]);
            }

            echo "Exame do funcionario criado: " . $exameFuncionario->id . " - " . $exameFuncionario->feedback_id . PHP_EOL;
        }

        return [
            'exame_funcionario_id' => $exameFuncionario->id,
            'exame_sesmt_id' => $exameSesmt->id,
            'tipo_exame' => $exame_tipo_id == 1 ? 'ADMISSIONAL' : 'PERIODICO',
            'data_exame' => $data_aso,
            'data_vencimento' => $exameSesmt->data_vencimento,
        ];
    } catch (\Exception $e) {
        echo "Erro ao criar o exame do funcionario: " . $e->getMessage() . PHP_EOL;
    }

}
