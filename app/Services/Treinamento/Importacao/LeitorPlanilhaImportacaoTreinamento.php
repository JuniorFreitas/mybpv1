<?php

namespace App\Services\Treinamento\Importacao;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use RuntimeException;

/**
 * Lê a planilha de importação de treinamentos (aba Dados) e converte para o payload do comando mybp:importa-treinamento.
 */
class LeitorPlanilhaImportacaoTreinamento
{
    public const ABA_DADOS = 'Dados';

    /**
     * @return list<array{
     *   nome: string,
     *   cpf: string,
     *   contrato: string,
     *   matricula: string,
     *   data_treinamento: string,
     *   empresa_id: string,
     *   vencimento_id: string,
     *   tipo: string
     * }>
     */
    public function ler(string $path, int $empresaId): array
    {
        $fullPath = $this->resolvePath($path);
        if (!is_readable($fullPath)) {
            throw new RuntimeException("Arquivo não encontrado ou não legível: {$path}");
        }

        $spreadsheet = IOFactory::load($fullPath);
        $sheet = $this->obterAbaDados($spreadsheet);
        $highestRow = (int) $sheet->getHighestDataRow();

        if ($highestRow < 2) {
            return [];
        }

        $headers = $this->normalizarCabecalhos(
            $sheet->rangeToArray('A1:C1', null, true, true, false)[0] ?? []
        );

        $idxColaborador = $this->indiceColuna($headers, 'colaborador');
        $idxTreinamento = $this->indiceColuna($headers, 'treinamento');
        $idxData = $this->indiceColuna($headers, 'data_treinamento');

        if ($idxColaborador === null || $idxTreinamento === null || $idxData === null) {
            throw new RuntimeException('Cabeçalhos inválidos. Esperado: colaborador, treinamento, data_treinamento.');
        }

        $registros = [];
        for ($row = 2; $row <= $highestRow; $row++) {
            $rowData = $sheet->rangeToArray('A' . $row . ':C' . $row, null, true, true, false)[0] ?? [];
            $colaboradorRaw = trim((string) ($rowData[$idxColaborador] ?? ''));
            $treinamentoRaw = trim((string) ($rowData[$idxTreinamento] ?? ''));
            $dataRaw = $rowData[$idxData] ?? null;

            if ($colaboradorRaw === '' && $treinamentoRaw === '' && ($dataRaw === null || $dataRaw === '')) {
                continue;
            }

            $colaborador = $this->parseColaborador($colaboradorRaw);
            $treinamento = $this->parseTreinamento($treinamentoRaw);
            $dataTreinamento = $this->normalizarData($dataRaw);

            if ($colaborador['cpf'] === '' || $treinamento['vencimento_id'] === '' || $dataTreinamento === '') {
                throw new RuntimeException("Linha {$row}: colaborador, treinamento e data_treinamento são obrigatórios.");
            }

            if (!$this->dataNoFormatoBrasileiro($dataTreinamento)) {
                throw new RuntimeException(
                    "Linha {$row}: data_treinamento inválida. Use o formato dd/mm/aaaa (exemplo: 15/03/2026)."
                );
            }

            $registros[] = [
                'nome' => $colaborador['nome'],
                'cpf' => $colaborador['cpf'],
                'contrato' => $treinamento['contrato'],
                // 1º campo do pipe do modelo = feedback_id (mantém chave matricula por compat. do comando)
                'matricula' => $colaborador['feedback_id'],
                'feedback_id' => $colaborador['feedback_id'],
                'data_treinamento' => $dataTreinamento,
                'empresa_id' => (string) $empresaId,
                'vencimento_id' => $treinamento['vencimento_id'],
                'tipo' => 'Fixo',
            ];
        }

        return $registros;
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

        $sheet = $spreadsheet->getSheetByName('Planilha1');
        if ($sheet !== null) {
            return $sheet;
        }

        return $spreadsheet->getSheet(0);
    }

    /**
     * @param array<int, mixed> $headerRow
     * @return array<int, string>
     */
    private function normalizarCabecalhos(array $headerRow): array
    {
        $headers = [];
        foreach ($headerRow as $index => $value) {
            $key = is_scalar($value) ? trim((string) $value) : '';
            $key = preg_replace('/\*+$/', '', $key) ?? $key;
            $key = mb_strtolower(trim($key));
            $key = preg_replace('/\s+/', '_', $key) ?? $key;
            $headers[(int) $index] = $key;
        }

        return $headers;
    }

    /**
     * @param array<int, string> $headers
     */
    private function indiceColuna(array $headers, string $nome): ?int
    {
        foreach ($headers as $index => $header) {
            if ($header === $nome) {
                return (int) $index;
            }
        }

        return null;
    }

    /**
     * @return array{feedback_id: string, cpf: string, nome: string, cargo: string}
     */
    private function parseColaborador(string $raw): array
    {
        $parts = array_pad(explode('|', $raw, 4), 4, '');

        return [
            'feedback_id' => trim((string) $parts[0]),
            'cpf' => trim((string) $parts[1]),
            'nome' => trim((string) $parts[2]),
            'cargo' => trim((string) $parts[3]),
        ];
    }

    /**
     * @return array{vencimento_id: string, contrato: string, label: string}
     */
    private function parseTreinamento(string $raw): array
    {
        $parts = array_pad(explode('|', $raw, 3), 3, '');

        return [
            'vencimento_id' => trim((string) $parts[0]),
            'contrato' => trim((string) $parts[1]) !== '' ? trim((string) $parts[1]) : 'GERAL',
            'label' => trim((string) $parts[2]),
        ];
    }

    private function normalizarData(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (is_numeric($value)) {
            try {
                $date = ExcelDate::excelToDateTimeObject((float) $value);

                return $date->format('d/m/Y');
            } catch (\Throwable) {
                return '';
            }
        }

        $texto = trim((string) $value);
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $texto)) {
            return $texto;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $texto)) {
            try {
                return (new \DateTimeImmutable(substr($texto, 0, 10)))->format('d/m/Y');
            } catch (\Throwable) {
                return '';
            }
        }

        return $texto;
    }

    private function dataNoFormatoBrasileiro(string $data): bool
    {
        if (!preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $data)) {
            return false;
        }

        [$dia, $mes, $ano] = array_map('intval', explode('/', $data));

        return checkdate($mes, $dia, $ano);
    }
}
