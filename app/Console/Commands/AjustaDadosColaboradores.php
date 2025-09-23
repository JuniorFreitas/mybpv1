<?php

namespace App\Console\Commands;

use App\Models\Treinamento;
use App\Models\TreinamentoVencimentoHistorico;
use Illuminate\Console\Command;
use MasterTag\DataHora;

class AjustaDadosColaboradores extends Command
{
    protected $signature = 'mybp:ajusta-dados-colaboradores {--empresa_id= : Description of option1} {--chunk-size=1000 : Description of option2} {--arquivo= : Description of option3}';
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

            // Array para rastrear todos os registros processados
            $registrosProcessados = [];

            foreach ($dadosJson as $index => $dados) {
                $this->info("=== Processando registro {$index} - CPF: {$dados['cpf']} ===");

                // Inicializar dados do registro para o CSV
                $registro = [
                    'nome' => $dados['nome'] ?? '',
                    'cpf' => $dados['cpf'],
                    'pis' => $dados['pis'],
                    'atualizado' => 'NÃO',
                    'encontrado' => 'NÃO'
                ];

                try {
                    $feedback = \DB::table('feedback_curriculos')
                        ->select(['feedback_curriculos.id as feedback_id', 'curriculos.cpf as cpf', 'feedback_curriculos.empresa_id', 'admissoes.id as admissao_id'])
                        ->join('curriculos', 'curriculos.id', '=', 'feedback_curriculos.curriculo_id')
                        ->leftJoin('admissoes', 'admissoes.feedback_id', '=', 'feedback_curriculos.id')
                        ->where('curriculos.cpf', $dados['cpf'])
                        ->where('feedback_curriculos.empresa_id', $empresaId)
                        ->first();

                    if (!$feedback) {
                        $this->error("Nenhum feedback encontrado para o CPF: {$dados['cpf']}");
                        $registrosProcessados[] = $registro;
                        continue;
                    }

                    if (!$feedback->admissao_id) {
                        $this->error("Nenhuma admissão encontrada para o feedback ID: {$feedback->feedback_id}, CPF: {$dados['cpf']}");
                        $registrosProcessados[] = $registro;
                        continue;
                    }

                    $this->info("Feedback encontrado: ID {$feedback->feedback_id}, Admissão ID {$feedback->admissao_id}");
                    
                    // Marcar como encontrado
                    $registro['encontrado'] = 'SIM';
                    
                    // Atualizar o colaborador_id na tabela treinamento
                    $atualizaPisAdmissao = \DB::table('admissoes')
                        ->where('id', $feedback->admissao_id)
                        ->update(['pis' => $dados['pis']]);

                    if ($atualizaPisAdmissao) {
                        $this->info("Admissão ID {$feedback->admissao_id} atualizada com sucesso com o PIS: {$dados['pis']}");
                        $registro['atualizado'] = 'SIM';
                    }

                    $registrosProcessados[] = $registro;

                } catch (\Exception $e) {
                    $this->error("Erro no registro {$index} (CPF: {$dados['cpf']}): " . $e->getMessage());
                    $this->error("Stack trace: " . $e->getTraceAsString());

                    // Adicionar registro com erro
                    $registrosProcessados[] = $registro;

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
            
            // Gerar arquivo CSV com os resultados
            $this->gerarArquivoCsv($registrosProcessados);
            
            $this->info('Importação concluída com sucesso!');
            return true;

        } catch (\Exception $e) {
//            \DB::rollback();
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

    private function gerarArquivoCsv($registrosProcessados)
    {
        try {
            $this->info('Gerando arquivo CSV com os resultados...');
            
            // Definir nome do arquivo com timestamp
            $timestamp = date('Y-m-d_H-i-s');
            $nomeArquivo = "resultado_ajuste_colaboradores_{$timestamp}.csv";
            $caminhoArquivo = storage_path("app/{$nomeArquivo}");
            
            // Abrir arquivo para escrita
            $arquivo = fopen($caminhoArquivo, 'w');
            
            if (!$arquivo) {
                $this->error("Erro ao criar arquivo CSV: {$caminhoArquivo}");
                return;
            }
            
            // Escrever cabeçalho
            fputcsv($arquivo, ['nome', 'cpf', 'pis', 'atualizado', 'encontrado']);
            
            // Escrever dados
            foreach ($registrosProcessados as $registro) {
                fputcsv($arquivo, [
                    $registro['nome'],
                    $registro['cpf'],
                    $registro['pis'],
                    $registro['atualizado'],
                    $registro['encontrado']
                ]);
            }
            
            fclose($arquivo);
            
            $this->info("Arquivo CSV gerado com sucesso: {$caminhoArquivo}");
            $this->info("Total de registros processados: " . count($registrosProcessados));
            
            // Estatísticas
            $encontrados = count(array_filter($registrosProcessados, function($r) { return $r['encontrado'] === 'SIM'; }));
            $atualizados = count(array_filter($registrosProcessados, function($r) { return $r['atualizado'] === 'SIM'; }));
            
            $this->info("Registros encontrados: {$encontrados}");
            $this->info("Registros atualizados: {$atualizados}");
            
        } catch (\Exception $e) {
            $this->error("Erro ao gerar arquivo CSV: " . $e->getMessage());
        }
    }
}
