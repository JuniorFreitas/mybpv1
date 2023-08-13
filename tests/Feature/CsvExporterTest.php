<?php

namespace Tests\Feature;

use App\Models\Arquivo;
use Storage;
use Tests\TestCase;
use MasterTag\CsvExporter;
use App\Models\User;

class CsvExporterTest extends TestCase
{
    public function testExportCsv()
    {
        // Busque o usuário com ID 1
        $user = User::find(1);

        // Dados fictícios para exportar
        $headers = ['Nome', 'Email'];
        $data = [
            ['John Doe', 'john@example.com'],
            ['Jane Smith', 'jane@example.com'],
        ];

        // Crie o exportador CSV
        $exporter = new CsvExporter($user, 'TestLocal', $headers, $data);

        // Execute o processo de exportação
        $fileName = $exporter->export();

        // Verifique se o arquivo foi exportado com sucesso
        $this->assertNotNull($fileName);

        // Verifique se o arquivo existe no armazenamento S3
        $this->assertTrue(Storage::disk(Arquivo::DISCO_EXPORTACAO)->exists($fileName));

    }
}
