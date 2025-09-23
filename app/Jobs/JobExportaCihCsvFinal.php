<?php

namespace App\Jobs;

use App\Events\Notificacoes\NotificacaoEvent;
use App\Models\Cih;
use App\Models\Exportacao;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;

class JobExportaCihCsvFinal implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 600; // 10 minutos
    public $queue;

    protected $usuario;
    protected $local;
    protected $nomeArquivo;
    protected $filtros;
    protected $modelo_cih_config;

    const CHUNK_SIZE = 1000; // Chunk maior para reduzir requisições

    /**
     * Create a new job instance.
     */
    public function __construct($usuario, $local, $nomeArquivo, $filtros, $modelo_cih_config)
    {
        $this->usuario = $usuario;
        $this->local = $local;
        $this->nomeArquivo = $nomeArquivo;
        $this->filtros = $filtros;
        $this->modelo_cih_config = $modelo_cih_config;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            \Log::info('Iniciando exportação CIH CSV final');
            \Log::info('Filtros: ' . json_encode($this->filtros));
            $headers = $this->getHeaders();
            \Log::info('Cabeçalhos: ' . json_encode($headers));

            $localFilePath = $this->createLocalCsvFile($headers);

            $s3FilePath = $this->nomeArquivo;
            $this->uploadToS3($localFilePath, $s3FilePath);

            unlink($localFilePath);

            Event::dispatch(new NotificacaoEvent([
                'user_id' => $this->usuario,
                'local' => $this->local,
            ], NotificacaoEvent::EXPORTACAO_EXCEL, NotificacaoEvent::TIPO_PADRAO));

            Exportacao::create([
                'user_id' => $this->usuario,
                'arquivo' => $this->nomeArquivo,
                'local' => $this->local,
                'removido' => false,
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro na exportação CIH CSV: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Obter cabeçalhos baseado na configuração
     */
    private function getHeaders()
    {
        if ($this->modelo_cih_config == Cih::CONFIG_CENTRO_DE_CUSTO) {
            return [
                "CIH ID",
                "Colaborador",
                "PIS",
                "Cargo",
                "Centro de Custo",
                "Data Ocorrência",
                "data_ocorrencia_iso",
                "Ocorrência",
                "Responsável Lançamento",
                'Data Lançamento',
                "Ação",
                "Status Aprovação Gestor",
                "Data Aprovação Gestor",
                "Responsável Aprovação Gestor",
                "Status Aprovação RH",
                "Data Aprovação RH",
                "Responsável Aprovação RH"
            ];
        }

        return [
            "CIH ID",
            "Colaborador",
            "PIS",
            "Cargo",
            "Área",
            "Centro de Custo",
            "Data Ocorrência",
            "data_ocorrencia_iso",
            "Ocorrência",
            "Responsável Lançamento",
            'Data Lançamento',
            'data_iso_lancamento',
            "Ação",
            "Status Aprovação Gestor",
            "Data Aprovação Gestor",
            "data_iso_aprovacao_gestor",
            "Responsável Aprovação Gestor",
            "Status Aprovação RH",
            "Data Aprovação RH",
            "data_iso_aprovacao_rh",
            "Responsável Aprovação RH"
        ];
    }

    /**
     * Criar arquivo CSV local
     */
    private function createLocalCsvFile($headers)
    {
        \Log::info('Criando arquivo CSV local');
        $localFilePath = tempnam(sys_get_temp_dir(), 'cih_export_') . '.csv';
        $file = fopen($localFilePath, 'w');

        if (!$file) {
            throw new \Exception("Não foi possível criar arquivo temporário: {$localFilePath}");
        }

        // Adicionar BOM para UTF-8 (resolve caracteres especiais)
        fwrite($file, "\xEF\xBB\xBF");

        // Escrever cabeçalhos
        fputcsv($file, $headers, ';', '"');

        // Processar dados em chunks
        $this->processDataInChunks($file);

        fclose($file);

        \Log::info("Arquivo local criado: {$localFilePath}");
        return $localFilePath;
    }

    /**
     * Processar dados em chunks e escrever no arquivo local
     */
    private function processDataInChunks($file)
    {
        \Log::info('Processando dados em chunks');

        $query = $this->buildQuery();
        $totalRecords = $query->count();
        \Log::info("Total de registros: {$totalRecords}");

        $processedRecords = 0;
        $rowsWritten = 0;

        $query->chunk(self::CHUNK_SIZE, function ($cihs) use ($file, &$processedRecords, &$rowsWritten) {
            \Log::info('Processando chunk de ' . $cihs->count() . ' registros');

            foreach ($cihs as $cih) {
                foreach ($cih->colaboradores as $colaborador) {
                    $row = $this->formatRow($cih, $colaborador);

                    // Verificar se a linha não está vazia
                    if ($this->isValidRow($row)) {
                        fputcsv($file, $row, ';', '"');
                        $rowsWritten++;
                    }
                }
            }

            $processedRecords += $cihs->count();
            \Log::info('Processados: ' . $processedRecords . ' | Linhas escritas: ' . $rowsWritten);

            // Log de progresso
            \Log::info("CIH Export - Processados {$processedRecords} registros de CIH");
        });

        \Log::info("Total de linhas escritas no CSV: {$rowsWritten}");
    }

    /**
     * Verificar se a linha é válida (não vazia)
     */
    private function isValidRow($row)
    {
        // Verificar se pelo menos um campo não está vazio
        foreach ($row as $field) {
            if (!empty(trim($field))) {
                return true;
            }
        }
        return false;
    }

    /**
     * Upload do arquivo local para S3
     */
    private function uploadToS3($localFilePath, $s3FilePath)
    {
        \Log::info("Fazendo upload para S3: {$s3FilePath}");
        $fileContent = file_get_contents($localFilePath);
        Storage::disk('disco-exportacao')->put($s3FilePath, $fileContent);

        \Log::info("Upload para S3 concluído");
    }

    /**
     * Construir query baseada nos filtros
     */
    private function buildQuery()
    {
        try {
            $user = \App\Models\User::find($this->usuario);
            \Log::info('Usuário: ' . json_encode($user));
            if (!$user) {
                \Log::error('Usuário não encontrado: ' . $this->usuario);
                throw new \Exception("Usuário não encontrado: {$this->usuario}");
            }

            auth()->login($user);
            \Log::info('Autenticado: ' . json_encode($user));

            // Aplicar filtros diretamente sem usar o método filtro() do controller
            $query = $this->buildQueryDirectly($user);

            return $query;
        } catch (\Exception $e) {
            \Log::error("Erro ao construir query no JobExportaCihCsv: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Construir query diretamente sem depender de permissões
     */
    private function buildQueryDirectly($user)
    {
        \Log::info("Usuário: " . json_encode($user->toArray()));

        // Base query com relacionamentos
        $query = \App\Models\Cih::with([
            'colaboradores.Curriculo',
            'colaboradores.Admissao',
            'colaboradores.Admissao.CentroCusto',
            'colaboradores.VagaAberta.Vaga',
            'CentroDeCusto',
            'Area',
            'Tag',
            'ResponsavelLancamento',
            'ResponsavelAprovacao',
            'RhAprovacao'
        ])->where('empresa_id', $user->empresa_id);

        // Aplicar filtros de período se existirem
        if (isset($this->filtros['filtroPeriodo']) && $this->filtros['filtroPeriodo']) {
            if (isset($this->filtros['periodo'])) {
                $periodo = explode(' até ', $this->filtros['periodo']);
                if (count($periodo) == 2) {
                    $dataInicio = new \MasterTag\DataHora($periodo[0] . ' 00:00:00');
                    $dataFim = new \MasterTag\DataHora($periodo[1] . ' 23:59:59');
                    $query->where('data_lancamento', '>=', $dataInicio->dataHoraInsert())
                        ->where('data_lancamento', '<=', $dataFim->dataHoraInsert());
                }
            }
        }

        // Aplicar outros filtros
        if (isset($this->filtros['campoBusca']) && !empty($this->filtros['campoBusca'])) {
            $query->whereHas('colaboradores.Curriculo', function ($q) {
                $q->where('nome', 'like', '%' . $this->filtros['campoBusca'] . '%');
            });
        }

        if (isset($this->filtros['campoStatus']) && !empty($this->filtros['campoStatus'])) {
            $status = $this->filtros['campoStatus'];
            switch ($status) {
                case 'aberto':
                    $query->where('status', 'aberto');
                    break;
                case 'aprovado_gestor':
                    $query->where('status', 'aprovado')->whereNull('resposta_rh');
                    break;
                case 'aprovado_rh':
                    $query->where('resposta_rh', 'aprovado');
                    break;
                case 'reprovado':
                    $query->where(function ($q) {
                        $q->where('status', 'reprovado')->orWhere('resposta_rh', 'reprovado');
                    });
                    break;
            }
        }

        if (isset($this->filtros['campoTags']) && !empty($this->filtros['campoTags'])) {
            $query->whereHas('Tag', function ($q) {
                $q->where('id', $this->filtros['campoTags']);
            });
        }

        if (isset($this->filtros['campoAreas']) && !empty($this->filtros['campoAreas'])) {
            $query->whereHas('Area', function ($q) {
                $q->where('id', $this->filtros['campoAreas']);
            });
        }

        if (isset($this->filtros['campoCentrosDeCusto']) && !empty($this->filtros['campoCentrosDeCusto'])) {
            $query->whereHas('CentroDeCusto', function ($q) {
                $q->where('id', $this->filtros['campoCentrosDeCusto']);
            });
        }

        if (isset($this->filtros['campoGestores']) && !empty($this->filtros['campoGestores'])) {
            $query->whereHas('GestorAprovacao', function ($q) {
                $q->where('id', $this->filtros['campoGestores']);
            });
        }

        \Log::info("Query: " . $query->toSql());
        \Log::info("Bindings: " . json_encode($query->getBindings()));

        return $query->orderByDesc('created_at');
    }

    /**
     * Formatar linha para CSV
     */
    private function formatRow($cih, $colaborador)
    {
        // Função para limpar e normalizar texto
        $cleanText = function ($text) {
            if (empty($text)) return '';

            // Converter para UTF-8 se necessário
            $text = mb_convert_encoding($text, 'UTF-8', 'auto');

            // Remover quebras de linha e caracteres de controle
            $text = preg_replace('/[\r\n\t]+/', ' ', $text);

            // Remover espaços extras
            $text = trim($text);

            // Substituir caracteres problemáticos
            $text = str_replace(['√†', '√°', '√≥', '√≠', '√ß', '√£', '√µ', '√∫'],
                ['ã', 'á', 'ó', 'í', 'ç', 'ã', 'õ', 'ú'], $text);

            return $text;
        };

        if ($this->modelo_cih_config == Cih::CONFIG_CENTRO_DE_CUSTO) {
            return [
                $cleanText($cih->id ?? ''),
                $cleanText($colaborador->Curriculo->nome ?? ''),
                $cleanText($colaborador->Admissao->pis ?? ''),
                $cleanText($colaborador->VagaAberta->Vaga->nome ?? ''),
                $cleanText($cih->CentroDeCusto->label ?? ''),
                $cleanText($cih->data_lancamento ?? ''),
                $cleanText($cih->data_iso_lancamento ?? ''),
                $cleanText($cih->Tag ? $cih->Tag->label : $cih->outra_tag ?? ''),
                $cleanText($cih->ResponsavelLancamento ? $cih->ResponsavelLancamento->nome : ''),
                $cleanText($cih->data_criacao ?? ''),
                $cleanText($cih->data_iso_criacao ?? ''),
                $cleanText($cih->acao ?? ''),
                $cleanText($cih->status ?? "aguardando"),
                $cleanText($cih->data_aprovacao ?? ''),
                $cleanText($cih->data_iso_aprovacao_gestor ?? ''),
                $cleanText($cih->ResponsavelAprovacao ? $cih->ResponsavelAprovacao->nome : ''),
                $cleanText($cih->resposta_rh ?? ""),
                $cleanText($cih->data_aprovacao_rh ?? ''),
                $cleanText($cih->data_iso_aprovacao_rh ?? ''),
                $cleanText($cih->RhAprovacao ? $cih->RhAprovacao->nome : ''),
            ];
        }

        return [
            $cleanText($cih->id ?? ''),
            $cleanText($colaborador->Curriculo->nome ?? ''),
            $cleanText($colaborador->Admissao->pis ?? ''),
            $cleanText($colaborador->VagaAberta->Vaga->nome ?? ''),
            $cleanText($cih->area_id ? ($cih->Area->label ?? '') : ($cih->outra_area ?? '')),
            $cleanText($colaborador->Admissao->CentroDeCusto->label ?? ''),
            $cleanText($cih->data_lancamento ?? ''),
            $cleanText($cih->data_iso_lancamento ?? ''),
            $cleanText($cih->Tag ? $cih->Tag->label : $cih->outra_tag ?? ''),
            $cleanText($cih->ResponsavelLancamento ? $cih->ResponsavelLancamento->nome : ''),
            $cleanText($cih->data_criacao ?? ''),
            $cleanText($cih->data_iso_criacao ?? ''),
            $cleanText($cih->acao ?? ''),
            $cleanText($cih->status ?? "aguardando"),
            $cleanText($cih->data_aprovacao ?? ''),
            $cleanText($cih->data_iso_aprovacao_gestor ?? ''),
            $cleanText($cih->ResponsavelAprovacao ? $cih->ResponsavelAprovacao->nome : ''),
            $cleanText($cih->resposta_rh ?? ""),
            $cleanText($cih->data_aprovacao_rh ?? ''),
            $cleanText($cih->data_iso_aprovacao_rh ?? ''),
            $cleanText($cih->RhAprovacao ? $cih->RhAprovacao->nome : ''),
        ];
    }
}
