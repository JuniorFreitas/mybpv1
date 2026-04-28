<?php

namespace App\Services\Cbo;

use Illuminate\Support\Str;

class CboCsvReader
{
    /**
     * @return array{headers: array<int, string>, rows: array<int, array<int, string>>}
     */
    public function read(string $absolutePath, string $delimiter = ';'): array
    {
        $raw = file_get_contents($absolutePath);
        if ($raw === false) {
            throw new \RuntimeException("Não foi possível ler o arquivo CSV: {$absolutePath}");
        }

        $encoding = $this->detectEncoding($raw);
        if ($encoding !== 'UTF-8') {
            $converted = @mb_convert_encoding($raw, 'UTF-8', $encoding);
            if ($converted !== false) {
                $raw = $converted;
            }
        }

        $lines = preg_split("/\r\n|\n|\r/", $raw) ?: [];
        $lines = array_values(array_filter($lines, static fn ($line) => $line !== '' && trim($line) !== ''));

        if ($lines === []) {
            return ['headers' => [], 'rows' => []];
        }

        $headerLine = array_shift($lines);
        $headerCells = str_getcsv($headerLine, $delimiter);
        $headers = [];
        foreach ($headerCells as $idx => $cell) {
            $headers[$idx] = $this->normalizeHeaderKey((string) $cell);
        }

        $rows = [];
        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }
            $cells = str_getcsv($line, $delimiter);
            $row = [];
            foreach ($headers as $i => $_) {
                $row[$i] = isset($cells[$i]) ? trim((string) $cells[$i]) : '';
            }
            $rows[] = $row;
        }

        return ['headers' => $headers, 'rows' => $rows];
    }

    private function detectEncoding(string $raw): string
    {
        if (str_starts_with($raw, "\xEF\xBB\xBF")) {
            return 'UTF-8';
        }

        $detected = mb_detect_encoding($raw, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
        if ($detected !== false) {
            return $detected;
        }

        return 'UTF-8';
    }

    private function normalizeHeaderKey(string $header): string
    {
        return (string) Str::of($header)
            ->lower()
            ->ascii()
            ->replace(' ', '_')
            ->replace('-', '_')
            ->trim();
    }
}
