<?php

namespace App\Console\Commands;

use App\Models\Treinamento;
use App\Models\TreinamentoVencimentoHistorico;
use Illuminate\Console\Command;
use MasterTag\DataHora;

class ImportaTreinamento extends Command
{
    protected $signature = 'mybp:importa-treinamento {--empresa_id= : Description of option1} {--chunk-size=1000 : Description of option2} {--arquivo= : Description of option3}';
    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        try {
            \DB::beginTransaction();

            // Habilitar query log para debug
            \DB::enableQueryLog();

            $this->autenticaEmpresa();
            $this->info('Iniciando importação de treinamento...');
            $empresaId = $this->option('empresa_id');

            if (empty($empresaId)) {
                $this->error('Nenhum ID de empresa informado');
                return false;
            }

            $this->info('ler arquivo JSON...');
            $dadosJson = $this->getArquivo();

            if (!$dadosJson) {
                $this->error('Erro ao obter o arquivo JSON');
                return false;
            }

            $this->info("Arquivo JSON carregado com sucesso. Total de registros: " . count($dadosJson));

            if ($dadosJson->isEmpty()) {
                $this->error('Nenhum dado encontrado no arquivo JSON');
                return false;
            }

            foreach ($dadosJson as $index => $dados) {
                $this->info("=== Processando registro {$index} - CPF: {$dados['cpf']} ===");

                try {
                    $feedback = \DB::table('feedback_curriculos')
                        ->select(['feedback_curriculos.id as feedback_id', 'curriculos.cpf as cpf', 'feedback_curriculos.empresa_id'])
                        ->join('curriculos', 'curriculos.id', '=', 'feedback_curriculos.curriculo_id')
                        ->where('curriculos.cpf', $dados['cpf'])
                        ->where('feedback_curriculos.empresa_id', $empresaId)
                        ->first();

                    if (!$feedback) {
                        $this->error("Nenhum feedback encontrado para o CPF: {$dados['cpf']}");
                        continue;
                    }

                    $dados['feedback_id'] = $feedback->feedback_id;
                    $dados['empresa_id'] = $empresaId;
                    $dados['cadastrou'] = $empresaId;
                    $dados['arquivo_id'] = null;
                    $dados['numero_fat'] = null;

                    $this->info("Buscando treinamento existente...");

                    // Debug: mostrar dados antes da busca
                    $this->info("Dados para busca - Tipo: {$dados['tipo']}, Feedback ID: {$dados['feedback_id']}");

                    $treinamentoModel = Treinamento::where('tipo', $dados['tipo'])
                        ->where('feedback_id', $dados['feedback_id'])
                        ->first();

                    if (!$treinamentoModel) {
                        $this->info("Treinamento não encontrado, criando novo treinamento para o CPF: {$dados['cpf']}");

                        // Debug antes de criar
                        $this->info("Dados para criação: " . json_encode($dados, JSON_PRETTY_PRINT));

                        $treinamentoNovo = Treinamento::create($dados);
                        $treinamentoModel = Treinamento::find($treinamentoNovo->id);
                        $this->info("Treinamento criado com sucesso: ID {$treinamentoModel->id}, Tipo: {$treinamentoModel->tipo}");

                        // Verificar se o vencimento existe antes de criar o pivot
                        $vencimentoExiste = \DB::table('vencimentos')->where('id', $dados['vencimento_id'])->exists();

                        if ($vencimentoExiste) {
                            $this->info("Criando TreinamentoVencimento...");

                            // Debug: mostrar dados do pivot
                            $pivotData = [
                                'vencimento_id' => $dados['vencimento_id'],
                                'treinamento_id' => $treinamentoModel->id,
                                'data_vencimento' => $dados['data_vencimento'],
                                'data_treinamento' => $dados['data_treinamento'],
                                'numero_fat' => $dados['numero_fat'],
                                'arquivo_id' => $dados['arquivo_id']
                            ];
                            $this->info("Dados do pivot: " . json_encode($pivotData, JSON_PRETTY_PRINT));

                            $TreinamentoVencimento = \App\Models\Pivot\TreinamentoVencimento::create($pivotData);
                            $this->info("TreinamentoVencimento criado com sucesso: ID {$TreinamentoVencimento->id}");
                        } else {
                            $this->warn("Vencimento ID {$dados['vencimento_id']} não existe na tabela vencimentos");
                        }

                        $this->info('Salvando histórico de vencimentos...');
                        $this->salvarHistorico($dados['feedback_id'], $treinamentoModel->id, $empresaId);
                        $this->info("Histórico de vencimentos salvo com sucesso para o treinamento ID: {$treinamentoModel->id}");

                    } else {
                        $this->warn("Treinamento já existe para o CPF: {$dados['cpf']}, ID: {$treinamentoModel->id}, Feedback ID: {$dados['feedback_id']}");

                        // Debug: mostrar dados do modelo existente
                        $this->info("Dados do treinamento existente: " . json_encode($treinamentoModel->toArray(), JSON_PRETTY_PRINT));

                        // Atualizar os dados do treinamento com os dados do JSON
                        $this->atualizarTreinamento($treinamentoModel, $dados);

                        $this->salvarHistorico($dados['feedback_id'], $treinamentoModel->id, $empresaId);
                        $this->info("Histórico de vencimentos salvo com sucesso para o treinamento ID: {$treinamentoModel->id}");

                        // Processar vencimentos existentes
                        $this->info("Iniciando processamento de vencimentos existentes...");
                        $this->processarVencimentosExistentes($treinamentoModel, $dados);
                    }

                } catch (\Exception $e) {
                    $this->error("Erro no registro {$index} (CPF: {$dados['cpf']}): " . $e->getMessage());
                    $this->error("Stack trace: " . $e->getTraceAsString());

                    // Mostrar queries executadas até agora
                    $queries = \DB::getQueryLog();
                    $this->error("Últimas queries executadas:");
                    foreach (array_slice($queries, -5) as $query) {
                        $this->error("SQL: {$query['query']}");
                        $this->error("Bindings: " . json_encode($query['bindings']));
                    }

                    throw $e; // Re-throw para parar a execução
                }
            }

            \DB::commit();
            $this->info('Importação concluída com sucesso!');
            return true;

        } catch (\Exception $e) {
            \DB::rollback();
            $this->error('Erro ao iniciar o comando: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());

            // Mostrar queries executadas
            $queries = \DB::getQueryLog();
            $this->error("Queries executadas:");
            foreach ($queries as $query) {
                $this->error("SQL: {$query['query']}");
                $this->error("Bindings: " . json_encode($query['bindings']));
            }

            return false;
        }
    }

    private function atualizarTreinamento($treinamentoModel, $dados)
    {
        $this->info("Atualizando dados do treinamento ID: {$treinamentoModel->id}");

        $dadosAtualizacao = collect($dados)->except([
            'vencimento_id',
            'data_vencimento',
            'data_treinamento'
        ])->toArray();

        $this->info("Dados que serão atualizados: " . json_encode($dadosAtualizacao, JSON_PRETTY_PRINT));

        $treinamentoModel->update($dadosAtualizacao);
        $treinamentoModel->refresh();

        $this->info("Treinamento atualizado com sucesso!");
        $this->info("Dados atualizados do treinamento: " . json_encode($treinamentoModel->toArray(), JSON_PRETTY_PRINT));
    }

    private function processarVencimentosExistentes($treinamentoModel, $dados)
    {
        try {
            $this->info("=== INÍCIO processarVencimentosExistentes ===");
            $this->info("Treinamento ID: {$treinamentoModel->id}");
            $this->info("Vencimento ID do arquivo: {$dados['vencimento_id']}");

            // Debug: verificar se o modelo tem as chaves corretas
            $this->info("Primary key do modelo: " . $treinamentoModel->getKeyName());
            $this->info("Valor da primary key: " . $treinamentoModel->getKey());

            // Buscar vencimentos usando query builder direto primeiro
            $this->info("Buscando vencimentos com query builder...");
            $vencimentosCount = \DB::table('treinamento_vencimento')
                ->where('treinamento_id', $treinamentoModel->id)
                ->count();
            $this->info("Vencimentos encontrados via query builder: {$vencimentosCount}");

            // Agora tentar com Eloquent
            $this->info("Buscando vencimentos com Eloquent...");
            $vencimentosAtuais = $treinamentoModel->Vencimentos;
            $this->info("Processando {$vencimentosAtuais->count()} vencimentos existentes para o treinamento ID: {$treinamentoModel->id}");

            // Verificar se o vencimento do arquivo existe na tabela vencimentos
            $vencimentoArquivoExiste = \DB::table('vencimentos')->where('id', $dados['vencimento_id'])->exists();

            if (!$vencimentoArquivoExiste) {
                $this->warn("Vencimento ID {$dados['vencimento_id']} do arquivo não existe na tabela vencimentos. Pulando...");
                return;
            }

            // Preparar dados para o novo vencimento
            $novoVencimentoData = [
                'data_vencimento' => (new DataHora($dados['data_vencimento']))->dataInsert(),
                'data_treinamento' => (new DataHora($dados['data_treinamento']))->dataInsert(),
                'numero_fat' => $dados['numero_fat'],
                'arquivo_id' => $dados['arquivo_id']
            ];

            $this->info("Dados do novo vencimento: " . json_encode($novoVencimentoData, JSON_PRETTY_PRINT));

            // Verificar se o vencimento já está vinculado ao treinamento usando query builder
            $this->info("Verificando se vencimento já está vinculado...");
            $vencimentoJaVinculado = \DB::table('treinamento_vencimento')
                ->where('treinamento_id', $treinamentoModel->id)
                ->where('vencimento_id', $dados['vencimento_id'])
                ->exists();

            if ($vencimentoJaVinculado) {
                $this->info("Vencimento ID {$dados['vencimento_id']} já está vinculado ao treinamento. Atualizando dados...");

                \DB::table('treinamento_vencimento')
                    ->where('treinamento_id', $treinamentoModel->id)
                    ->where('vencimento_id', $dados['vencimento_id'])
                    ->update($novoVencimentoData);

                $this->info("Dados do vencimento atualizados com sucesso");
            } else {
                $this->info("Vinculando novo vencimento ID {$dados['vencimento_id']} ao treinamento");

                \DB::table('treinamento_vencimento')->insert([
                    'treinamento_id' => $treinamentoModel->id,
                    'vencimento_id' => $dados['vencimento_id'],
                    ...$novoVencimentoData
                ]);

                $this->info("Vencimento vinculado com sucesso");
            }

            $this->info("=== FIM processarVencimentosExistentes ===");

        } catch (\Exception $e) {
            $this->error("Erro em processarVencimentosExistentes: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
            throw $e;
        }
    }

    private function salvarHistorico(int $feedbackId, int $treinamentoId, int $empresa_id): void
    {
        try {
            $this->info("=== INÍCIO salvarHistorico ===");
            $this->info("Salvando histórico de vencimentos para o treinamento ID: {$treinamentoId}, feedback ID: {$feedbackId}, empresa ID: {$empresa_id}");

            // Debug: buscar treinamento
            $this->info("Buscando treinamento com vencimentos...");
            $treinamento = Treinamento::with('Vencimentos')->find($treinamentoId);

            if (!$treinamento) {
                $this->error("Treinamento ID {$treinamentoId} não encontrado para histórico");
                return;
            }

            $this->info("Treinamento encontrado. Vencimentos: " . $treinamento->Vencimentos->count());

            TreinamentoVencimentoHistorico::create([
                'feedback_id' => $feedbackId,
                'empresa_id' => $empresa_id,
                'treinamento_id' => $treinamentoId,
                'user_id' => $empresa_id,
                'treinamentos_vencimentos' => $treinamento->Vencimentos
            ]);

            $this->info("=== FIM salvarHistorico ===");

        } catch (\Exception $e) {
            $this->error("Erro em salvarHistorico: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
            throw $e;
        }
    }

    public function autenticaEmpresa()
    {
        $empresaId = $this->option('empresa_id');
        if (empty($empresaId)) {
            $this->error('Nenhum ID de empresa informado');
            return false;
        }
        $this->info("Autenticando empresa com ID {$empresaId}...");
        \Auth::loginUsingId($empresaId);
        return true;
    }

    public function getArquivo()
    {
        $arquivo = $this->option('arquivo');
        if (empty($arquivo)) {
            $this->error('Nenhum arquivo informado');
            return false;
        }
        $path = base_path("scripts/import_treinamento_json/{$arquivo}.json");
        if (!file_exists($path)) {
            $this->error("Arquivo {$path} não encontrado");
            return false;
        }

        $this->info("Arquivo {$path} encontrado");

        $jsonContent = file_get_contents($path);
        if ($jsonContent === false) {
            $this->error("Erro ao ler o arquivo {$path}");
            return false;
        }
        $data = json_decode($jsonContent, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Erro ao decodificar o JSON: ' . json_last_error_msg());
            return false;
        }
        return collect($data);
    }
}
