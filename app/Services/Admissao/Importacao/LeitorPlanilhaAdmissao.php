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
     * Normaliza nomes de colunas: remove asterisco, trim, lowercase, espaços -> underscore,
     * para que "CPF*", "Cod. Vaga", "Data Admissão" batam com as chaves esperadas (cpf, cod_vaga, data_admissao).
     */
    private function normalizarCabecalhos(array $headerRow): array
    {
        $headers = [];
        foreach ($headerRow as $index => $value) {
            $key = is_scalar($value) ? trim((string) $value) : '';
            $key = preg_replace('/\*+$/', '', $key);
            $key = trim($key);
            $key = mb_strtolower($key);
            $key = preg_replace('/\s+/', '_', $key);
            $key = $this->removerAcentosCabecalho($key);
            $headers[$index] = $key;
        }
        return $headers;
    }

    private function removerAcentosCabecalho(string $key): string
    {
        $map = ['ã' => 'a', 'á' => 'a', 'à' => 'a', 'â' => 'a', 'ä' => 'a', 'é' => 'e', 'ê' => 'e', 'í' => 'i', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ú' => 'u', 'ü' => 'u', 'ç' => 'c'];
        return strtr(mb_strtolower($key), $map);
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
                return $this->normalizarDataString($valor);
            }
        }

        return $this->normalizarDataString($valor);
    }

    /**
     * Normaliza string de data: trim e, se for yyyy-d-m (dia/mês trocados), converte para dd/mm/yyyy.
     */
    private function normalizarDataString($valor): string
    {
        $v = is_scalar($valor) ? trim((string) $valor) : '';
        if ($v === '') {
            return $v;
        }
        // yyyy-d-m ou yyyy-dd-m (ex.: 1994-29-7) -> interpretar como dia=29, mês=7
        if (preg_match('/^(\d{4})\D+(\d{1,2})\D+(\d{1,2})$/', $v, $m)) {
            $ano = (int) $m[1];
            $a = (int) $m[2];
            $b = (int) $m[3];
            if ($b >= 1 && $b <= 12 && $a >= 1 && $a <= 31 && checkdate($b, $a, $ano)) {
                return sprintf('%02d/%02d/%04d', $a, $b, $ano);
            }
            if ($a >= 1 && $a <= 12 && $b >= 1 && $b <= 31 && checkdate($a, $b, $ano)) {
                return sprintf('%02d/%02d/%04d', $b, $a, $ano);
            }
        }
        return $v;
    }
}
