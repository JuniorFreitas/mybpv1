<?php

namespace App\Services\Admissao\Importacao;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use RuntimeException;

/**
 * Lê a planilha de importação de admissões em chunks (aba "Dados").
 * Converte datas Excel para dd/mm/yyyy e normaliza cabeçalhos.
 */
class LeitorPlanilhaAdmissao
{
    private const ABA_DADOS = 'Dados';

    /** Colunas que podem conter data serial do Excel */
    private const COLUNAS_DATA = [
        'cnh_vencimento', 'rg_emissao', 'nascimento', 'data_entrega_area',
        'ctps_data_emissao', 'data_admissao', 'data_aso', 'admissao_encerramento',
        'encaminhado_documento_data', 'encaminhado_exame_data', 'encaminhado_treinamento_data',
    ];

    /**
     * Lê o arquivo XLSX e retorna um iterador que a cada iteração entrega um chunk de linhas.
     * Cada linha é um array associativo (chave = nome da coluna normalizado, valor = string ou null).
     *
     * @param string $path Caminho absoluto ou relativo a storage_path('app')
     * @param int $chunkSize Quantidade de linhas por chunk
     * @return \Iterator<int, array<int, array<string, mixed>>>
     * @throws RuntimeException
     */
    public function ler(string $path, int $chunkSize = 100): \Iterator
    {
        $fullPath = $this->resolvePath($path);
        if (!is_readable($fullPath)) {
            throw new RuntimeException("Arquivo não encontrado ou não legível: {$path}");
        }

        $spreadsheet = IOFactory::load($fullPath);
        $sheet = $this->obterAbaDados($spreadsheet);
        $highestRow = $sheet->getHighestDataRow();
        $highestColumn = $sheet->getHighestDataColumn();

        if ($highestRow < 2) {
            return;
        }

        $headerRow = $sheet->rangeToArray('A1:' . $highestColumn . '1', null, true, true, false)[0] ?? [];
        $headers = $this->normalizarCabecalhos($headerRow);

        $chunk = [];
        $startRow = 2;

        for ($row = 2; $row <= $highestRow; $row++) {
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, null, true, true, false)[0] ?? [];
            $linha = $this->montarLinha($headers, $rowData);
            $chunk[] = $linha;

            if (count($chunk) >= $chunkSize) {
                yield $chunk;
                $chunk = [];
            }
        }

        if (count($chunk) > 0) {
            yield $chunk;
        }
    }

    private function resolvePath(string $path): string
    {
        if (str_starts_with($path, '/') || preg_match('#^[A-Za-z]:\\\\#', $path)) {
            return $path;
        }
        return storage_path('app/' . ltrim($path, '/'));
    }

    private function obterAbaDados(\PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet): \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
    {
        $sheet = $spreadsheet->getSheetByName(self::ABA_DADOS);
        if ($sheet !== null) {
            return $sheet;
        }
        return $spreadsheet->getSheet(0);
    }

    /**
     * Normaliza nomes de colunas: remove asterisco, trim, lowercase para chave.
     */
    private function normalizarCabecalhos(array $headerRow): array
    {
        $headers = [];
        foreach ($headerRow as $index => $value) {
            $key = is_scalar($value) ? trim((string) $value) : '';
            $key = preg_replace('/\*+$/', '', $key);
            $key = trim($key);
            $headers[$index] = $key;
        }
        return $headers;
    }

    private function montarLinha(array $headers, array $rowData): array
    {
        $linha = [];
        foreach ($headers as $colIndex => $nomeColuna) {
            $valor = $rowData[$colIndex] ?? null;
            if ($valor !== null && $valor !== '') {
                $valor = $this->converterValor($nomeColuna, $valor);
            } else {
                $valor = null;
            }
            $linha[$nomeColuna] = $valor;
        }
        return $linha;
    }

    private function converterValor(string $nomeColuna, $valor)
    {
        if (!in_array($nomeColuna, self::COLUNAS_DATA, true)) {
            return is_scalar($valor) ? trim((string) $valor) : $valor;
        }

        if (is_numeric($valor) && (float) $valor >= 1) {
            try {
                $date = ExcelDate::excelToDateTimeObject($valor);
                return $date->format('d/m/Y');
            } catch (\Throwable) {
                return is_scalar($valor) ? trim((string) $valor) : $valor;
            }
        }

        return is_scalar($valor) ? trim((string) $valor) : $valor;
    }
}
