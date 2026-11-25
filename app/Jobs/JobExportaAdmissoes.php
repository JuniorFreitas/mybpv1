<?php

namespace App\Jobs;

use App\Events\Notificacoes\NotificacaoEvent;
use App\Http\Controllers\AdmissaoController;
use App\Models\Arquivo;
use App\Models\Exportacao;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Exportação de admissões em CSV (padrão CIH): chunk no banco + fwrite,
 * sem PhpSpreadsheet em memória e sem memory_limit 1024M no container ECS.
 */
class JobExportaAdmissoes implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;

    protected $requestData;

    protected $fileName;

    protected mixed $cacheKey;

    public int $timeout = 900;

    public int $tries = 3;

    public int $backoff = 10;

    /** Registros por chunk — mantém footprint baixo no worker. */
    public const CHUNK_QNT = 100;

    public function __construct(int $userId, array $requestData, string $fileName, ?string $cacheKey = null)
    {
        $this->userId = $userId;
        $this->requestData = $requestData;
        $this->fileName = $fileName;
        $this->cacheKey = $cacheKey;
    }

    public function handle(): void
    {
        try {
            $this->updateCacheStatus('processing', [
                'attempt' => $this->attempts(),
                'max_tries' => $this->tries,
            ]);

            \Auth::loginUsingId($this->userId);

            // Cap alinhado ao container ECS (~700MB). Nunca pedir 1024M aqui.
            ini_set('memory_limit', '256M');
            ini_set('max_execution_time', '0');

            if (function_exists('gc_enable')) {
                gc_enable();
            }

            $user = User::find($this->userId);
            if (!$user) {
                throw new \Exception("Usuário {$this->userId} não encontrado");
            }

            Log::info("Iniciando exportação CSV de admissões para usuário: {$this->userId} (Tentativa {$this->attempts()}/{$this->tries})");

            $localFilePath = $this->createLocalCsvFile();
            $this->uploadAndRegister($localFilePath);

            if (is_file($localFilePath)) {
                @unlink($localFilePath);
            }

            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }

            Log::info("Exportação CSV de admissões concluída para usuário: {$this->userId}. Memória: " . round(memory_get_usage(true) / 1024 / 1024, 1) . ' MB');

            $this->completeExport();
        } catch (\Exception $e) {
            Log::error('Erro ao exportar admissões: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            Log::error("Tentativa {$this->attempts()}/{$this->tries} falhou");

            $this->updateCacheStatus('retrying', [
                'attempt' => $this->attempts(),
                'max_tries' => $this->tries,
                'last_error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function updateCacheStatus(string $status, array $additionalData = []): void
    {
        if (!$this->cacheKey || !Cache::has($this->cacheKey)) {
            return;
        }

        $cacheData = Cache::get($this->cacheKey);
        $cacheData['status'] = $status;
        $cacheData['updated_at'] = now();

        foreach ($additionalData as $key => $value) {
            $cacheData[$key] = $value;
        }

        $expiresAt = $cacheData['expires_at'] ?? now()->addMinutes(15);
        Cache::put($this->cacheKey, $cacheData, $expiresAt);

        if ($status === 'retrying') {
            Log::warning('EXPORT_RETRY', [
                'user_id' => $this->userId,
                'attempt' => $additionalData['attempt'] ?? 0,
                'max_tries' => $additionalData['max_tries'] ?? 3,
                'last_error' => $additionalData['last_error'] ?? 'Unknown',
            ]);
        }
    }

    private function completeExport(): void
    {
        Cache::forget($this->cacheKey);

        Log::info('EXPORT_SUCCESS', [
            'user_id' => $this->userId,
            'filename' => $this->fileName,
            'attempts' => $this->attempts(),
            'cache_key' => $this->cacheKey,
            'format' => 'csv',
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Cache::forget($this->cacheKey);

        Log::error('EXPORT_FAILED_FINAL', [
            'user_id' => $this->userId,
            'filename' => $this->fileName,
            'total_attempts' => $this->tries,
            'error_message' => $exception->getMessage(),
            'error_file' => $exception->getFile(),
            'error_line' => $exception->getLine(),
            'cache_key' => $this->cacheKey,
        ]);

        try {
            Event::dispatch(new NotificacaoEvent([
                'user_id' => $this->userId,
                'local' => 'Relatório de Admissões',
                'error_message' => "Exportação falhou após {$this->tries} tentativas: " . $exception->getMessage(),
                'filename' => $this->fileName,
            ], NotificacaoEvent::EXPORTACAO_EXCEL, NotificacaoEvent::TIPO_PADRAO));
        } catch (\Exception $e) {
            Log::error('EXPORT_NOTIFICATION_FAILED', [
                'user_id' => $this->userId,
                'notification_error' => $e->getMessage(),
            ]);
        }
    }

    private function createLocalCsvFile(): string
    {
        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $localFilePath = $tempDir . DIRECTORY_SEPARATOR . $this->fileName;
        $file = fopen($localFilePath, 'w');
        if ($file === false) {
            throw new \Exception("Não foi possível criar arquivo temporário: {$localFilePath}");
        }

        fwrite($file, "\xEF\xBB\xBF");
        fputcsv($file, $this->headers(), ';', '"');

        $this->processDataInChunks($file);

        fclose($file);

        return $localFilePath;
    }

    /**
     * @param  resource  $file
     */
    private function processDataInChunks($file): void
    {
        $request = new Request($this->requestData);
        $query = (new AdmissaoController())->filtro($request);

        // Listagem usa SELECT/with enxutos — export precisa do registro completo.
        $table = $query->getModel()->getTable();
        $query->select("{$table}.*");
        $query->with($this->eagerLoadExportacao());

        if (!empty($this->requestData['selecionados'])) {
            $query->whereIn("{$table}.id", $this->requestData['selecionados']);
        }

        $chunkCount = 0;
        $totalProcessed = 0;

        Log::info("Iniciando CSV em chunks para usuário: {$this->userId}");

        $query->chunkById(self::CHUNK_QNT, function ($results) use ($file, &$chunkCount, &$totalProcessed) {
            foreach ($results as $item) {
                try {
                    $row = array_map([$this, 'cleanCell'], $this->buildRowData($item));
                    fputcsv($file, $row, ';', '"');
                    $totalProcessed++;
                } catch (\Exception $e) {
                    Log::warning("Erro ao processar item {$item->id}: " . $e->getMessage());
                }
            }

            $chunkCount++;

            if ($chunkCount % 5 === 0) {
                $this->updateCacheStatus('processing', [
                    'progress' => min(90, $chunkCount * 10),
                    'chunks_processed' => $chunkCount,
                    'records_processed' => $totalProcessed,
                ]);
            }

            if ($chunkCount % 10 === 0 && function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
                Log::info("Processados {$chunkCount} chunks, {$totalProcessed} registros. Memória: " . round(memory_get_usage(true) / 1024 / 1024, 1) . ' MB');
            }
        }, "{$table}.id", 'id');

        Log::info("CSV concluído. Total: {$totalProcessed} registros em {$chunkCount} chunks");
    }

    /**
     * Relações usadas em buildRowData — evita N+1.
     *
     * @return array<string, mixed>
     */
    private function eagerLoadExportacao(): array
    {
        return [
            'Empresa:id,nome,razao_social,cnpj',
            'TelPrincipal:id,curriculo_id,numero,tipo',
            'parecerRh',
            'parecerTecnica',
            'parecerRota',
            'parecerTeste',
            'ResultadoIntegrado:id,feedback_id,documentos_entregue,documentos_entregue_data,encaminhado_exame,encaminhado_exame_data,encaminhado_treinamento,encaminhado_treinamento_data',
            'BancoConta',
            'VagaAberta:id,vaga_id,municipio_id',
            'VagaAberta.VagaSelecionada:id,nome',
            'VagaAberta.Vaga:id,nome',
            'VagaAberta.Municipio:id,nome,uf',
            'Curriculo:id,nome,cpf,rg,orgao_expeditor,pcd,cid,naturalidade,estado_civil,sexo,filiacao_pai,filiacao_mae,nascimento,email,formacao,formacao_curso,logradouro,end_numero,complemento,bairro,municipio,uf,cep',
            'Curriculo.Escolaridade:id,tipo',
            'Curriculo.Dependentes',
            'Curriculo.FotoTres' => function ($q) {
                $q->select('arquivos.id');
            },
            'Admissao',
            'Admissao.AreaEtiqueta:id,label',
            'Admissao.CentroCusto:id,label',
            'Admissao.CentroCustoFilial',
            'Admissao.CentroCustoFilial.Filial',
            'Admissao.CentroCustoFilial.Filial.dados',
            'Admissao.DadosAdmissoes',
            'Admissao.UltimoAso:id,feedback_id,data_realizacao',
            'Admissao.QuemAdmitiu:id,nome',
            'Admissao.QuemAlterou:id,nome',
        ];
    }

    /**
     * @return list<string>
     */
    private function headers(): array
    {
        return [
            'Nome', 'Naturalidade', 'Estado Civil', 'Sexo', 'CPF', 'RG', 'Órgão emissor', 'Pai', 'Mãe', 'PCD', 'CID',
            'Destro/Canhoto', 'CNH', 'Nascimento', 'Idade', 'Formação', 'Calça', 'Bota',
            'Camisa Meia', 'Camisa Proteção', 'Empresa', 'Vaga', 'Ex Funcionário', 'Contato',
            'E-mail', 'Disponibilidade para turnos 6X2', 'Indicado', 'Indicado por quem', 'Indicado para qual área',
            'Endereço', 'Tem Rota que atende', 'Qual', 'Bairro Rota', 'Ponto de referência Rota',
            'Informado sobre ponto de referência', 'Qual', 'Bairro Residência', 'Ponto de referência Residência',
            'Teste aplicado', 'Resultado Teste Prático', 'Parecer final do teste', 'Rigger', 'Plataforma Móvel', 'Ponte Rolante',
            'Encaminhado para Documentos', 'Data Encaminhado para Documentos', 'Encaminhado para Exame',
            'Data Encaminhado para Exame', 'Encaminhado para treinamento', 'Data Encaminhado para Treinamento',
            'Área', 'Centro de Custo', 'CNPJ Filial', 'Centro de custo filial', 'Função', 'Cargo',
            'Salário R$', 'Documento', 'Documento Portaria', 'Tipo de admissão', 'Treinamento',
            'Tipo de Treinamento', 'Data Treinamento', 'Número Crachá', 'Data do ASO', 'PIS',
            'CTPS', 'CTPS Série', 'CTPS Data Emissão', 'CTPS UF', 'Título de Eleitor',
            'Título de Eleitor Sessão', 'Título de Eleitor Zona', 'Certificado de Reservista',
            'Certificado de Reservista Categoria', 'Dependentes', 'Conta PIX', 'Tipo de Chave PIX',
            'Chave PIX', 'Banco', 'Agência', 'Conta', 'Status Carteira de Treinamento e Etiqueta',
            'Status', 'Data da Admissão', 'Foto', 'Quem Admitiu', 'Quem Alterou',
        ];
    }

    /**
     * @return list<mixed>
     */
    private function buildRowData($item): array
    {
        $dependentes = '';

        if (count($item->Curriculo->Dependentes ?? []) > 0) {
            $item->Curriculo?->Dependentes?->each(function ($dependente) use (&$dependentes) {
                $cpf = $dependente->cpf ?: 'Não informado';
                $nascimento = $dependente->nascimento ?: 'Não informado';
                $dependentes .= 'Tipo: ';
                $dependentes .= $dependente->tipo == 'outro' ? $dependente->outro_tipo : \App\Models\UsuarioDependente::TIPOS_DEPENDENTES[$dependente->tipo] ?? $dependente->tipo;
                $dependentes .= ' | Nome: ' . $dependente->nome;
                $dependentes .= ' | CPF: ' . $cpf;
                $dependentes .= ' | Data de Nascimento: ' . $nascimento;
                $dependentes .= ' || ';
            });
        }

        $vagaNome = $item->VagaAberta?->VagaSelecionada?->nome
            ?? $item->VagaAberta?->Vaga?->nome
            ?? '';
        $vagaUf = $item->VagaAberta?->Municipio?->uf ?? '';

        return [
            $item->Curriculo?->nome ?? '',
            $item->Curriculo?->naturalidade ?? 'NÃO INFORMADO',
            $item->Curriculo?->estado_civil ?? 'NÃO INFORMADO',
            $item->Curriculo?->sexo ?? 'NÃO INFORMADO',
            $item->Curriculo?->cpf ?? '',
            $item->Curriculo?->rg ?? 'NÃO INFORMADO',
            $item->Curriculo?->orgao_expeditor ?? 'NÃO INFORMADO',
            $item->Curriculo?->filiacao_pai ?? '',
            $item->Curriculo?->filiacao_mae ?? '',
            $item->Curriculo?->pcd ? 'SIM' : 'NÃO',
            $item->Curriculo?->cid ?? 'NÃO INFORMADO',
            $item->parecerRh?->destro ?? 'NÃO INFORMADO',
            $item->parecerRh ? ($item->parecerRh->cnh ? ($item->parecerRh->cnh_tipo ?: 'NÃO INFORMADO') : 'Não possui') : 'NÃO INFORMADO',
            $item->Curriculo?->nascimento ?? '',
            $item->Curriculo?->idade ?? '',
            $item->Curriculo?->formacao <= 7 ? $item->Curriculo?->Escolaridade?->tipo : $this->getEscolaridade($item),
            $item->parecerRh?->calca ?? 'NÃO INFORMADO',
            $item->parecerRh?->bota ?? 'NÃO INFORMADO',
            $item->parecerRh?->camisa_meia ?? 'NÃO INFORMADO',
            $item->parecerRh?->camisa_protecao ?? 'NÃO INFORMADO',
            $item->Empresa?->cnpj ? $item->Empresa?->razao_social : ($item->Empresa?->nome ?? 'NÃO INFORMADO'),
            trim($vagaNome . ($vagaUf !== '' ? ' - ' . $vagaUf : '')),
            $item->parecerRh?->ex_funcionario ? 'SIM' : 'NÃO',
            $item->TelPrincipal ? $item->TelPrincipal?->numero : 'NÃO INFORMADO',
            $item->Curriculo->email ?? '',
            $item->parecerRh ? ($item->parecerRh?->turnos_seis_por_dois ? 'SIM' : 'NÃO') : 'NÃO INFORMADO',
            $item->parecerRh ? ($item->parecerRh->indicacao ? 'SIM' : 'NÃO') : 'NÃO INFORMADO',
            $item->parecerRh->indicado_por ?? '',
            $item->parecerTecnica?->indicado_area ?? '',
            $this->enderecoFuncionario($item->Curriculo),
            $item->parecerRota ? ($item->parecerRota?->tem_rota ? 'SIM' : 'NÃO') : 'NÃO INFORMADO',
            $item->parecerRota ? ($item->parecerRota?->qual ?: 'NÃO INFORMADO') : 'NÃO INFORMADO',
            $item->parecerRota ? ($item->parecerRota?->bairro_rota ?: 'NÃO INFORMADO') : 'NÃO INFORMADO',
            $item->parecerRota ? ($item->parecerRota?->ponto_referencia_rota ?: 'NÃO INFORMADO') : 'NÃO INFORMADO',
            $item->parecerRota ? ($item->parecerRota?->pega_onibus ? 'SIM' : 'NÃO') : 'NÃO INFORMADO',
            $item->parecerRota ? ($item->parecerRota?->pega_onibus_qual_ponto ?: 'NÃO INFORMADO') : 'NÃO INFORMADO',
            $item->parecerRota ? ($item->parecerRota?->bairro_residencia ?: 'NÃO INFORMADO') : 'NÃO INFORMADO',
            $item->parecerRota ? ($item->parecerRota?->ponto_referencia_residencia ?: 'NÃO INFORMADO') : 'NÃO INFORMADO',
            $item->parecerTeste->qual_teste ?? 'NÃO INFORMADO',
            $item->parecerTeste ? ($item->parecerTeste?->nota_teste == 0 ? 'Não se Aplica' : $item->parecerTeste?->nota_teste) : 'Aguardando',
            $item->parecerTeste?->parecer_final_teste ?: 'NÃO INFORMADO',
            $item->parecerTecnica ? ($item->parecerTecnica?->experiencia_cargas_rigger ? 'SIM' : 'NÃO') : 'NÃO INFORMADO',
            $item->parecerTecnica ? ($item->parecerTecnica?->opera_plat_movel ? 'SIM' : 'NÃO') : 'NÃO INFORMADO',
            $item->parecerTecnica ? ($item->parecerTecnica?->opera_plat_ponte ? 'SIM' : 'NÃO') : 'NÃO INFORMADO',
            $item->ResultadoIntegrado?->documentos_entregue ? 'SIM' : 'NÃO',
            $item->ResultadoIntegrado?->documentos_entregue ? (new \MasterTag\DataHora($item->ResultadoIntegrado?->documentos_entregue_data))->dataCompleta() : '',
            $item->ResultadoIntegrado?->encaminhado_exame ? 'SIM' : 'NÃO',
            $item->ResultadoIntegrado?->encaminhado_exame ? (new \MasterTag\DataHora($item->ResultadoIntegrado?->encaminhado_exame_data))->dataCompleta() : '',
            $item->ResultadoIntegrado?->encaminhado_treinamento ? 'SIM' : 'NÃO',
            $item->ResultadoIntegrado?->encaminhado_treinamento ? (new \MasterTag\DataHora($item->ResultadoIntegrado?->encaminhado_treinamento_data))->dataCompleta() : '',
            $item->Admissao->AreaEtiqueta->label ?? 'NÃO INFORMADO',
            $item->Admissao->CentroCusto->label ?? 'NÃO INFORMADO',
            $item->Admissao?->filial ? 'SIM' : 'NÃO',
            $item->Admissao->CentroCustoFilial?->Filial?->dados->razao_social ?? '',
            $item->Admissao->funcao ?? 'NÃO INFORMADO',
            $item->Admissao->cargo ?? 'NÃO INFORMADO',
            $item->Admissao->salario ?? 'NÃO INFORMADO',
            $item->Admissao->documento ?? 'NÃO INFORMADO',
            $item->Admissao->documento_portaria ?? 'NÃO INFORMADO',
            $item->Admissao->tipo_admissao ?? 'NÃO INFORMADO',
            $item->Admissao->treinamento ?? 'NÃO INFORMADO',
            $item->Admissao->tipo_treinamento ?? 'NÃO INFORMADO',
            $item->Admissao->data_treinamento ?? 'NÃO INFORMADO',
            $item->Admissao->numero_cracha ?? 'NÃO INFORMADO',
            $item->Admissao->UltimoAso->data_realizacao ?? 'NÃO INFORMADO',
            $item->Admissao->pis ?? 'NÃO INFORMADO',
            $item->Admissao?->DadosAdmissoes?->ctps_numero ?? 'NÃO INFORMADO',
            $item->Admissao?->DadosAdmissoes?->ctps_serie ?? 'NÃO INFORMADO',
            $item->Admissao?->DadosAdmissoes?->ctps_data_emissao ?? 'NÃO INFORMADO',
            $item->Admissao?->DadosAdmissoes?->ctps_uf ?? 'NÃO INFORMADO',
            $item->Admissao?->DadosAdmissoes?->titulo_eleitor_numero ?? 'NÃO INFORMADO',
            $item->Admissao?->DadosAdmissoes?->titulo_eleitor_sessao ?? 'NÃO INFORMADO',
            $item->Admissao?->DadosAdmissoes?->titulo_eleitor_zona ?? 'NÃO INFORMADO',
            $item->Admissao?->DadosAdmissoes?->cert_reservista_num ?? 'NÃO INFORMADO',
            $item->Admissao?->DadosAdmissoes?->cert_reservista_categoria ?? 'NÃO INFORMADO',
            $dependentes,
            $item->BancoConta ? ($item->BancoConta?->pix ? 'SIM' : 'NÃO') : 'NÃO INFORMADO',
            $item->BancoConta?->tipochavepix ?? '',
            $item->BancoConta?->chavepix ?? '',
            $item->BancoConta?->banco ?? 'NÃO INFORMADO',
            $item->BancoConta?->agencia ?? 'NÃO INFORMADO',
            $item->BancoConta?->conta ?? 'NÃO INFORMADO',
            $item->Admissao?->status_carteira_treinamento ?? 'NÃO INFORMADO',
            $item->Admissao?->status ?? 'NÃO INFORMADO',
            $item->Admissao?->data_admissao ?? 'NÃO INFORMADO',
            $item->Curriculo?->FotoTres?->isNotEmpty() ? 'SIM' : 'NÃO',
            $item->Admissao?->QuemAdmitiu?->nome ?? '',
            $item->Admissao?->QuemAlterou?->nome ?? '',
        ];
    }

    /**
     * Endereço do funcionário para a planilha.
     */
    private function enderecoFuncionario($curriculo): string
    {
        if (!$curriculo) {
            return 'NÃO INFORMADO';
        }

        $completo = $curriculo->endereco_completo;
        if (is_string($completo) && trim($completo) !== '') {
            return $completo;
        }

        $municipioUf = trim(implode(' / ', array_filter([
            $curriculo->municipio,
            $curriculo->uf,
        ], fn ($parte) => $parte !== null && trim((string) $parte) !== '')));

        $partes = array_filter([
            $curriculo->logradouro,
            $curriculo->complemento,
            $curriculo->end_numero,
            $curriculo->cep,
            $curriculo->bairro,
            $municipioUf !== '' ? $municipioUf : null,
        ], fn ($parte) => $parte !== null && trim((string) $parte) !== '');

        if ($partes === []) {
            return 'NÃO INFORMADO';
        }

        return implode(', ', $partes);
    }

    public function getEscolaridade($row): string
    {
        try {
            if ($row->Curriculo?->Escolaridade && $row->Curriculo?->Escolaridade?->id >= 8) {
                return $row->Curriculo?->Escolaridade?->tipo . ' - ' . ($row->Curriculo?->formacao_curso ?? 'NÃO INFORMADO');
            }

            if ($row->Curriculo?->Escolaridade && $row->Curriculo?->Escolaridade?->id <= 7) {
                return $row->Curriculo?->Escolaridade?->tipo ?? 'NÃO INFORMADO';
            }

            return 'NÃO INFORMADO';
        } catch (\Exception $e) {
            Log::debug($e->getMessage() . ' - ' . ($row->Curriculo->nome ?? ''));

            return 'NÃO INFORMADO';
        }
    }

    private function cleanCell($value): string
    {
        if ($value === null) {
            return '';
        }

        $text = is_scalar($value) ? (string) $value : json_encode($value, JSON_UNESCAPED_UNICODE);
        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        $text = preg_replace('/[\r\n\t]+/', ' ', $text) ?? $text;

        return trim($text);
    }

    private function uploadAndRegister(string $localFilePath): void
    {
        $s3Path = Storage::disk(Arquivo::DISCO_EXPORTACAO)
            ->putFile('', new \Illuminate\Http\File($localFilePath), 'private');

        if (!$s3Path) {
            throw new \Exception('Falha ao fazer upload para S3');
        }

        $local = 'Relatório de Admissões';

        Exportacao::create([
            'user_id' => $this->userId,
            'arquivo' => basename($s3Path),
            'local' => $local,
            'removido' => false,
        ]);

        Event::dispatch(new NotificacaoEvent([
            'user_id' => $this->userId,
            'local' => $local,
        ], NotificacaoEvent::EXPORTACAO_EXCEL, NotificacaoEvent::TIPO_PADRAO));

        Log::info("Exportação CSV salva no S3: {$s3Path}");
    }
}
