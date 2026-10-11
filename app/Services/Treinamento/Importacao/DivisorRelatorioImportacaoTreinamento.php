<?php

namespace App\Services\Treinamento\Importacao;

/**
 * Divide o CSV completo da importação em arquivos de sucesso e erro.
 */
class DivisorRelatorioImportacaoTreinamento
{
    /**
     * @return array{sucesso: ?string, erro: ?string, total: int, sucesso_count: int, erros_count: int}
     */
    public function dividir(string $relatorioPath, string $dirSaida, string $prefixo): array
    {
        $resultado = [
            'sucesso' => null,
            'erro' => null,
            'total' => 0,
            'sucesso_count' => 0,
            'erros_count' => 0,
        ];

        if (!is_readable($relatorioPath)) {
            return $resultado;
        }

        $fp = fopen($relatorioPath, 'r');
        if ($fp === false) {
            return $resultado;
        }

        $header = fgetcsv($fp, 0, ';');
        if (!is_array($header)) {
            fclose($fp);

            return $resultado;
        }
        if (isset($header[0])) {
            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header[0]);
        }

        $statusIdx = array_search('Status', $header, true);
        if ($statusIdx === false) {
            $statusIdx = 5;
        }

        $linhasSucesso = [];
        $linhasErro = [];

        while (($row = fgetcsv($fp, 0, ';')) !== false) {
            if (!is_array($row) || count($row) < 1) {
                continue;
            }
            $resultado['total']++;
            $status = strtoupper(trim((string) ($row[$statusIdx] ?? '')));
            if ($status === 'SUCESSO') {
                $linhasSucesso[] = $row;
                $resultado['sucesso_count']++;
            } else {
                $linhasErro[] = $row;
                $resultado['erros_count']++;
            }
        }
        fclose($fp);

        if (!is_dir($dirSaida)) {
            mkdir($dirSaida, 0755, true);
        }

        if (count($linhasSucesso) > 0) {
            $pathSucesso = rtrim($dirSaida, '/') . '/' . $prefixo . '_sucesso.csv';
            $this->gravarCsv($pathSucesso, $header, $linhasSucesso);
            $resultado['sucesso'] = $pathSucesso;
        }

        if (count($linhasErro) > 0) {
            $pathErro = rtrim($dirSaida, '/') . '/' . $prefixo . '_erro.csv';
            $this->gravarCsv($pathErro, $header, $linhasErro);
            $resultado['erro'] = $pathErro;
        }

        return $resultado;
    }

    /**
     * @param list<string> $header
     * @param list<list<string|null>> $linhas
     */
    private function gravarCsv(string $path, array $header, array $linhas): void
    {
        $arquivo = fopen($path, 'w');
        if ($arquivo === false) {
            return;
        }
        fprintf($arquivo, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($arquivo, $header, ';');
        foreach ($linhas as $linha) {
            fputcsv($arquivo, $linha, ';');
        }
        fclose($arquivo);
    }
}
