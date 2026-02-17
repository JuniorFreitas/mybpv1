<?php

namespace App\Services\MudancaCargo;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class MudancaCargoCsvFileManager
{
    private const CHUNK_SIZE = 500;

    private string $tempFilePath;
    private $fileHandle;

    public function createTempFile(array $headers): string
    {
        $this->tempFilePath = tempnam(sys_get_temp_dir(), 'mudanca_cargo_export_') . '.csv';
        $this->fileHandle = fopen($this->tempFilePath, 'w');
        if (!$this->fileHandle) {
            throw new \RuntimeException("Não foi possível criar arquivo temporário: {$this->tempFilePath}");
        }
        fwrite($this->fileHandle, "\xEF\xBB\xBF");
        fputcsv($this->fileHandle, $headers, ';', '"');
        return $this->tempFilePath;
    }

    public function writeDataInChunks(Builder $query, MudancaCargoExportFormatter $formatter): int
    {
        $rowsWritten = 0;
        $query->chunk(self::CHUNK_SIZE, function ($itens) use ($formatter, &$rowsWritten) {
            foreach ($itens as $row) {
                $rowData = $formatter->formatRow($row);
                if ($this->isValidRow($rowData)) {
                    fputcsv($this->fileHandle, $rowData, ';', '"');
                    $rowsWritten++;
                }
            }
        });
        return $rowsWritten;
    }

    public function closeFile(): void
    {
        if (isset($this->fileHandle) && $this->fileHandle) {
            fclose($this->fileHandle);
        }
    }

    public function uploadToS3(string $s3FilePath): void
    {
        $fileContent = file_get_contents($this->tempFilePath);
        Storage::disk('disco-exportacao')->put($s3FilePath, $fileContent);
    }

    public function cleanup(): void
    {
        if (isset($this->tempFilePath) && file_exists($this->tempFilePath)) {
            unlink($this->tempFilePath);
        }
    }

    private function isValidRow(array $row): bool
    {
        return collect($row)->contains(fn($field) => $field !== null && trim((string) $field) !== '');
    }
}
