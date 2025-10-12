<?php

namespace App\Console\Commands;

use App\Models\Admissao;
use App\Models\AdmissaoAso;
use App\Models\Curriculo;
use App\Models\Sistema;
use App\Models\TelefoneCurriculo;
use App\Models\User;
use App\Models\VagasAbertas;
use App\Rules\CpfValidoEmpresaRules;
use App\Rules\VagaAbertaEmpresaRules;
use App\Rules\VerificaCpfEmpresaRules;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use MasterTag\DataHora;

class ImportaAdmissaoMaxtec extends Command
{
    protected $signature = 'mybp:importa-admissao-maxtec {--empresa_id= : ID da empresa} {--chunk-size=200 : Tamanho do chunk para processamento} {--arquivo= : Nome do arquivo JSON}';
    protected $description = 'Importação de admissões da MAXTEC a partir de arquivo JSON';
//php artisan mybp:importa-admissao-maxtec --empresa_id=73473 --arquivo=maxtec_importacao_2025-setembro
    private $outputHandle;
    private $count = 0;

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        try {
            DB::beginTransaction();
            DB::enableQueryLog();

            $this->autenticaEmpresa();
            $this->info('Iniciando importação de admissões MAXTEC...');

            $empresaId = $this->option('empresa_id');
            if (empty($empresaId)) {
                $this->error('Nenhum ID de empresa informado');
                return false;
            }

            $arquivo = $this->option('arquivo');
            if (empty($arquivo)) {
                $this->error('Nenhum arquivo informado');
                return false;
            }

            $this->inicializarLog();

            $this->info('Carregando arquivo JSON...');
            $dadosImportados = $this->carregarArquivoJson($arquivo);

            if (!$dadosImportados) {
                $this->error('Erro ao carregar o arquivo JSON');
                return false;
            }

            $this->info("Arquivo JSON carregado com sucesso. Total de registros: " . $dadosImportados->count());

            if ($dadosImportados->isEmpty()) {
                $this->error('Nenhum dado encontrado no arquivo JSON');
                return false;
            }

            $dadosProcessados = $this->processarDados($dadosImportados, $empresaId);

            if ($dadosProcessados->count() == 0) {
                $this->error('Nenhum registro válido encontrado após processamento');
                return false;
            }

            $this->info("Processando {$dadosProcessados->count()} registros em chunks...");
            $this->processarEmChunks($dadosProcessados, $empresaId);

            DB::commit();
            $this->info('Importação concluída com sucesso!');
            $this->finalizarLog();
            return true;

        } catch (\Exception $e) {
            DB::rollback();
            $this->error('Erro durante a importação: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());

            if (isset($this->outputHandle)) {
                fwrite($this->outputHandle, 'ERRO GERAL: ' . $e->getMessage() . ' - ' . (new DataHora())->dataHoraCompleta() . PHP_EOL);
                $this->finalizarLog();
            }

            return false;
        }
    }

    private function autenticaEmpresa()
    {
        $empresaId = $this->option('empresa_id');
        if (empty($empresaId)) {
            $this->error('Nenhum ID de empresa informado');
            return false;
        }

        $this->info("Autenticando empresa com ID {$empresaId}...");
        Auth::loginUsingId($empresaId);
        return true;
    }

    private function inicializarLog()
    {
        $arquivo = $this->option('arquivo');
        $diretorioLog = base_path("scripts/import_admissao_json/");

        // Criar diretório se não existir
        if (!is_dir($diretorioLog)) {
            mkdir($diretorioLog, 0755, true);
            $this->info("Diretório criado: {$diretorioLog}");
        }

        $logPath = $diretorioLog . $arquivo . '_' . (new DataHora())->dataInsert() . '_importacao_log.txt';
        $this->outputHandle = fopen($logPath, 'w');

        if (!$this->outputHandle) {
            throw new \Exception("Não foi possível criar o arquivo de log: {$logPath}");
        }

        $this->info("Log iniciado em: {$logPath}");
    }

    private function finalizarLog()
    {
        if ($this->outputHandle) {
            fclose($this->outputHandle);
        }
    }

    private function carregarArquivoJson($nomeArquivo)
    {
        try {
            $caminhoArquivo = base_path("scripts/import_admissao_json/{$nomeArquivo}.json");

            if (!file_exists($caminhoArquivo)) {
                $this->error("Arquivo não encontrado: {$caminhoArquivo}");
                return false;
            }

            $jsonContent = file_get_contents($caminhoArquivo);
            if ($jsonContent === false) {
                $this->error("Erro ao ler o arquivo {$caminhoArquivo}");
                return false;
            }

            $data = json_decode($jsonContent, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->error('Erro ao decodificar o JSON: ' . json_last_error_msg());
                return false;
            }

            return collect($data);

        } catch (\Exception $e) {
            $this->error("Erro ao carregar arquivo JSON: " . $e->getMessage());
            return false;
        }
    }

    private function processarDados($dadosImportados, $empresaId)
    {
        $this->info('Processando dados do JSON...');

        return $dadosImportados->map(function ($line) use ($empresaId) {
            $this->count++;
            $this->info("Processando linha: {$this->count} - {$line['nome']}");

            try {
                DB::beginTransaction();

                $vagaAberta = $this->VagaAbertaEntity($line['vaga']);

//                $cadastraCargo = $this->firstOrCreateCargo($line['vaga'], $empresaId);

                // Extrair cidade da string "2743|São Luís - MA"
                $vaga_cidade_id = $vagaAberta['municipio_id'];
//                $vagaAberta = $this->firstOrCreateVagaAberta($cadastraCargo->id, $vaga_cidade_id, $empresaId, $cadastraCargo->nome);


//                $centro_custo = $this->firstOrCreateCentroCusto(explode('|', $line['centro_custo'])[0], $empresaId);
                $centro_custo = explode('|', $line['centro_custo'])[0];

                DB::commit();

                return $this->montarArrayDados($line, $vagaAberta, $centro_custo);

            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("Erro ao processar linha {$this->count}: " . $e->getMessage());
                return null;
            }
        })->filter(function ($item) {
            return $item !== null && $item['curriculo']['cpf'] != '';
        })->unique('curriculo.cpf');
    }

    private function montarArrayDados($line, $vagaAberta, $centro_custo)
    {
        // Processar telefone (priorizar whatsapp, senão telefone_numero)
//        $telefone = $line['whatsapp'] ?? $line['telefone_numero'] ?? '';

        // Processar PCD
        $pcd = strtoupper($line['pcd']) === 'SIM' ? true : false;

        // Processar PIX
        $pix = strtoupper($line['pix']) === 'SIM' ? true : false;

        // Mapear tipo de admissão
        $tipoAdmissao = $this->mapearTipoAdmissao($line['tipo_admissao']);

        // Mapear prazo de experiência (se aplicável)
        $prazoExperiencia = $line['prazo_experiencia'] ?? Admissao::QUARENTAECINCO_MAIS_QUARENTAECINCO;

        $dados = [
            "curriculo" => [
                'cpf' => Sistema::mascaraCpf($line['cpf']),
                "nome" => (string)$line['nome'],
                "naturalidade" => (string)($line['naturalidade'] ?? null),
                "email" => (string)mb_strtolower(trim($line['email'])) ?? Sistema::EMAILPADRAO,
                "cnh" => null,
                "cnh_vencimento" => null,
                "estado_civil" => (string)($line['estado_civil'] ?? null),
                "rg" => (string)($line['rg'] ?? null),
                "rg_data_emissao" => null,
                "nascimento" => (new DataHora($line['nascimento']))->dataInsert(),
                "sexo" => (string)($line['sexo'] ?? null),
                "filiacao_pai" => (string)($line['pai'] ?? null),
                "filiacao_mae" => (string)($line['mae'] ?? null),
                "pcd" => $pcd,
                "cid" => (string)($line['cid'] ?? null),
                "vaga_pretendida" => intval($vagaAberta['id']),
                "vaga_id" => intval($vagaAberta['vaga_id']),
                'uf_vaga' => $vagaAberta['municipio']['uf'],
                'municipio_id' => $vagaAberta['municipio']['id'],
                "telefone" => [
                    "whatsapp" => $line['whatsapp'] ? TelefoneCurriculo::$WHATS : TelefoneCurriculo::$CELULAR,
                    "numero" => Sistema::mascaraTelefone($line['telefone_numero']),
                ],
                "endereco" => [
                    "cep" => Sistema::mascaraCep($line['cep']) ?? null,
                    "logradouro" => (string)($line['endereco'] ?? null),
                    "numero" => (string)($line['numero'] ?? null),
                    "complemento" => null,
                    "bairro" => (string)($line['bairro'] ?? null),
                    "municipio" => (string)($line['municipio'] ?? null),
                    "uf" => (string)($line['uf'] ?? null),
                ],
            ],
            "admissao" => [
                "cargo" => $vagaAberta['vaga']['nome'],
                "funcao" => $vagaAberta['vaga']['nome'],
                "area_etiqueta_id" => null,
                "centro_custo_id" => $centro_custo,
                "filial" => null,
                "centro_custo_filial_id" => null,
                "data_entrega_area" => (new DataHora($line['data_entrega_area']))->dataInsert(),
                "salario" => $this->formatarSalario($line['salario']),
                "pis" => null,
                "ctps_numero" => null,
                "ctps_serie" => null,
                "ctps_data_emissao" => null,
                "titulo_eleitor_numero" => null,
                "titulo_eleitor_sessao" => null,
                "titulo_eleitor_zona" => null,
                "tipo_admissao" => $tipoAdmissao,
                "data_admissao" => (new DataHora($line['data_admissao']))->dataInsert(),
                "data_aso" => (new DataHora($line['data_admissao']))->dataInsert(),
                "admissao_encerramento" => null,
                "prazo_experiencia" => $prazoExperiencia,
                "encaminhado_documento" => strtoupper($line['encaminhado_documento'] ?? 'NAO') === 'SIM',
                "encaminhado_documento_data" => strlen($line['encaminhado_documento_data']) > 0 ? (new DataHora($line['encaminhado_documento_data']))->dataInsert() : null,
                "encaminhado_exame" => strtoupper($line['encaminhado_exame'] ?? 'NAO') === 'SIM',
                "encaminhado_exame_data" => strlen($line['encaminhado_exame_data']) > 0 ? (new DataHora($line['encaminhado_exame_data']))->dataInsert() : null,
                "encaminhado_treinamento" => strtoupper($line['encaminhado_treinamento'] ?? 'NAO') === 'SIM',
                "encaminhado_treinamento_data" => strlen($line['encaminhado_treinamento_data']) > 0 ? (new DataHora($line['encaminhado_treinamento_data']))->dataInsert() : null,
                "numero_cracha" => null,
                "matricula" => (string)($line['matricula'] ?? null),
                "banco" => [
                    "nome" => (string)($line['banco'] ?? null),
                    "agencia" => (string)($line['agencia'] ?? null),
                    "conta" => (string)($line['conta'] ?? null),
                    "pix" => $pix,
                    "pix_tipo_chave" => null,
                    "pix_chave" => null
                ]
            ]
        ];

        return $dados;
    }

    private function processarEmChunks($dados, $empresaId)
    {
        $chunkSize = $this->option('chunk-size') ?? 200;
        $dados = $dados->toArray();

        collect($dados)->chunk($chunkSize)->each(function ($records) use ($empresaId) {
            foreach ($records as $record) {
                $this->processarRegistro($record, $empresaId);
            }
        });
    }

    private function processarRegistro($record, $empresaId)
    {
        try {
            $this->info("=== Processando CPF: {$record['curriculo']['cpf']} ===");

            $validation = $this->validarDados($record, $empresaId);

            if ($validation->fails()) {
                $msg = 'Erro ao fazer validação para importação - do CPF: ' . $record['curriculo']['cpf'];
                fwrite($this->outputHandle, $msg . PHP_EOL);
                $this->error('Erro de validação: ' . $validation->errors() . ' - do CPF: ' . $record['curriculo']['cpf']);
                return;
            }

            DB::beginTransaction();

            $novoUsuario = $this->criarOuAtualizarUsuario($record, $empresaId);
            $this->criarOuAtualizarCurriculo($record, $novoUsuario['usuario']);
            $this->criarOuAtualizarTelefone($record, $novoUsuario['usuario']);
            $this->criarOuAtualizarFeedback($record, $novoUsuario['usuario'], $empresaId);
            $this->criarEntrevistas($novoUsuario['usuario']);
            $this->criarResultadoIntegrado($record, $novoUsuario['usuario']);
            $this->criarAdmissao($record, $novoUsuario['usuario']);
            $this->criarDadosAdicionais($record, $novoUsuario['usuario'], $empresaId);

            DB::commit();

            $msg = ($novoUsuario['novo'] ? 'Novo Colaborador' : 'Atualizado Colaborador') .
                ' Importação realizada com sucesso do CPF: ' . $record['curriculo']['cpf'] .
                ' - ' . (new DataHora())->dataHoraCompleta();
            fwrite($this->outputHandle, $msg . PHP_EOL);
            $this->info($msg);

        } catch (\Exception $e) {
            DB::rollBack();
            $msg = 'Erro ao importar do CPF: ' . $e->getMessage() . ' - ' . $record['curriculo']['cpf'] . ' - ' . (new DataHora())->dataHoraCompleta();
            fwrite($this->outputHandle, $msg . PHP_EOL);
            $this->error('Erro ao importar CPF ' . $record['curriculo']['cpf'] . ': ' . $e->getMessage());
        }
    }

    private function validarDados($data, $empresaId)
    {
        return Validator::make($data, [
            'curriculo.cpf' => [
                'required',
                'min:14',
                'regex:/^\d{3}\.\d{3}\.\d{3}\-\d{2}$/',
                new CpfValidoEmpresaRules($empresaId),
                new VerificaCpfEmpresaRules($empresaId, true)
            ],
            'curriculo.nome' => 'required|max:255',
            'curriculo.vaga_pretendida' => ['required', new VagaAbertaEmpresaRules($empresaId)],
            'curriculo.endereco.cep' => 'required|min:9',
            'curriculo.endereco.logradouro' => 'required|max:255',
            'curriculo.endereco.numero' => 'nullable|max:10',
            'curriculo.endereco.complemento' => 'nullable|max:255',
            'curriculo.endereco.bairro' => 'required|max:255',
            'curriculo.endereco.municipio' => 'required|max:255',
            'curriculo.endereco.uf' => 'required|max:2|regex:/^[A-Z]{2}$/',
            'curriculo.telefone.numero' => 'required|max:16',
            'admissao.salario' => 'max:100',
            'admissao.tipo_admissao' => "required|in:" . implode(",", Admissao::TODOS_TIPOS_ADMISSAO),
            'admissao.banco.nome' => 'nullable|max:200',
            'admissao.banco.agencia' => 'nullable|max:200',
            'admissao.banco.conta' => 'nullable|max:200',
            'admissao.banco.pix_tipo_chave' => 'required_if:admissao.banco.pix,true|max:200',
            'admissao.banco.pix_chave' => 'required_if:admissao.banco.pix,true|max:200',
        ]);
    }

    private function criarOuAtualizarUsuario($record, $empresaId)
    {
        $usuario = User::where('empresa_id', $empresaId)->whereHas('Curriculo', function ($q) use ($record) {
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
            'empresa_id' => $empresaId
        ];

        if ($usuario->count() == 0) {
            $this->info("Criando o Colaborador - " . $record['curriculo']['nome']);
            $usuario = User::create($dadosUser);
            $novoUsuario = true;
        } else {
            $this->info("Atualizando o Colaborador - " . $record['curriculo']['nome']);
            $usuario = $usuario->first();
            $usuario->update($dadosUser);
            $novoUsuario = false;
        }

        // Criar ou atualizar dados bancários
        $dadosConta = [
            'banco' => $record['admissao']['banco']['nome'],
            'agencia' => $record['admissao']['banco']['agencia'],
            'conta' => $record['admissao']['banco']['conta'],
            'pix' => $record['admissao']['banco']['pix'],
            'tipochavepix' => $record['admissao']['banco']['pix_tipo_chave'],
            'chavepix' => $record['admissao']['banco']['pix_chave'],
        ];

        $usuario->BancoConta ? $usuario->BancoConta->update($dadosConta) : $usuario->BancoConta()->create($dadosConta);

        return ['usuario' => $usuario, 'novo' => $novoUsuario];
    }

    private function criarOuAtualizarCurriculo($record, $usuario)
    {
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
            'vaga_pretendida' => $record['curriculo']['vaga_pretendida']
        ];

        $curriculo = Curriculo::find($usuario->id);

        if (is_null($curriculo)) {
            Curriculo::create($dadosCurriculo);
        } else {
            $curriculo->update($dadosCurriculo);
        }
    }

    private function criarOuAtualizarTelefone($record, $usuario)
    {
        $curriculo = $usuario->Curriculo;

        $dadosTel = [
            'curriculo_id' => $curriculo->id,
            'tipo' => $record['curriculo']['telefone']['whatsapp'],
            'pais' => "55",
            'numero' => $record['curriculo']['telefone']['numero'],
            'principal' => true,
        ];

        return $curriculo->Telefones()->updateOrCreate([
            'curriculo_id' => $curriculo->id
        ], $dadosTel)->id;
    }

    private function criarOuAtualizarFeedback($record, $usuario, $empresaId)
    {
        $curriculo = $usuario->Curriculo;
        $telefone_id = $this->criarOuAtualizarTelefone($record, $usuario);

        $curriculo->Feedback()->updateOrCreate(
            [
                'curriculo_id' => $curriculo->id,
                'cliente_id' => $empresaId,
                'empresa_id' => $empresaId,
                'deleted_at' => null
            ],
            [
                'curriculo_id' => $curriculo->id,
                'selecionado' => 'sim',
                'vaga_id' => $record['curriculo']['vaga_id'],
                'cliente_id' => $empresaId,
                'empresa_id' => $empresaId,
                'interesse' => true,
                'contato_realizado' => true,
                'telefone_id' => $telefone_id,
                'vagas_abertas_id' => $record['curriculo']['vaga_pretendida']
            ]
        );
    }

    private function criarEntrevistas($usuario)
    {
        $curriculo = $usuario->Curriculo;

        $curriculo->Feedback->parecerRh()->updateOrCreate(['nota' => 9]);
        $curriculo->Feedback->parecerRota()->updateOrCreate([]);
        $curriculo->Feedback->parecerTecnica()->updateOrCreate([]);
        $curriculo->Feedback->parecerTeste()->updateOrCreate([]);
        $curriculo->Feedback->individualRh()->updateOrCreate([]);
        $curriculo->Feedback->gestorRh()->updateOrCreate([]);
        $curriculo->Feedback->entrevistaRh()->updateOrCreate([]);
    }

    private function criarResultadoIntegrado($record, $usuario)
    {
        $curriculo = $usuario->Curriculo;

        $curriculo->Feedback->ResultadoIntegrado()->updateOrCreate(
            [
                'feedback_id' => $curriculo->Feedback->id,
            ],
            [
                'responsavel_envio' => 'importacao',
                'documentos_entregue' => (bool)$record['admissao']['encaminhado_documento'],
                'documentos_entregue_data' => $record['admissao']['encaminhado_documento_data'],
                'encaminhado_exame' => (bool)$record['admissao']['encaminhado_exame'],
                'encaminhado_exame_data' => $record['admissao']['encaminhado_exame_data'],
                'encaminhado_treinamento' => (bool)$record['admissao']['encaminhado_treinamento'],
                'encaminhado_treinamento_data' => $record['admissao']['encaminhado_treinamento_data'],
            ]
        );
    }

    private function criarAdmissao($record, $usuario)
    {
        $curriculo = $usuario->Curriculo;

        $curriculo->Feedback->Admissao()->updateOrCreate([
            'feedback_id' => $curriculo->Feedback->id,
            'deleted_at' => null
        ], [
            'centro_custo_id' => $record['admissao']['centro_custo_id'],
            'area_etiqueta_id' => $record['admissao']['area_etiqueta_id'],
            'data_entrega_area' => $record['admissao']['data_entrega_area'],
            'data_admissao' => (new DataHora($record['admissao']['data_admissao']))->dataInsert(),
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
    }

    private function criarDadosAdicionais($record, $usuario, $empresaId)
    {
        $curriculo = $usuario->Curriculo;

        // Tipo de admissão e avaliações
        Admissao::tipoAdmissaoAvalNoventaCriarAtualizar(
            $curriculo->Feedback->id,
            $record['admissao']['tipo_admissao'],
            $record['admissao']['prazo_experiencia'],
            $record['admissao']['data_admissao'],
            $record['admissao']['admissao_encerramento']
        );

        // ASO
        AdmissaoAso::criarAtualizar(
            $curriculo->Feedback->Admissao->id,
            $empresaId,
            $record['admissao']['data_aso']
        );

        // Dados de admissão
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
    }

    private function extrairVagaAbertaId($vagaAberta)
    {
        return explode('|', $vagaAberta)[0];
    }

    private function VagaAbertaEntity($vagaAberta)
    {
        $vagaAbertaId = explode('|', $vagaAberta)[0];
        return VagasAbertas::with('Vaga', 'Municipio')->find($vagaAbertaId)->toArray();
    }

    // Métodos auxiliares
    private function extrairCidadeId($vagaCidade)
    {
        // Extrai o ID da string "2743|São Luís - MA"
        if (preg_match('/^(\d+)\s*\|/', $vagaCidade, $matches)) {
            return (int)$matches[1];
        }

        // Se não conseguir extrair, retorna um valor padrão ou busca pela cidade
        $this->warn("Não foi possível extrair ID da cidade de: {$vagaCidade}");
        return 2743; // São Luís como padrão
    }

    private function mapearTipoAdmissao($tipoAdmissao)
    {
        $mapeamento = [
            'APRENDIZ' => Admissao::TIPO_ADMISSAO_APRENDIZ,
            'FIXO' => Admissao::TIPO_ADMISSAO_FIXO,
            'DETERMINADO' => Admissao::TIPO_ADMISSAO_DETERMINADO,
            'TEMPORARIO' => Admissao::TIPO_ADMISSAO_TEMPORARIO,
            'INTERMITENTE' => Admissao::TIPO_ADMISSAO_INTERMITENTE,
        ];

        $tipoUpper = strtoupper($tipoAdmissao);
        return $mapeamento[$tipoUpper] ?? Admissao::TIPO_ADMISSAO_FIXO;
    }

    private function formatarSalario($salario)
    {
        if (empty($salario)) {
            return number_format(0.00, 2, ',', '.');
        }

        // Remove pontos e substitui vírgula por ponto para conversão
        $salarioNumerico = str_replace(['.', ','], ['', '.'], $salario);
        $salarioFloat = (float)$salarioNumerico;

        return number_format($salarioFloat, 2, ',', '.');
    }

    private function firstOrCreateCargo($nome, $empresa_id, $ativo = true)
    {
        return \App\Models\Vaga::firstOrCreate([
            'nome' => $nome,
            'empresa_id' => $empresa_id,
            'ativo' => $ativo
        ]);
    }

    private function firstOrCreateVagaAberta($vaga_id, $municipio_id, $empresa_id, $titulo, $descricao = '', $ativo_sistema = true, $ativo = true)
    {
        return VagasAbertas::firstOrCreate([
            'vaga_id' => $vaga_id,
            'municipio_id' => $municipio_id,
            'empresa_id' => $empresa_id,
            'titulo' => $titulo,
            'descricao' => $descricao,
            'ativo_sistema' => $ativo_sistema,
            'ativo' => $ativo
        ]);
    }

    private function firstOrCreateCentroCusto($nome, $empresa_id, $ativo = true)
    {
        return \App\Models\CentroCusto::firstOrCreate([
            'label' => $nome,
            'gestor_id' => null,
            'empresa_id' => $empresa_id,
            'ativo' => $ativo
        ]);
    }
}
