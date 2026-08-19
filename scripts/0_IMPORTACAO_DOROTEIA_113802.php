<?php

/**
 * Importação DORATEIA – empresa 113802 (Congregação de Santa Doroteia do Brasil).
 * Baseado em scripts/0_IMPORTACAO_MAXTEC.php
 *
 * - Planilha: scripts/importacao_dorateia_2026.xlsx
 * - Município das vagas abertas: MANAUS-AM (id 286)
 * - Cria/reutiliza cargo (vaga), vaga_aberta e centro de custo
 *
 * Uso:
 *   docker compose exec mybpdp php scripts/0_IMPORTACAO_DORATEIA_113802.php
 */

use App\Imports\Admissaoimport;
use App\Models\Admissao;
use App\Models\AdmissaoAso;
use App\Models\Curriculo;
use App\Models\Sistema;
use App\Models\User;
use App\Models\VagasAbertas;
use App\Rules\CpfValidoEmpresaRules;
use App\Rules\VagaAbertaEmpresaRules;
use App\Rules\VerificaCpfEmpresaRules;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Shared\Date;

require dirname(__DIR__) . '/vendor/autoload.php';

$app = require_once dirname(__DIR__) . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

ini_set('memory_limit', '-1');
ini_set('max_execution_time', '-1');

$empresa_id = 113802;
$municipio_id = 286; // MANAUS-AM
$user_id = $empresa_id;
$planilha = base_path('scripts/importacao_dorateia_2026.xlsx');

if (!is_readable($planilha)) {
    fwrite(STDERR, "Planilha não encontrada: {$planilha}\n");
    exit(1);
}

$import = new Admissaoimport;
\Excel::import($import, $planilha);

Auth::loginUsingId($user_id);
$count = 0;

echo "Importação CST | empresa_id={$empresa_id} | municipio_id={$municipio_id} | linhas={$import->dados->count()}\n";

$dados = $import->dados->map(function ($line) use ($empresa_id, $municipio_id, &$count) {
    $count++;
    $linhaExcel = $count + 1; // Excel: linha 1 = cabeçalho
    $nome = trim((string) ($line['nome'] ?? ''));
    $codVaga = trim((string) ($line['cod_vaga'] ?? ''));
    $centroCustoLabel = trim((string) ($line['centro_custo'] ?? ''));

    echo "Linha: {$count} - {$nome}\n";

    if ($codVaga === '' || $centroCustoLabel === '') {
        echo "  SKIP: cod_vaga ou centro_custo vazio\n";
        return null;
    }

    DB::beginTransaction();
    try {
        $cadastraCargo = firstOrCreateCargo($codVaga, $empresa_id);
        $vagaAberta = firstOrCreateVagaAberta($cadastraCargo->id, $municipio_id, $empresa_id, $cadastraCargo->nome);
        $vagaAberta->load(['Vaga', 'Municipio']);
        $centro_custo = firstOrCreateCentroCusto($centroCustoLabel, $empresa_id);
        $labelArea = trim((string) ($line['cod_area'] ?? ''));
        if ($labelArea === '') {
            $labelArea = $centroCustoLabel;
        }
        $area = firstOrCreateAreaEtiqueta($labelArea, $empresa_id, $centro_custo->id);
        DB::commit();
    } catch (\Throwable $e) {
        DB::rollBack();
        echo "  ERRO prep cargo/cc/area: {$e->getMessage()}\n";
        return null;
    }

    $tipoAdmissao = mb_strtoupper(trim((string) ($line['tipo_admissao'] ?? Admissao::TIPO_ADMISSAO_FIXO)));
    if ($tipoAdmissao === '') {
        $tipoAdmissao = Admissao::TIPO_ADMISSAO_FIXO;
    }

    $prazo = trim((string) ($line['prazo_experiencia'] ?? ''));
    if ($prazo === '' && $tipoAdmissao === Admissao::TIPO_ADMISSAO_FIXO) {
        // Planilha CST veio sem prazo; padrão Maxtec para FIXO
        $prazo = Admissao::QUARENTAECINCO_MAIS_QUARENTAECINCO;
    }
    if ($prazo === '') {
        $prazo = null;
    }

    $email = trim((string) ($line['email'] ?? ''));
    if ($email === '') {
        $email = Sistema::EMAILPADRAO;
    }

    $sexo = trim((string) ($line['sexo'] ?? ''));
    $sexo = $sexo !== '' ? mb_strtoupper($sexo) : null;
    $sexo = $sexo === 'MASCULINO' ? 'Masculino' : ($sexo === 'FEMININO' ? 'Feminino' : '');

    $whatsappRaw = mb_strtoupper(trim((string) ($line['whatsapp'] ?? '')));
    $tipoTel = ($whatsappRaw === 'SIM') ? 'whatsapp' : 'celular';

    $pixRaw = mb_strtoupper(trim((string) ($line['pix'] ?? '')));
    $pix = $pixRaw === 'SIM';

    $pcdRaw = mb_strtoupper(trim((string) ($line['pcd'] ?? '')));
    $pcd = $pcdRaw == 'SIM' ? true : false;

    $salario = $line['salario'] ?? null;
    if ($salario === null || $salario === '') {
        $salarioFormatado = number_format(0.00, 2, ',', '.');
    } else {
        $salarioFormatado = is_numeric($salario)
            ? number_format((float) $salario, 2, ',', '.')
            : (string) $salario;
    }

    $arrayDados = [
        'linha_planilha' => $linhaExcel,
        'curriculo' => [
            'cpf' => Sistema::mascaraCpf($line['cpf']),
            'nome' => $nome,
            'naturalidade' => valorOuNull($line['naturalidade'] ?? null),
            'email' => mb_strtolower($email),
            'cnh' => valorOuNull($line['cnh'] ?? null),
            'cnh_vencimento' => parseDataImportacao($line['cnh_vencimento'] ?? null),
            'estado_civil' => valorOuNull($line['estado_civil'] ?? null),
            'rg' => valorOuNull($line['rg'] ?? null),
            'rg_data_emissao' => parseDataImportacao($line['rg_emissao'] ?? null),
            // curriculos.nascimento é NOT NULL — fallback quando a planilha não informa
            'nascimento' => parseDataImportacao($line['nascimento'] ?? null) ?? '2000-01-01',
            'sexo' => $sexo,
            'filiacao_pai' => valorOuNull($line['pai'] ?? null),
            'filiacao_mae' => valorOuNull($line['mae'] ?? null),
            'pcd' => $pcd,
            'cid' => valorOuNull($line['cid'] ?? null),
            'vaga_pretendida' => (int) $vagaAberta->id,
            'vaga_id' => (int) $vagaAberta->vaga_id,
            'uf_vaga' => $vagaAberta->Municipio->uf ?? 'MA',
            'municipio_id' => (int) ($vagaAberta->municipio_id ?? $municipio_id),
            'telefone' => [
                'whatsapp' => $tipoTel,
                'numero' => normalizarTelefoneImportacao($line['telefone_numero'] ?? null),
            ],
            'endereco' => [
                'cep' => Sistema::mascaraCep((string) ($line['cep'] ?? '')),
                'logradouro' => mb_substr(trim((string) ($line['endereco'] ?? '')), 0, 255),
                'numero' => mb_substr(trim((string) ($line['numero'] ?? 'SN')), 0, 10),
                'complemento' => valorOuNull($line['complemento'] ?? null),
                'bairro' => mb_substr(trim((string) ($line['bairro'] ?? '')), 0, 255),
                'municipio' => mb_substr(trim((string) ($line['municipio'] ?? '')), 0, 255),
                'uf' => mb_strtoupper(mb_substr(trim((string) ($line['uf'] ?? '')), 0, 2)),
            ],
        ],
        'admissao' => [
            'cargo' => $vagaAberta->Vaga->nome ?? $codVaga,
            'funcao' => $vagaAberta->Vaga->nome ?? $codVaga,
            'area_etiqueta_id' => $area->id,
            'centro_custo_id' => $centro_custo->id,
            'filial' => null,
            'centro_custo_filial_id' => null,
            'data_entrega_area' => parseDataImportacao($line['data_entrega_area'] ?? null) ?? parseDataImportacao($line['data_admissao'] ?? null),
            'salario' => $salarioFormatado,
            'pis' => valorOuNull($line['pis'] ?? null),
            'ctps_numero' => valorOuNull($line['ctps_numero'] ?? null),
            'ctps_serie' => valorOuNull($line['ctps_serie'] ?? null),
            'ctps_data_emissao' => parseDataImportacao($line['ctps_data_emissao'] ?? null),
            'titulo_eleitor_numero' => valorOuNull($line['titulo_eleitor_numero'] ?? null),
            'titulo_eleitor_sessao' => valorOuNull($line['titulo_eleitor_sessao'] ?? null),
            'titulo_eleitor_zona' => valorOuNull($line['titulo_eleitor_zona'] ?? null),
            'tipo_admissao' => $tipoAdmissao,
            'data_admissao' => parseDataImportacao($line['data_admissao'] ?? null) ?? '2000-01-01',
            // se data_aso não vier na planilha, usa 2000-01-01
            'data_aso' => parseDataImportacao($line['data_aso'] ?? null) ?? '2000-01-01',
            'admissao_encerramento' => parseDataImportacao($line['admissao_encerramento'] ?? null),
            'prazo_experiencia' => $prazo,
            'encaminhado_documento' => resolverEncaminhadoBool($line['encaminhado_documento'] ?? null, true),
            'encaminhado_documento_data' => parseDataImportacao($line['encaminhado_documento_data'] ?? null)
                ?? parseDataImportacao($line['data_admissao'] ?? null)
                ?? '2000-01-01',
            'encaminhado_exame' => resolverEncaminhadoBool($line['encaminhado_exame'] ?? null, true),
            'encaminhado_exame_data' => parseDataImportacao($line['encaminhado_exame_data'] ?? null)
                ?? parseDataImportacao($line['data_aso'] ?? null)
                ?? parseDataImportacao($line['data_admissao'] ?? null)
                ?? '2000-01-01',
            'encaminhado_treinamento' => resolverEncaminhadoBool($line['encaminhado_treinamento'] ?? null, true),
            'encaminhado_treinamento_data' => parseDataImportacao($line['encaminhado_treinamento_data'] ?? null)
                ?? parseDataImportacao($line['data_admissao'] ?? null)
                ?? '2000-01-01',
            'numero_cracha' => valorOuNull($line['numero_cracha'] ?? null),
            'matricula' => valorOuNull($line['matricula'] ?? null),
            'banco' => [
                'nome' => valorOuNull($line['banco'] ?? null),
                'agencia' => valorOuNull($line['agencia'] ?? null),
                'conta' => valorOuNull($line['conta'] ?? null),
                'pix' => $pix,
                'pix_tipo_chave' => valorOuNull($line['pix_tipo_chave'] ?? null),
                'pix_chave' => valorOuNull($line['pix_chave'] ?? null),
            ],
        ],
    ];

    return $arrayDados;
})->filter(function ($item) {
    return $item !== null && ($item['curriculo']['cpf'] ?? '') !== '';
})->unique('curriculo.cpf');

if ($dados->count() == 0) {
    echo "Nenhum registro encontrado\n";
    exit(1);
}

$dados = $dados->toArray();

$logDir = base_path('scripts/xls/logs_import');
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}
$outputFile = $logDir . '/' . (new \MasterTag\DataHora())->dataInsert() . '_importacao_dorateia_113802_log.txt';
$outputHandle = fopen($outputFile, 'w');

echo "Registros únicos a processar: " . count($dados) . "\n";
echo "Log: {$outputFile}\n";

$stats = ['sucesso' => 0, 'erro_validacao' => 0, 'erro_persistencia' => 0];

collect($dados)->chunk(200)->each(function ($records) use ($empresa_id, $outputHandle, &$stats) {
    foreach ($records as $record) {
        $data = (array) $record;

        $validation = Validator::make($data, [
            'curriculo.cpf' => [
                'required',
                'min:14',
                'regex:/^\d{3}\.\d{3}\.\d{3}\-\d{2}$/',
                new CpfValidoEmpresaRules($empresa_id),
                new VerificaCpfEmpresaRules($empresa_id, true),
            ],
            'curriculo.nome' => 'required|max:255',
            'curriculo.vaga_pretendida' => ['required', new VagaAbertaEmpresaRules($empresa_id)],
            'curriculo.endereco.cep' => 'required|min:9',
            'curriculo.endereco.logradouro' => 'required|max:255',
            'curriculo.endereco.numero' => 'nullable|max:10',
            'curriculo.endereco.complemento' => 'nullable|max:255',
            'curriculo.endereco.bairro' => 'required|max:255',
            'curriculo.endereco.municipio' => 'required|max:255',
            'curriculo.endereco.uf' => 'required|max:2|regex:/^[A-Z]{2}$/',
            'curriculo.telefone.numero' => 'nullable|max:16',
            'admissao.salario' => 'max:100',
            'admissao.tipo_admissao' => 'required|in:' . implode(',', Admissao::TODOS_TIPOS_ADMISSAO),
            'admissao.banco.nome' => 'nullable|max:200',
            'admissao.banco.agencia' => 'nullable|max:200',
            'admissao.banco.conta' => 'nullable|max:200',
            'admissao.banco.pix_tipo_chave' => 'required_if:admissao.banco.pix,true|max:200',
            'admissao.banco.pix_chave' => 'required_if:admissao.banco.pix,true|max:200',
        ]);

        if ($validation->fails()) {
            $stats['erro_validacao']++;
            $linhaPlanilha = $record['linha_planilha'] ?? '?';
            $msg = 'Erro ao fazer validacao para importação - linha ' . $linhaPlanilha
                . ' - CPF: ' . $record['curriculo']['cpf'] . PHP_EOL
                . $validation->errors()->toJson(JSON_UNESCAPED_UNICODE) . PHP_EOL;
            fwrite($outputHandle, $msg);
            print_r([
                'msg' => 'Erro ao fazer importação',
                'linha_planilha' => $linhaPlanilha,
                'nome' => $record['curriculo']['nome'] ?? null,
                'erros' => $validation->errors()->toArray(),
                'cpf' => $record['curriculo']['cpf'],
            ]);
            continue;
        }

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
                'empresa_id' => $empresa_id,
            ];

            if ($usuario->count() == 0) {
                echo 'Criando o Colaborador - ' . $record['curriculo']['nome'] . "\n";
                $novoUsuario = true;
                $usuario = User::create($dadosUser);
            } else {
                echo 'Atualizando do Colaborador - ' . $record['curriculo']['nome'] . "\n";
                $novoUsuario = false;
                $usuario = $usuario->first();
                $usuario->update($dadosUser);
            }

            $dadosConta = [
                'banco' => $record['admissao']['banco']['nome'],
                'agencia' => $record['admissao']['banco']['agencia'],
                'conta' => $record['admissao']['banco']['conta'],
                'pix' => $record['admissao']['banco']['pix'],
                'tipochavepix' => $record['admissao']['banco']['pix_tipo_chave'],
                'chavepix' => $record['admissao']['banco']['pix_chave'],
            ];

            $usuario->BancoConta ? $usuario->BancoConta->update($dadosConta) : $usuario->BancoConta()->create($dadosConta);

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
                'rg' => $record['curriculo']['rg'],
                'rg_data_emissao' => $record['curriculo']['rg_data_emissao'],
                'filiacao_pai' => $record['curriculo']['filiacao_pai'],
                'filiacao_mae' => $record['curriculo']['filiacao_mae'],
                'sexo' => $record['curriculo']['sexo'],
                'pcd' => $record['curriculo']['pcd'],
                'cid' => $record['curriculo']['cid'],
                'vaga_pretendida' => $record['curriculo']['vaga_pretendida'],
            ];

            $curriculo = Curriculo::find($usuario->id);

            if (is_null($curriculo)) {
                $curriculo = Curriculo::create($dadosCurriculo);
            } else {
                $curriculo->update($dadosCurriculo);
            }

            $telefoneNumero = trim((string) ($record['curriculo']['telefone']['numero'] ?? ''));
            $telefone_id = null;
            if ($telefoneNumero !== '') {
                $dadosTel = [
                    'curriculo_id' => $curriculo->id,
                    'tipo' => $record['curriculo']['telefone']['whatsapp'] ?? 'celular',
                    'pais' => '55',
                    'numero' => $telefoneNumero,
                    'principal' => true,
                ];

                $telefone_id = $curriculo->Telefones()->updateOrCreate([
                    'curriculo_id' => $curriculo->id,
                ], $dadosTel)->id;
            }

            $curriculo->Feedback()->updateOrCreate(
                [
                    'curriculo_id' => $curriculo->id,
                    'cliente_id' => $empresa_id,
                    'empresa_id' => $empresa_id,
                    'deleted_at' => null,
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
                    'vagas_abertas_id' => $record['curriculo']['vaga_pretendida'],
                ]
            );

            $curriculo->Feedback->parecerRh()->updateOrCreate(['nota' => 9]);
            $curriculo->Feedback->parecerRota()->updateOrCreate([]);
            $curriculo->Feedback->parecerTecnica()->updateOrCreate([]);
            $curriculo->Feedback->parecerTeste()->updateOrCreate([]);
            $curriculo->Feedback->individualRh()->updateOrCreate([]);
            $curriculo->Feedback->gestorRh()->updateOrCreate([]);
            $curriculo->Feedback->entrevistaRh()->updateOrCreate([]);

            $curriculo->Feedback->ResultadoIntegrado()->updateOrCreate(
                [
                    'feedback_id' => $curriculo->Feedback->id,
                ],
                [
                    'responsavel_envio' => 'importacao',
                    // encaminhado_documento da planilha = documentos_entregue no ResultadoIntegrado
                    'documentos_entregue' => (bool) $record['admissao']['encaminhado_documento'],
                    'documentos_entregue_data' => $record['admissao']['encaminhado_documento_data'],
                    'encaminhado_exame' => (bool) $record['admissao']['encaminhado_exame'],
                    'encaminhado_exame_data' => $record['admissao']['encaminhado_exame_data'],
                    'encaminhado_treinamento' => (bool) $record['admissao']['encaminhado_treinamento'],
                    'encaminhado_treinamento_data' => $record['admissao']['encaminhado_treinamento_data'],
                ]
            );

            $curriculo->Feedback->Admissao()->updateOrCreate([
                'feedback_id' => $curriculo->Feedback->id,
                'deleted_at' => null,
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

            Admissao::tipoAdmissaoAvalNoventaCriarAtualizar(
                $curriculo->Feedback->id,
                $record['admissao']['tipo_admissao'],
                $record['admissao']['prazo_experiencia'],
                $record['admissao']['data_admissao'],
                $record['admissao']['admissao_encerramento']
            );
            AdmissaoAso::criarAtualizar($curriculo->Feedback->Admissao->id, $empresa_id, $record['admissao']['data_aso']);
            criarOuAtualizarExameAsoCst(
                $curriculo->Feedback->id,
                $empresa_id,
                $record['admissao']['data_aso'],
                $record['admissao']['data_admissao']
            );

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
                ]
            );

            DB::commit();
            $stats['sucesso']++;
            $msg = ($novoUsuario ? 'Novo' : 'Atualizado') . ' Colaborador Importação realizada com sucesso do CPF: '
                . $record['curriculo']['cpf'] . ' - ' . (new \MasterTag\DataHora())->dataHoraCompleta() . PHP_EOL;
            fwrite($outputHandle, $msg);
        } catch (\Throwable $e) {
            DB::rollBack();
            $stats['erro_persistencia']++;
            $linhaPlanilha = $record['linha_planilha'] ?? '?';
            $msg = 'Erro ao importar - linha ' . $linhaPlanilha . ' - CPF: ' . $record['curriculo']['cpf']
                . ' - ' . $e->getMessage()
                . ' - ' . (new \MasterTag\DataHora())->dataHoraCompleta() . PHP_EOL;
            fwrite($outputHandle, $msg);
            print_r([
                'msg' => 'Erro ao importar',
                'linha_planilha' => $linhaPlanilha,
                'nome' => $record['curriculo']['nome'] ?? null,
                'cpf' => $record['curriculo']['cpf'],
                'erro' => $e->getMessage(),
            ]);
        }
    }
});

fclose($outputHandle);
echo "Importação finalizada. Log: {$outputFile}\n";
echo "RESUMO: sucesso={$stats['sucesso']} erro_validacao={$stats['erro_validacao']} erro_persistencia={$stats['erro_persistencia']}\n";

function firstOrCreateCargo($nome, $empresa_id, $ativo = true)
{
    return \App\Models\Vaga::firstOrCreate([
        'nome' => $nome,
        'empresa_id' => $empresa_id,
    ], [
        'ativo' => $ativo,
    ]);
}

function firstOrCreateVagaAberta($vaga_id, $municipio_id, $empresa_id, $titulo, $descricao = '', $ativo_sistema = true, $ativo = true)
{
    return VagasAbertas::firstOrCreate([
        'vaga_id' => $vaga_id,
        'municipio_id' => $municipio_id,
        'empresa_id' => $empresa_id,
    ], [
        'titulo' => $titulo,
        'descricao' => $descricao,
        'ativo_sistema' => $ativo_sistema,
        'ativo' => $ativo,
    ]);
}

function firstOrCreateCentroCusto($nome, $empresa_id, $ativo = true)
{
    return \App\Models\CentroCusto::firstOrCreate([
        'label' => $nome,
        'empresa_id' => $empresa_id,
    ], [
        'gestor_id' => null,
        'ativo' => $ativo,
    ]);
}

function firstOrCreateAreaEtiqueta($nome, $empresa_id, $centro_custo_id = null, $ativo = true)
{
    $area = \App\Models\AreaEtiqueta::firstOrCreate([
        'label' => $nome,
        'empresa_id' => $empresa_id,
    ], [
        'ativo' => $ativo,
        'gestor_id' => null,
        'centro_custo_id' => $centro_custo_id,
    ]);

    if ($centro_custo_id && !$area->centro_custo_id) {
        $area->centro_custo_id = $centro_custo_id;
        $area->save();
    }

    return $area;
}

function garantirInfraAsoCst(int $empresaId): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }

    $formulario = \App\Models\Formulario::withoutGlobalScopes()
        ->where('empresa_id', $empresaId)
        ->where('titulo', 'Exames')
        ->first();
    if (!$formulario) {
        $formulario = \App\Models\Formulario::create([
            'titulo' => 'Exames',
            'descricao' => 'Formulário de exames (importação CST)',
            'empresa_id' => $empresaId,
        ]);
    }

    $pcmso = \App\Models\Pcmso::withoutGlobalScopes()->where('empresa_id', $empresaId)->first();
    if (!$pcmso) {
        $pcmso = \App\Models\Pcmso::create([
            'empresa_id' => $empresaId,
            'label' => 'Fixo',
            'ativo' => true,
        ]);
    }

    $clinica = \App\Models\EmpresaExame::withoutGlobalScopes()->where('empresa_id', $empresaId)->first();
    if (!$clinica) {
        $clinica = \App\Models\EmpresaExame::withoutEvents(function () use ($empresaId) {
            return \App\Models\EmpresaExame::create([
                'user_id' => $empresaId,
                'empresa_id' => $empresaId,
                'nome' => 'CLINICA IMPORTACAO CST',
                'dados' => [
                    'cnpj' => '00.000.000/0000-00',
                    'email' => 'clinica.cst.' . $empresaId . '@mybp.local',
                    'telefone' => '(00) 0000-0000',
                    'nome_fantasia' => 'CLINICA CST',
                    'endereco' => [
                        'uf' => 'MA',
                        'cep' => '65000-000',
                        'bairro' => 'CENTRO',
                        'municipio' => 'São Luís',
                        'end_numero' => 'SN',
                        'logradouro' => 'NAO INFORMADO',
                        'complemento' => null,
                    ],
                ],
                'ativo' => true,
            ]);
        });
    }

    return $cache = [
        'empresa_exame_id' => (int) $clinica->id,
        'formulario_id' => (int) $formulario->id,
        'pcmso_id' => (int) $pcmso->id,
    ];
}

function criarOuAtualizarExameAsoCst(int $feedbackId, int $empresaId, string $dataAso, string $dataAdmissao): void
{
    $infra = garantirInfraAsoCst($empresaId);
    $diffDias = \MasterTag\DataHora::diferencaDias($dataAdmissao, (new \MasterTag\DataHora())->dataInsert());
    $exameTipoId = $diffDias <= 547 ? 1 : 2;

    $exameFuncionario = \App\Models\ExameFuncionario::withoutGlobalScopes()->updateOrCreate(
        [
            'feedback_id' => $feedbackId,
            'empresa_id' => $empresaId,
            'empresa_exame_id' => $infra['empresa_exame_id'],
            'formulario_id' => $infra['formulario_id'],
            'exame_tipo_id' => $exameTipoId,
        ],
        [
            'user_encaminhou_id' => $empresaId,
            'respostas' => (object) [],
            'token' => Sistema::uuid(),
            'pcmso' => true,
            'pcmso_id' => $infra['pcmso_id'],
            'encaminhamento_data' => $dataAso,
        ]
    );

    \App\Models\Examesesmt::withoutGlobalScopes()
        ->where('feedback_id', $feedbackId)
        ->where('empresa_id', $empresaId)
        ->update(['atual' => 0]);

    \App\Models\Examesesmt::withoutGlobalScopes()->updateOrCreate(
        [
            'feedback_id' => $feedbackId,
            'empresa_id' => $empresaId,
            'exame_funcionario_id' => $exameFuncionario->id,
        ],
        [
            'exame_realizado' => true,
            'resultado' => [
                'result' => 'Apto',
                'aprovado' => 'Sim',
                'pendencias' => 'Não',
                'observacoes' => null,
                'trabalho_altura' => 'Não se aplica',
                'pendencias_quais' => null,
                'espacao_confinado' => 'Não se aplica',
            ],
            'data_realizacao' => $dataAso,
            'data_vencimento' => \Carbon\Carbon::parse($dataAso)->addYear()->format('Y-m-d'),
            'vencido' => false,
            'atual' => true,
            'user_id' => $empresaId,
        ]
    );
}

function valorOuNull($valor)
{
    if ($valor === null) {
        return null;
    }
    $valor = trim((string) $valor);
    return $valor === '' ? null : $valor;
}

/**
 * Telefone opcional: se vazio/inválido, retorna null (não cadastra).
 */
function normalizarTelefoneImportacao($valor): ?string
{
    $raw = trim((string) ($valor ?? ''));
    if ($raw === '' || $raw === '0' || strtolower($raw) === 'null') {
        return null;
    }
    $digitos = preg_replace('/\D+/', '', $raw);
    if ($digitos === null || strlen($digitos) < 8) {
        return null;
    }
    $mascarado = Sistema::mascaraTelefone($raw);
    $mascarado = trim((string) $mascarado);
    return $mascarado !== '' ? $mascarado : null;
}

function simNaoBool($valor): ?bool
{
    $v = mb_strtoupper(trim((string) ($valor ?? '')));
    if ($v === 'SIM' || $v === '1' || $v === 'TRUE' || $v === 'S') {
        return true;
    }
    if ($v === 'NAO' || $v === 'NÃO' || $v === '0' || $v === 'FALSE' || $v === 'N') {
        return false;
    }
    return null;
}

/**
 * Converte SIM/NAO; se vazio, usa default (para CST admitidos: true).
 */
function resolverEncaminhadoBool($valor, bool $defaultQuandoVazio): bool
{
    $parsed = simNaoBool($valor);
    return $parsed === null ? $defaultQuandoVazio : $parsed;
}

function parseDataImportacao($date, $format = 'Y-m-d')
{
    if ($date === null || $date === '') {
        return null;
    }
    try {
        if (is_numeric($date)) {
            $parsed = Date::excelToDateTimeObject($date)->format($format);
            return dataImportacaoValida($parsed) ? $parsed : null;
        }
        $date = normalizarDataPlanilha(trim((string) $date));
        $parsed = (new \MasterTag\DataHora($date))->dataInsert();
        return dataImportacaoValida($parsed) ? $parsed : null;
    } catch (\Throwable $e) {
        return null;
    }
}

/**
 * Aceita 23/004/2025 (mês/dia com zeros a mais) e devolve 23/04/2025.
 */
function normalizarDataPlanilha(string $date): string
{
    if (preg_match('/^(\d{1,4})\/(\d{1,4})\/(\d{4})$/', $date, $m)) {
        $dia = (int) $m[1];
        $mes = (int) $m[2];
        $ano = (int) $m[3];
        if ($dia >= 1 && $mes >= 1 && $mes <= 12 && checkdate($mes, $dia, $ano)) {
            return sprintf('%02d/%02d/%04d', $dia, $mes, $ano);
        }
    }

    return $date;
}

function dataImportacaoValida($date): bool
{
    if (!is_string($date) || !preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $m)) {
        return false;
    }

    return checkdate((int) $m[2], (int) $m[3], (int) $m[1]);
}
