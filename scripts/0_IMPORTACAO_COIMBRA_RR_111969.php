<?php

/**
 * Importação COIMBRA – empresa 111969 (Congregação de Santa Doroteia do Brasil).
 * Baseado em scripts/0_IMPORTACAO_CST_111969.php
 *
 * - Planilha: scripts/importacao_coimbra_rr_2026.xlsx
 * - Município das vagas abertas: coluna vaga_mun no formato "id | Nome - UF" (ex.: 2598 | Coroatá - MA)
 * - Cria/reutiliza cargo (vaga), vaga_aberta e centro de custo
 *
 * Uso:
 *   docker compose exec mybpdp php scripts/0_IMPORTACAO_COIMBRA_RR_111969.php
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

$empresa_id = defined('IMPORTACAO_EMPRESA_ID') ? (int) IMPORTACAO_EMPRESA_ID : 111969;
$user_id = $empresa_id;
$nomeImportacao = defined('IMPORTACAO_NOME') ? (string) IMPORTACAO_NOME : 'Coimbra RR';
$slugImportacao = defined('IMPORTACAO_SLUG') ? (string) IMPORTACAO_SLUG : 'coimbra_rr_111969';
$arquivoPlanilha = defined('IMPORTACAO_PLANILHA') ? (string) IMPORTACAO_PLANILHA : 'importacao_CoimbraeRR_2026.xlsx';
$municipioIdFixo = defined('IMPORTACAO_MUNICIPIO_ID') ? (int) IMPORTACAO_MUNICIPIO_ID : null;
$planilha = base_path('scripts/' . $arquivoPlanilha);

if (!is_readable($planilha)) {
    fwrite(STDERR, "Planilha não encontrada: {$planilha}\n");
    exit(1);
}

$import = new Admissaoimport;
\Excel::import($import, $planilha);

Auth::loginUsingId($user_id);
$count = 0;

echo "Importação {$nomeImportacao} | empresa_id={$empresa_id} | linhas={$import->dados->count()}\n";

$errosPrep = [];
$dados = $import->dados->map(function ($line) use ($empresa_id, $municipioIdFixo, &$count, &$errosPrep) {
    $count++;
    $linhaExcel = $count + 1;
    $nome = trim((string) ($line['nome'] ?? ''));
    $codVaga = trim((string) ($line['cod_vaga'] ?? ''));
    $centroCustoLabel = trim((string) ($line['centro_custo'] ?? ''));
    $municipio_id = $municipioIdFixo ?: parseMunicipioIdVagaMun($line['vaga_mun'] ?? null);

    echo "Linha: {$count} - {$nome}\n";

    if ($codVaga === '' || $centroCustoLabel === '') {
        echo "  SKIP: cod_vaga ou centro_custo vazio\n";
        $errosPrep[] = formatarErroImportacao($linhaExcel, $line['cpf'] ?? '', $nome, 'skip', 'cod_vaga ou centro_custo vazio');
        return null;
    }

    if ($municipio_id === null) {
        $vagaMun = trim((string) ($line['vaga_mun'] ?? ''));
        echo "  SKIP: vaga_mun vazio ou inválido ({$vagaMun})\n";
        $errosPrep[] = formatarErroImportacao($linhaExcel, $line['cpf'] ?? '', $nome, 'skip', 'vaga_mun vazio ou inválido: ' . $vagaMun);
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
        $errosPrep[] = formatarErroImportacao($linhaExcel, $line['cpf'] ?? '', $nome, 'prep', $e->getMessage());
        return null;
    }

    $tipoAdmissao = mb_strtoupper(trim((string) ($line['tipo_admissao'] ?? Admissao::TIPO_ADMISSAO_FIXO)));
    if ($tipoAdmissao === '') {
        $tipoAdmissao = Admissao::TIPO_ADMISSAO_FIXO;
    }

    $prazo = trim((string) ($line['prazo_experiencia'] ?? ''));
    if ($prazo === '' && $tipoAdmissao === Admissao::TIPO_ADMISSAO_FIXO) {
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

$dados = $dados->toArray();

$logDir = base_path('scripts/xls/logs_import');
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}
$dataLog = (new \MasterTag\DataHora())->dataInsert();
$outputFile = $logDir . '/' . $dataLog . '_importacao_' . $slugImportacao . '_log.txt';
$erroFile = $logDir . '/' . $dataLog . '_importacao_' . $slugImportacao . '_erros.txt';
$outputHandle = fopen($outputFile, 'w');
$erroHandle = null;

foreach ($errosPrep as $msgErroPrep) {
    registrarErroImportacao($erroHandle, $erroFile, $msgErroPrep);
}

if (count($dados) == 0) {
    fclose($outputHandle);
    if ($erroHandle !== null) {
        fclose($erroHandle);
        echo "Nenhum registro encontrado. Erros: {$erroFile}\n";
    } else {
        echo "Nenhum registro encontrado\n";
    }
    exit(1);
}

echo "Registros únicos a processar: " . count($dados) . "\n";
echo "Log: {$outputFile}\n";

$stats = ['sucesso' => 0, 'erro_validacao' => 0, 'erro_persistencia' => 0];

collect($dados)->chunk(200)->each(function ($records) use ($empresa_id, $outputHandle, $erroFile, &$erroHandle, &$stats) {
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
            $cpf = $record['curriculo']['cpf'] ?? '';
            $nome = $record['curriculo']['nome'] ?? '';
            $errosTexto = json_encode($validation->errors()->toArray(), JSON_UNESCAPED_UNICODE);
            $msg = formatarErroImportacao($linhaPlanilha, $cpf, $nome, 'validacao', $errosTexto);
            registrarErroImportacao($erroHandle, $erroFile, $msg);
            print_r([
                'msg' => 'Erro ao fazer importação',
                'linha_planilha' => $linhaPlanilha,
                'nome' => $nome,
                'erros' => $validation->errors()->toArray(),
                'cpf' => $cpf,
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
            criarOuAtualizarExameAsoImportacao(
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
        } catch (\Exception $e) {
            DB::rollBack();
            $stats['erro_persistencia']++;
            $linhaPlanilha = $record['linha_planilha'] ?? '?';
            $cpf = $record['curriculo']['cpf'] ?? '';
            $nome = $record['curriculo']['nome'] ?? '';
            $msg = formatarErroImportacao($linhaPlanilha, $cpf, $nome, 'persistencia', $e->getMessage());
            registrarErroImportacao($erroHandle, $erroFile, $msg);
            print_r([
                'msg' => 'Erro ao importar',
                'linha_planilha' => $linhaPlanilha,
                'nome' => $nome,
                'cpf' => $cpf,
                'erro' => $e->getMessage(),
            ]);
        }
    }
});

fclose($outputHandle);
if ($erroHandle !== null) {
    fclose($erroHandle);
    echo "Importação finalizada. Log: {$outputFile}\n";
    echo "Erros: {$erroFile}\n";
} else {
    echo "Importação finalizada. Log: {$outputFile}\n";
}
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

function garantirInfraAsoImportacao(int $empresaId): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }

    $slug = defined('IMPORTACAO_SLUG') ? (string) IMPORTACAO_SLUG : 'coimbra_rr_111969';
    $nome = defined('IMPORTACAO_NOME') ? (string) IMPORTACAO_NOME : 'Coimbra RR';

    $formulario = \App\Models\Formulario::withoutGlobalScopes()
        ->where('empresa_id', $empresaId)
        ->where('titulo', 'Exames')
        ->first();
    if (!$formulario) {
        $formulario = \App\Models\Formulario::create([
            'titulo' => 'Exames',
            'descricao' => 'Formulário de exames (importação ' . $nome . ')',
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
        $clinica = \App\Models\EmpresaExame::withoutEvents(function () use ($empresaId, $slug, $nome) {
            return \App\Models\EmpresaExame::create([
                'user_id' => $empresaId,
                'empresa_id' => $empresaId,
                'nome' => 'CLINICA IMPORTACAO ' . mb_strtoupper($nome),
                'dados' => [
                    'cnpj' => '00.000.000/0000-00',
                    'email' => 'clinica.' . $slug . '.' . $empresaId . '@mybp.local',
                    'telefone' => '(00) 0000-0000',
                    'nome_fantasia' => 'CLINICA ' . mb_strtoupper($nome),
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

function criarOuAtualizarExameAsoImportacao(int $feedbackId, int $empresaId, string $dataAso, string $dataAdmissao): void
{
    $infra = garantirInfraAsoImportacao($empresaId);
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
 * Extrai municipio_id de vaga_mun no formato "2598 | Coroatá - MA".
 * Usa o trecho antes do primeiro "|"; se vier só o número, aceita também.
 */
function parseMunicipioIdVagaMun($valor): ?int
{
    $valor = trim((string) ($valor ?? ''));
    if ($valor === '') {
        return null;
    }
    if (str_contains($valor, '|')) {
        $valor = trim(explode('|', $valor, 2)[0]);
    }
    if ($valor === '' || !ctype_digit($valor)) {
        return null;
    }

    return (int) $valor;
}

function formatarErroImportacao($linha, $cpf, $nome, string $tipo, string $detalhe): string
{
    $cpf = trim((string) $cpf);
    $nome = trim((string) $nome);

    return sprintf(
        "[%s] linha=%s cpf=%s nome=%s\n  %s\n\n",
        $tipo,
        $linha === '' || $linha === null ? '?' : $linha,
        $cpf === '' ? '-' : $cpf,
        $nome === '' ? '-' : $nome,
        $detalhe
    );
}

function registrarErroImportacao(&$handle, string $path, string $msg): void
{
    if ($handle === null) {
        $handle = fopen($path, 'w');
    }
    fwrite($handle, $msg);
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
 * Converte SIM/NAO; se vazio, usa o default da importação.
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
            return Date::excelToDateTimeObject($date)->format($format);
        }
        return (new \MasterTag\DataHora(trim((string) $date)))->dataInsert();
    } catch (\Throwable $e) {
        return null;
    }
}
