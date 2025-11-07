<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class AvaliacaoNoventaDiasExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths
{
    protected $vencimentos;

    public function __construct(array $vencimentos)
    {
        $this->vencimentos = $vencimentos;
    }

    public function headings(): array
    {
        return [
            'Colaborador',
            'Data de Vencimento',
            'Status',
            'Dias em Atraso',
            'Observação'
        ];
    }

    public function array(): array
    {
        return array_map(function ($vencimento) {
            return [
                $vencimento['colaborador'],
                $vencimento['prazo_vencido'],
                $vencimento['status'],
                $vencimento['dias_atraso'] > 0 ? $vencimento['dias_atraso'] : '-',
                $vencimento['observacao'] ?? ''
            ];
        }, $this->vencimentos);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Estilo do cabeçalho
            1 => [
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'alignment' => ['horizontal' => 'center']
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 40,  // Colaborador
            'B' => 20,  // Data de Vencimento
            'C' => 15,  // Status
            'D' => 18,  // Dias em Atraso
            'E' => 50,  // Observação
        ];
    }
}
