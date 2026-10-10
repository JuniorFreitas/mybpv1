<?php

namespace Tests\Unit\Models;

use App\Models\Arquivo;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ArquivoExecutavelTest extends TestCase
{
    #[DataProvider('executaveisProvider')]
    public function test_detecta_executaveis(?string $mime, string $nome): void
    {
        $this->assertTrue(Arquivo::ehExecutavel($mime, $nome));
        $this->assertFalse(Arquivo::permitidoNoCloud($mime, $nome));
    }

    #[DataProvider('permitidosProvider')]
    public function test_permite_nao_executaveis(?string $mime, string $nome): void
    {
        $this->assertFalse(Arquivo::ehExecutavel($mime, $nome));
        $this->assertTrue(Arquivo::permitidoNoCloud($mime, $nome));
    }

    public static function executaveisProvider(): array
    {
        return [
            'exe por extensao' => ['application/octet-stream', 'setup.exe'],
            'bat' => ['text/plain', 'script.bat'],
            'ps1' => ['text/plain', 'deploy.ps1'],
            'msi por mime' => ['application/x-msi', 'instalador.bin'],
            'jar' => ['application/java-archive', 'app.jar'],
            'sh' => ['application/x-sh', 'run.sh'],
        ];
    }

    public static function permitidosProvider(): array
    {
        return [
            'pdf' => ['application/pdf', 'contrato.pdf'],
            'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'planilha.xlsx'],
            'zip' => ['application/zip', 'anexos.zip'],
            'rar' => ['application/vnd.rar', 'backup.rar'],
            'png' => ['image/png', 'foto.png'],
            'octet stream generico' => ['application/octet-stream', 'dados.dat'],
            'sem extensao' => ['application/octet-stream', 'README'],
        ];
    }
}
