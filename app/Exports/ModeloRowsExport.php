<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * Exportação tabular padrão CIH (cabeçalho + linhas).
 * Campos vazios viram "Não informado"; booleanos viram SIM/NÃO.
 */
class ModeloRowsExport implements FromArray, WithHeadings
{
    protected $headdings;
    protected $rows;

    public function __construct($headdings, $rows)
    {
        $this->headdings = $headdings;
        $this->rows = $rows;
    }

    public function headings(): array
    {
        return array_map(fn ($h) => $this->sanitizar($h, true), array_values((array) $this->headdings));
    }

    public function array(): array
    {
        return array_map(function ($row) {
            return array_map(fn ($valor) => $this->sanitizar($valor), array_values((array) $row));
        }, $this->rows);
    }

    /**
     * @param  mixed  $valor
     */
    private function sanitizar($valor, bool $cabecalho = false): mixed
    {
        if ($valor === null || $valor === '') {
            return $cabecalho ? '' : 'Não informado';
        }

        if (is_bool($valor)) {
            return $valor ? 'SIM' : 'NÃO';
        }

        if (is_scalar($valor)) {
            return $valor;
        }

        return json_encode($valor, JSON_UNESCAPED_UNICODE) ?: ($cabecalho ? '' : 'Não informado');
    }
}
