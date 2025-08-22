<?php

namespace App\Console\Commands;

use App\Models\Treinamento;
use App\Models\TreinamentoVencimentoHistorico;
use Illuminate\Console\Command;
use MasterTag\DataHora;

class ImportaTreinamento extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mybp:importa-treinamento {--empresa_id= : Description of option1} {--chunk-size=1000 : Description of option2} {--arquivo= : Description of option3}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            \DB::beginTransaction();
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

            foreach ($dadosJson as $dados) {
                $this->info("Importando cpf: {$dados['cpf']}");
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

                $treinamentoModel = Treinamento::where('tipo', $dados['tipo'])
                    ->where('feedback_id', $dados['feedback_id'])
                    ->first();

                if (!$treinamentoModel) {
                    // Criar novo treinamento
                    $this->info("Treinamento não encontrado, criando novo treinamento para o CPF: {$dados['cpf']}");
                    $treinamentoNovo = Treinamento::create($dados);
                    $treinamentoModel = Treinamento::find($treinamentoNovo->id);
                    $this->info("Treinamento criado com sucesso: ID {$treinamentoModel->id}, Tipo: {$treinamentoModel->tipo}");

                    // Verificar se o vencimento existe antes de criar o pivot
                    $vencimentoExiste = \DB::table('vencimentos')->where('id', $dados['vencimento_id'])->exists();

                    if ($vencimentoExiste) {
                        $TreinamentoVencimento = \App\Models\Pivot\TreinamentoVencimento::create([
                            'vencimento_id' => $dados['vencimento_id'],
                            'treinamento_id' => $treinamentoModel->id,
                            'data_vencimento' => $dados['data_vencimento'],
                            'data_treinamento' => $dados['data_treinamento'],
                            'numero_fat' => $dados['numero_fat'],
                            'arquivo_id' => $dados['arquivo_id']
                        ]);
                        $this->info("TreinamentoVencimento criado com sucesso: ID {$TreinamentoVencimento->id}");
                    } else {
                        $this->warn("Vencimento ID {$dados['vencimento_id']} não existe na tabela vencimentos");
                    }

                    $this->info('Salvando histórico de vencimentos...');
                    $this->salvarHistorico($dados['feedback_id'], $treinamentoModel->id, $empresaId);
                    $this->info("Histórico de vencimentos salvo com sucesso para o treinamento ID: {$treinamentoModel->id}");

                } else {
                    // Treinamento já existe - atualizar dados e processar vencimentos
                    $this->warn("Treinamento já existe para o CPF: {$dados['cpf']}, ID: {$treinamentoModel->id}, Feedback ID: {$dados['feedback_id']}");
                    $this->warn("=======================");
                    $this->warn("Dados atuais do treinamento: " . json_encode($treinamentoModel->toArray(), JSON_PRETTY_PRINT));
                    $this->warn("=======================");

                    // Atualizar os dados do treinamento com os dados do JSON
                    $this->atualizarTreinamento($treinamentoModel, $dados);

                    $this->salvarHistorico($dados['feedback_id'], $treinamentoModel->id, $empresaId);
                    $this->info("Histórico de vencimentos salvo com sucesso para o treinamento ID: {$treinamentoModel->id}");

                    // Processar vencimentos existentes
                    $this->processarVencimentosExistentes($treinamentoModel, $dados);
                }
            }

            \DB::commit();
            $this->info('Importação concluída com sucesso!');
            return true;

        } catch (\Exception $e) {
            \DB::rollback();
            $this->error('Erro ao iniciar o comando: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza um treinamento existente com dados do JSON
     */
    private function atualizarTreinamento($treinamentoModel, $dados)
    {
        $this->info("Atualizando dados do treinamento ID: {$treinamentoModel->id}");

        // Preparar dados para atualização (excluindo campos que não devem ser atualizados)
        $dadosAtualizacao = collect($dados)->except([
            'vencimento_id', // Este será tratado separadamente
            'data_vencimento', // Este será tratado nos vencimentos
            'data_treinamento' // Este será tratado nos vencimentos
        ])->toArray();

        // Mostrar dados que serão atualizados
        $this->info("Dados que serão atualizados: " . json_encode($dadosAtualizacao, JSON_PRETTY_PRINT));

        // Atualizar o treinamento
        $treinamentoModel->update($dadosAtualizacao);

        // Recarregar o modelo para mostrar os dados atualizados
        $treinamentoModel->refresh();

        $this->info("Treinamento atualizado com sucesso!");
        $this->info("Dados atualizados do treinamento: " . json_encode($treinamentoModel->toArray(), JSON_PRETTY_PRINT));
        $this->info("=======================");
    }

    /**
     * Processa vencimentos para treinamentos existentes
     */
    private function processarVencimentosExistentes($treinamentoModel, $dados)
    {
        // Buscar vencimentos atuais do treinamento
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
            'vencimento_id' => $dados['vencimento_id'],
            'data_vencimento' => (new DataHora($dados['data_vencimento']))->dataInsert(),
            'data_treinamento' => (new DataHora($dados['data_treinamento']))->dataInsert(),
            'numero_fat' => $dados['numero_fat'],
            'arquivo_id' => $dados['arquivo_id']
        ];

        // Verificar se o vencimento já está vinculado ao treinamento
        $vencimentoJaVinculado = $treinamentoModel->Vencimentos()
            ->where('vencimento_id', $dados['vencimento_id'])
            ->exists();

        if ($vencimentoJaVinculado) {
            $this->info("Vencimento ID {$dados['vencimento_id']} já está vinculado ao treinamento. Atualizando dados...");

            // Atualizar o registro existente na tabela pivot
            $treinamentoModel->Vencimentos()
                ->wherePivot('vencimento_id', $dados['vencimento_id'])
                ->updateExistingPivot($dados['vencimento_id'], $novoVencimentoData);

            $this->info("Dados do vencimento atualizados com sucesso");
        } else {
            $this->info("Vinculando novo vencimento ID {$dados['vencimento_id']} ao treinamento");

            // Adicionar novo vencimento
            $treinamentoModel->Vencimentos()->attach($dados['vencimento_id'], $novoVencimentoData);

            $this->info("Vencimento vinculado com sucesso");
        }
    }

    /**
     * Usar eager loading para evitar N+1
     * @param int $feedbackId
     * @param int $treinamentoId
     * @param int $empresa_id
     * @return void
     */
    private function salvarHistorico(int $feedbackId, int $treinamentoId, int $empresa_id): void
    {
        // Usar with() para eager loading - evita query N+1
        $this->info("Salvando histórico de vencimentos para o treinamento ID: {$treinamentoId}, feedback ID: {$feedbackId}, empresa ID: {$empresa_id}");
        $treinamento = Treinamento::with('Vencimentos')->find($treinamentoId);

        TreinamentoVencimentoHistorico::create([
            'feedback_id' => $feedbackId,
            'empresa_id' => $empresa_id,
            'treinamento_id' => $treinamentoId,
            'user_id' => $empresa_id,
            'treinamentos_vencimentos' => $treinamento->Vencimentos
        ]);
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

    /**
     * Get arquivo scripts/import_treinamento_json/{arquivo}.json
     */
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
