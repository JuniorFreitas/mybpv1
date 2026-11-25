<?php

namespace Tests\Unit\Support;

use App\Support\ExportDateBr;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExportDateBrTest extends TestCase
{
    #[Test]
    public function remove_hora_de_data_hora_completa_do_cih(): void
    {
        $this->assertSame('09/04/2026', ExportDateBr::only('09/04/2026 às 13:45h'));
    }

    #[Test]
    public function remove_hora_de_timestamp_br(): void
    {
        $this->assertSame('09/04/2026', ExportDateBr::only('09/04/2026 13:45:00'));
    }

    #[Test]
    public function converte_iso_com_hora(): void
    {
        $this->assertSame('09/04/2026', ExportDateBr::only('2026-04-09 13:45:00'));
    }

    #[Test]
    public function mantem_somente_data_br(): void
    {
        $this->assertSame('09/04/2026', ExportDateBr::only('09/04/2026'));
    }

    #[Test]
    public function aceita_carbon(): void
    {
        $this->assertSame('09/04/2026', ExportDateBr::only(Carbon::parse('2026-04-09 18:30:00')));
    }

    #[Test]
    public function vazio_retorna_string_vazia(): void
    {
        $this->assertSame('', ExportDateBr::only(null));
        $this->assertSame('', ExportDateBr::only(''));
    }
}
