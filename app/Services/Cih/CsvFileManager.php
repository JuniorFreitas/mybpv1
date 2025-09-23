<?php

namespace App\Services\Cih;

use Illuminate\Support\Facades\Storage;

class CsvFileManager
{
    private const CHUNK_SIZE = 1000;
    private string $tempFilePath;
    private $fileHandle;

    public function createTempFile(array $headers): string
    {
        $this->tempFilePath = tempnam(sys_get_temp_dir(), 'cih_export_') . '.csv';
        $this->fileHandle = fopen($this->tempFilePath, 'w');

        if (!$this->fileHandle) {
            throw new \Exception("Não foi possível criar arquivo temporário: {$this->tempFilePath}");
        }

        // Adicionar BOM para UTF-8
        fwrite($this->fileHandle, "\xEF\xBB\xBF");

        // Escrever cabeçalhos
        fputcsv($this->fileHandle, $headers, ';', '"');

        return $this->tempFilePath;
    }

    public function writeDataInChunks($query, CihExportFormatter $formatter): int
    {
        $totalRecords = $query->count();
        $rowsWritten = 0;

        \Log::info("Total de registros: {$totalRecords}");

        $query->chunk(self::CHUNK_SIZE, function ($cihs) use ($formatter, &$rowsWritten) {
            foreach ($cihs as $cih) {
                foreach ($cih->colaboradores as $colaborador) {
                    $row = $formatter->formatRow($cih, $colaborador);

                    if ($this->isValidRow($row)) {
                        fputcsv($this->fileHandle, $row, ';', '"');
                        $rowsWritten++;
                    }
                }
            }
        });

        \Log::info("Total de linhas escritas: {$rowsWritten}");
        return $rowsWritten;
    }

    public function closeFile(): void
    {
        if ($this->fileHandle) {
            fclose($this->fileHandle);
        }
    }

    public function uploadToS3(string $s3FilePath): void
    {
        $fileContent = file_get_contents($this->tempFilePath);
        Storage::disk('disco-exportacao')->put($s3FilePath, $fileContent);
        \Log::info("Upload para S3 concluído: {$s3FilePath}");
    }

    public function cleanup(): void
    {
        if (file_exists($this->tempFilePath)) {
            unlink($this->tempFilePath);
        }
    }

    private function isValidRow(array $row): bool
    {
        return collect($row)->contains(fn($field) => !empty(trim($field)));
    }
}
