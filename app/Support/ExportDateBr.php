<?php

namespace App\Support;

use Carbon\Carbon;
use DateTimeInterface;
use MasterTag\DataHora;

/**
 * Formata valores de data para exportação no padrão brasileiro **somente data** (dd/mm/aaaa).
 * Remove hora de accessors como Cih::dataHoraCompleta() ("dd/mm/aaaa às hh:mmh").
 */
final class ExportDateBr
{
    public static function only(mixed $date): string
    {
        if ($date === null || $date === '') {
            return '';
        }

        if ($date instanceof DateTimeInterface) {
            return $date->format('d/m/Y');
        }

        $dateString = trim((string) $date);
        if ($dateString === '') {
            return '';
        }

        // Já é só data BR
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $dateString)) {
            return $dateString;
        }

        // ISO: 2026-04-09 ou 2026-04-09 13:45:00
        if (preg_match('/(\d{4})-(\d{2})-(\d{2})/', $dateString, $matches)) {
            return $matches[3] . '/' . $matches[2] . '/' . $matches[1];
        }

        // BR com hora: 09/04/2026 13:45:00 ou 09/04/2026 às 13:45h
        if (preg_match('/(\d{2})\/(\d{2})\/(\d{4})/', $dateString, $matches)) {
            return $matches[1] . '/' . $matches[2] . '/' . $matches[3];
        }

        try {
            return Carbon::parse($dateString)->format('d/m/Y');
        } catch (\Throwable) {
            try {
                return (new DataHora($dateString))->dataCompleta();
            } catch (\Throwable) {
                return '';
            }
        }
    }
}
