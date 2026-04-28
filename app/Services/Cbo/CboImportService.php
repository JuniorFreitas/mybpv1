<?php

namespace App\Services\Cbo;

use App\Models\Cbo;
use App\Models\CboFamilia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CboImportService
{
    public const FONTE_PADRAO = 'Ministério do Trabalho e Emprego - CBO';

    private const CHUNK = 250;

    public function __construct(
        private readonly CboCsvUrlResolver $urlResolver,
        private readonly CboCsvDownloader $downloader,
        private readonly CboCsvReader $reader,
        private readonly CboColumnMapper $mapper,
    ) {}

    public function run(bool $force = false): CboImportResult
    {
        $urls = $this->urlResolver->resolve();

        $pathFamilias = $this->downloader->storagePath('familias.csv');
        $pathOcupacoes = $this->downloader->storagePath('ocupacoes.csv');
        $pathPerfil = $this->downloader->storagePath('perfil.csv');

        if (! $this->downloader->shouldUseCache($pathFamilias, $force)) {
            $this->downloader->downloadTo($urls['familias'], $pathFamilias);
        } else {
            Log::info('CBO: usando CSV de famílias em cache (menos de 24h)');
        }

        if (! $this->downloader->shouldUseCache($pathOcupacoes, $force)) {
            $this->downloader->downloadTo($urls['ocupacoes'], $pathOcupacoes);
        } else {
            Log::info('CBO: usando CSV de ocupações em cache (menos de 24h)');
        }

        if (! empty($urls['perfil'])) {
            if (! $this->downloader->shouldUseCache($pathPerfil, $force)) {
                $this->downloader->downloadTo($urls['perfil'], $pathPerfil);
            } else {
                Log::info('CBO: usando CSV de perfil em cache (menos de 24h)');
            }
        }

        $fam = $this->importFamilias($pathFamilias);
        $ocu = $this->importOcupacoes($pathOcupacoes);
        $per = ! empty($urls['perfil']) && is_file($pathPerfil)
            ? $this->importPerfil($pathPerfil)
            : ['ok' => 0, 'skip' => 0];

        if (empty($urls['perfil']) || ! is_file($pathPerfil)) {
            Log::warning('CBO: arquivo de perfil/descrição sumária não disponível; importação de famílias e ocupações segue sem descrição sumária.');
        }

        return new CboImportResult(
            familiasProcessadas: $fam['ok'],
            familiasIgnoradas: $fam['skip'],
            ocupacoesProcessadas: $ocu['ok'],
            ocupacoesIgnoradas: $ocu['skip'],
            perfisAtualizados: $per['ok'],
            perfisIgnorados: $per['skip'],
        );
    }

    /**
     * @return array{ok: int, skip: int}
     */
    private function importFamilias(string $path): array
    {
        $data = $this->reader->read($path);
        $headers = $data['headers'];
        $rows = $data['rows'];
        $map = $this->mapper->mapFamiliaColumns($headers);
        if ($map['codigo'] === null) {
            Log::error('CBO: coluna de código não encontrada no CSV de famílias', ['map' => $map, 'headers' => $headers]);

            throw new \RuntimeException('CSV de famílias: não foi possível detectar a coluna de código.');
        }

        $ok = 0;
        $skip = 0;
        $chunks = array_chunk($rows, self::CHUNK);
        foreach ($chunks as $chunk) {
            DB::transaction(function () use ($chunk, $map, &$ok, &$skip) {
                foreach ($chunk as $row) {
                    $codigo = $row[$map['codigo']] ?? '';
                    $titulo = $map['titulo'] !== null ? ($row[$map['titulo']] ?? '') : '';
                    $codigo = preg_replace('/\D/', '', $codigo) ?? '';
                    if ($codigo === '' || strlen($codigo) < 4) {
                        $skip++;

                        continue;
                    }
                    $codigo = $this->normalizeFamiliaCodigo($codigo);
                    CboFamilia::updateOrCreate(
                        ['codigo' => $codigo],
                        [
                            'titulo' => $titulo !== '' ? $titulo : null,
                            'ativo' => true,
                            'fonte' => self::FONTE_PADRAO,
                            'data_importacao' => now(),
                        ]
                    );
                    $ok++;
                }
            });
        }

        return ['ok' => $ok, 'skip' => $skip];
    }

    /**
     * @return array{ok: int, skip: int}
     */
    private function importOcupacoes(string $path): array
    {
        $data = $this->reader->read($path);
        $headers = $data['headers'];
        $rows = $data['rows'];
        $map = $this->mapper->mapOcupacaoColumns($headers);
        if ($map['codigo'] === null || $map['titulo'] === null) {
            Log::error('CBO: colunas obrigatórias não encontradas no CSV de ocupações', ['map' => $map, 'headers' => $headers]);

            throw new \RuntimeException('CSV de ocupações: não foi possível detectar colunas de código e título.');
        }

        $ok = 0;
        $skip = 0;
        $chunks = array_chunk($rows, self::CHUNK);
        foreach ($chunks as $chunk) {
            DB::transaction(function () use ($chunk, $map, &$ok, &$skip) {
                foreach ($chunk as $row) {
                    $codigo = preg_replace('/\D/', '', $row[$map['codigo']] ?? '') ?? '';
                    $titulo = trim($row[$map['titulo']] ?? '');
                    if ($codigo === '' || strlen($codigo) < 4 || $titulo === '') {
                        $skip++;

                        continue;
                    }
                    $codFamilia = null;
                    if ($map['familia'] !== null) {
                        $rawFam = $row[$map['familia']] ?? '';
                        $codFamilia = preg_replace('/\D/', '', $rawFam) ?? '';
                        $codFamilia = $codFamilia !== '' ? $this->normalizeFamiliaCodigo($codFamilia) : null;
                    }
                    if ($codFamilia === null || $codFamilia === '') {
                        $codFamilia = $this->mapper->deriveCodigoFamiliaFromOcupacao($codigo);
                    }

                    Cbo::updateOrCreate(
                        ['codigo' => $codigo],
                        [
                            'titulo' => $titulo,
                            'codigo_familia' => $codFamilia,
                            'ativo' => true,
                            'fonte' => self::FONTE_PADRAO,
                            'data_importacao' => now(),
                        ]
                    );
                    $ok++;
                }
            });
        }

        return ['ok' => $ok, 'skip' => $skip];
    }

    /**
     * @return array{ok: int, skip: int}
     */
    private function importPerfil(string $path): array
    {
        $data = $this->reader->read($path);
        $headers = $data['headers'];
        $rows = $data['rows'];
        $map = $this->mapper->mapPerfilColumns($headers);
        if ($map['codigo_familia'] === null || $map['descricao_sumaria'] === null) {
            Log::warning('CBO: colunas não detectadas no CSV de perfil; ignorando descrição sumária', ['map' => $map]);

            return ['ok' => 0, 'skip' => count($rows)];
        }

        /** @var array<string, list<string>> */
        $textosPorFamilia = [];
        $skipLinhas = 0;
        foreach ($rows as $row) {
            $codigo = preg_replace('/\D/', '', $row[$map['codigo_familia']] ?? '') ?? '';
            $texto = trim($row[$map['descricao_sumaria']] ?? '');
            if ($codigo === '' || strlen($codigo) < 4) {
                $skipLinhas++;

                continue;
            }
            $codigo = $this->normalizeFamiliaCodigo($codigo);
            if ($texto === '') {
                $skipLinhas++;

                continue;
            }
            $textosPorFamilia[$codigo][] = $texto;
        }

        $ok = 0;
        $skip = $skipLinhas;
        $maxBytes = 60000;

        foreach ($textosPorFamilia as $codigo => $textos) {
            $unicos = array_values(array_unique(array_filter(array_map('trim', $textos))));
            if ($unicos === []) {
                $skip++;

                continue;
            }
            $merged = implode("\n\n", $unicos);
            if (strlen($merged) > $maxBytes) {
                $merged = substr($merged, 0, $maxBytes) . "\n…";
            }

            try {
                $familia = CboFamilia::query()->where('codigo', $codigo)->first();
                if ($familia) {
                    $familia->update([
                        'descricao_sumaria' => $merged,
                        'data_importacao' => now(),
                    ]);
                } else {
                    CboFamilia::updateOrCreate(
                        ['codigo' => $codigo],
                        [
                            'titulo' => null,
                            'descricao_sumaria' => $merged,
                            'ativo' => true,
                            'fonte' => self::FONTE_PADRAO,
                            'data_importacao' => now(),
                        ]
                    );
                }
                $ok++;
            } catch (\Throwable $e) {
                Log::error('CBO: erro ao atualizar descrição sumária', [
                    'codigo_familia' => $codigo,
                    'erro' => $e->getMessage(),
                ]);
                $skip++;
            }
        }

        return ['ok' => $ok, 'skip' => $skip];
    }

    private function normalizeFamiliaCodigo(string $digits): string
    {
        if (strlen($digits) > 4) {
            return substr($digits, 0, 4);
        }

        return $digits;
    }
}
