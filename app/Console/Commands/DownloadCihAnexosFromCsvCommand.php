<?php

namespace App\Console\Commands;

use App\Models\Arquivo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Helper\ProgressBar;
use Throwable;

/**
 * Baixa anexos de CIH (evidencias) listados em um CSV exportado no padrao CIH (coluna "CIH ID", separador ;).
 *
 * Uso (na raiz do projeto):
 *   php artisan mybp:download-cih-anexos /caminho/para/admissao_cih.csv
 *
 * Com Docker (servico tipico mybpdp):
 *   docker compose exec mybpdp php artisan mybp:download-cih-anexos /var/www/html/admissao_cih.csv
 *
 * Opcoes uteis:
 *   --dry-run              apenas verifica existencia no storage (use -v para listar cada objeto)
 *   --output-dir=...       pasta de saida (padrao: download_anexos_cih na raiz do repositorio)
 *   --empresa-id=          restringe a CIH dessa empresa (multi-tenant)
 *   --with-trashed         inclui CIH soft-deleted
 *   --with-thumbs          tambem baixa thumbnail quando for imagem
 *
 * Saida: todos os arquivos na raiz de --output-dir, nome {cih_id}_{arquivo_id}_{nome_sanitizado}{ext}
 * (sem subpastas por CIH).
 *
 * Documentacao: docs/DOWNLOAD_ANEXOS_CIH_CLI.md
 */
class DownloadCihAnexosFromCsvCommand extends Command
{
    protected $signature = 'mybp:download-cih-anexos
                            {csv : Caminho absoluto ou relativo a raiz do projeto do arquivo CSV}
                            {--output-dir=download_anexos_cih : Diretorio de saida (relativo a raiz do projeto ou absoluto)}
                            {--dry-run : Apenas verificar existencia no storage, sem gravar arquivos}
                            {--with-thumbs : Baixar tambem o thumb quando existir}
                            {--empresa-id= : Filtrar apenas CIH com este empresa_id}
                            {--with-trashed : Incluir registros de CIH com soft delete}
                            {--chunk-cih=500 : Tamanho do lote de CIH IDs na consulta ao banco}';

    protected $description = 'Baixa anexos de CIH (S3/storage) a partir dos IDs de um CSV exportado';

    public function handle(): int
    {
        $csvPath = $this->resolveCsvPath((string) $this->argument('csv'));
        if (!is_readable($csvPath)) {
            $this->error('CSV nao encontrado ou nao legivel: ' . (string) $this->argument('csv'));

            return self::FAILURE;
        }

        $cihIds = $this->parseCihIdsFromCsv($csvPath);
        if ($cihIds === []) {
            $this->error('Nenhum CIH ID valido encontrado no CSV (esperada coluna "CIH ID").');

            return self::FAILURE;
        }

        $this->info('CIH IDs unicos no CSV: ' . count($cihIds));

        $outputRoot = $this->resolveOutputRoot((string) $this->option('output-dir'));
        $dryRun = (bool) $this->option('dry-run');
        $withThumbs = (bool) $this->option('with-thumbs');
        $withTrashed = (bool) $this->option('with-trashed');
        $empresaId = $this->option('empresa-id');
        $empresaId = $empresaId !== null && $empresaId !== '' ? (int) $empresaId : null;
        $chunkCih = max(1, (int) $this->option('chunk-cih'));

        if (!$dryRun && !is_dir($outputRoot) && !mkdir($outputRoot, 0755, true) && !is_dir($outputRoot)) {
            $this->error('Nao foi possivel criar o diretorio de saida: ' . $outputRoot);

            return self::FAILURE;
        }

        $this->line('Saida: ' . ($dryRun ? '(dry-run, sem gravar)' : $outputRoot));
        if ($empresaId !== null) {
            $this->line('Filtro empresa_id: ' . $empresaId);
        }

        $rows = $this->fetchAnexoRows($cihIds, $chunkCih, $empresaId, $withTrashed);
        if ($rows === []) {
            $this->warn('Nenhum anexo encontrado no banco para os CIH IDs informados (e filtros aplicados).');

            return self::SUCCESS;
        }

        $totalSteps = count($rows);
        if ($withThumbs) {
            foreach ($rows as $row) {
                if ($this->rowTemThumb($row)) {
                    $totalSteps++;
                }
            }
        }

        $bar = $this->output->createProgressBar($totalSteps);
        $bar->start();

        $dryExists = 0;
        $dryMissing = 0;
        $ok = 0;
        $fail = 0;
        $errors = 0;

        foreach ($rows as $row) {
            $result = $this->processOneObject(
                (int) $row->cih_id,
                (int) $row->arquivo_id,
                (string) $row->disco,
                (string) $row->file,
                (string) $row->nome,
                (string) $row->extensao,
                $outputRoot,
                $dryRun,
                $bar
            );
            $this->accumulateResult($result, $dryRun, $dryExists, $dryMissing, $ok, $fail, $errors);

            if ($withThumbs && $this->rowTemThumb($row)) {
                $thumbResult = $this->processOneObject(
                    (int) $row->cih_id,
                    (int) $row->arquivo_id,
                    (string) $row->disco,
                    (string) $row->thumb,
                    (string) $row->nome . '_thumb',
                    $this->thumbExtensionSuffix((string) $row->thumb),
                    $outputRoot,
                    $dryRun,
                    $bar
                );
                $this->accumulateResult($thumbResult, $dryRun, $dryExists, $dryMissing, $ok, $fail, $errors);
            }
        }

        $bar->finish();
        $this->newLine(2);
        $this->info($dryRun ? 'Dry-run concluido.' : 'Download concluido.');
        if ($dryRun) {
            $this->line("Objetos existentes no storage: {$dryExists} | ausentes: {$dryMissing}");
            if ($errors > 0) {
                $this->warn("Erros (disco invalido ou excecao): {$errors}");
            }
        } else {
            $this->line("Arquivos gravados com sucesso: {$ok} | falhas: {$fail}");
            if ($errors > 0) {
                $this->warn("Erros (disco invalido ou excecao): {$errors}");
            }
        }

        return ($errors > 0 || (!$dryRun && $fail > 0)) ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @param array{state: string, present: ?bool} $result
     */
    private function accumulateResult(
        array $result,
        bool $dryRun,
        int &$dryExists,
        int &$dryMissing,
        int &$ok,
        int &$fail,
        int &$errors
    ): void {
        if ($result['state'] === 'error') {
            $errors++;

            return;
        }
        if ($dryRun) {
            if ($result['present'] === true) {
                $dryExists++;
            } elseif ($result['present'] === false) {
                $dryMissing++;
            }

            return;
        }
        if ($result['state'] === 'ok') {
            $ok++;
        } else {
            $fail++;
        }
    }

    private function resolveCsvPath(string $csv): string
    {
        if ($csv !== '' && ($csv[0] === '/' || (strlen($csv) > 2 && ctype_alpha($csv[0]) && $csv[1] === ':' && ($csv[2] === '\\' || $csv[2] === '/')))) {
            return $csv;
        }

        return base_path($csv);
    }

    /**
     * @return list<int>
     */
    private function parseCihIdsFromCsv(string $csvPath): array
    {
        $handle = fopen($csvPath, 'rb');
        if ($handle === false) {
            return [];
        }

        try {
            $firstLine = fgets($handle);
            if ($firstLine === false) {
                return [];
            }

            $firstLine = $this->stripBom($firstLine);
            $header = str_getcsv($firstLine, ';');
            $header = array_map(static fn ($item) => trim((string) $item, " \t\n\r\0\x0B\""), $header);

            $idx = array_search('CIH ID', $header, true);
            if ($idx === false) {
                return [];
            }

            $ids = [];
            while (($line = fgets($handle)) !== false) {
                $line = $this->stripBom($line);
                $row = str_getcsv($line, ';');
                if (!isset($row[$idx])) {
                    continue;
                }
                $val = trim((string) $row[$idx], " \t\n\r\0\x0B\"");
                if ($val === '' || strcasecmp($val, 'CIH ID') === 0) {
                    continue;
                }
                if (ctype_digit($val)) {
                    $ids[(int) $val] = true;
                }
            }

            $list = array_keys($ids);
            sort($list, SORT_NUMERIC);

            return $list;
        } finally {
            fclose($handle);
        }
    }

    private function stripBom(string $line): string
    {
        if (str_starts_with($line, "\xEF\xBB\xBF")) {
            return substr($line, 3);
        }

        return $line;
    }

    private function resolveOutputRoot(string $dir): string
    {
        if ($dir !== '' && ($dir[0] === '/' || (strlen($dir) > 2 && ctype_alpha($dir[0]) && $dir[1] === ':' && ($dir[2] === '\\' || $dir[2] === '/')))) {
            return rtrim($dir, '/\\');
        }

        return rtrim(base_path($dir), '/\\');
    }

    /**
     * @param list<int> $cihIds
     * @return list<object>
     */
    private function fetchAnexoRows(array $cihIds, int $chunkCih, ?int $empresaId, bool $withTrashed): array
    {
        $output = [];
        $seen = [];

        foreach (array_chunk($cihIds, $chunkCih) as $chunk) {
            $q = DB::table('cih_evidencia')
                ->join('arquivos', 'arquivos.id', '=', 'cih_evidencia.arquivo_id')
                ->join('cihs', 'cihs.id', '=', 'cih_evidencia.cih_id')
                ->whereIn('cih_evidencia.cih_id', $chunk)
                ->orderBy('cih_evidencia.cih_id')
                ->orderBy('arquivos.id')
                ->select([
                    'cih_evidencia.cih_id as cih_id',
                    'arquivos.id as arquivo_id',
                    'arquivos.file as file',
                    'arquivos.disco as disco',
                    'arquivos.nome as nome',
                    'arquivos.extensao as extensao',
                    'arquivos.imagem as imagem',
                    'arquivos.thumb as thumb',
                ]);

            if (!$withTrashed) {
                $q->whereNull('cihs.deleted_at');
            }
            if ($empresaId !== null) {
                $q->where('cihs.empresa_id', $empresaId);
            }

            foreach ($q->get() as $row) {
                $key = $row->cih_id . ':' . $row->arquivo_id;
                if (isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;
                $output[] = $row;
            }
        }

        return $output;
    }

    /**
     * @return array{state: string, present: ?bool}
     */
    private function processOneObject(
        int $cihId,
        int $arquivoId,
        string $disco,
        string $fileKey,
        string $nome,
        string $extensao,
        string $outputRoot,
        bool $dryRun,
        ProgressBar $bar
    ): array {
        try {
            if (!in_array($disco, Arquivo::LISTAGEM_DISCOS, true)) {
                $this->newLine();
                $this->warn("Disco nao suportado (arquivo_id={$arquivoId}): {$disco}");

                return ['state' => 'error', 'present' => null];
            }

            $disk = Storage::disk($disco);
            $exists = $disk->exists($fileKey);

            if ($dryRun) {
                if ($this->output->isVerbose()) {
                    $this->newLine();
                    $this->line("cih={$cihId} arquivo_id={$arquivoId} disco={$disco} file_key=" . basename($fileKey) . ' exists=' . ($exists ? 'yes' : 'no'));
                }

                return ['state' => 'ok', 'present' => $exists];
            }

            if (!$exists) {
                $this->newLine();
                $this->warn("Objeto inexistente no storage (cih={$cihId}, arquivo_id={$arquivoId}, disco={$disco})");

                return ['state' => 'missing', 'present' => false];
            }

            if (!is_dir($outputRoot) && !mkdir($outputRoot, 0755, true) && !is_dir($outputRoot)) {
                throw new \RuntimeException("Nao foi possivel criar: {$outputRoot}");
            }

            $safeNome = $this->sanitizeNomeArquivo($nome);
            $ext = $extensao !== '' ? $extensao : '';
            if ($ext !== '' && $ext[0] !== '.') {
                $ext = '.' . $ext;
            }
            $localName = $cihId . '_' . $arquivoId . '_' . $safeNome . $ext;
            $localPath = $outputRoot . DIRECTORY_SEPARATOR . $localName;

            $stream = $disk->readStream($fileKey);
            if (!is_resource($stream)) {
                $contents = $disk->get($fileKey);
                if ($contents === false) {
                    throw new \RuntimeException('readStream/get falhou');
                }
                file_put_contents($localPath, $contents);
            } else {
                $dest = fopen($localPath, 'wb');
                if ($dest === false) {
                    fclose($stream);
                    throw new \RuntimeException('fopen destino falhou');
                }
                stream_copy_to_stream($stream, $dest);
                fclose($dest);
                fclose($stream);
            }

            return ['state' => 'ok', 'present' => true];
        } catch (Throwable $e) {
            $this->newLine();
            $this->error("Erro ao processar arquivo_id={$arquivoId} cih={$cihId}: " . $e->getMessage());

            return ['state' => 'error', 'present' => null];
        } finally {
            $bar->advance();
        }
    }

    private function rowTemThumb(object $row): bool
    {
        $thumb = $row->thumb ?? null;
        if ($thumb === null || $thumb === '') {
            return false;
        }

        $imagem = $row->imagem;

        return $imagem === true || $imagem === 1 || $imagem === '1';
    }

    private function sanitizeNomeArquivo(string $nome): string
    {
        $nome = preg_replace('/[^\p{L}\p{N}_\-\. ]/u', '_', $nome) ?? '';
        $nome = trim(preg_replace('/_+/', '_', $nome) ?? '', '._ ');

        return $nome !== '' ? $nome : 'arquivo';
    }

    private function thumbExtensionSuffix(string $thumbKey): string
    {
        $base = basename($thumbKey);
        $pos = strrpos($base, '.');

        return $pos !== false ? substr($base, $pos) : '';
    }
}
