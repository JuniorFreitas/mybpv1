<?php

namespace Tests\Unit\Console;

use Tests\TestCase;

class ImportarAdmissoesCommandTest extends TestCase
{
    public function testArquivoInexistenteFalha(): void
    {
        $this->artisan('mybp:importar-admissoes', [
            'arquivo' => 'nao_existe.xlsx',
            'empresa_id' => 1,
        ])->assertFailed();
    }

    public function testResumoExibidoAposProcessamento(): void
    {
        $path = storage_path('app/nao_existe_xyz_12345.xlsx');
        if (file_exists($path)) {
            unlink($path);
        }
        $this->assertFileDoesNotExist($path);
        $exitCode = $this->artisan('mybp:importar-admissoes', [
            'arquivo' => 'nao_existe_xyz_12345.xlsx',
            'empresa_id' => 1,
        ]);
        $this->assertNotEquals(0, $exitCode);
    }
}
