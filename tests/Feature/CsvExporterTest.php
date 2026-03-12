<?php

namespace Tests\Feature;

use App\Models\Arquivo;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use MasterTag\CsvExporter;
use App\Models\User;

class CsvExporterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake(Arquivo::DISCO_EXPORTACAO);
    }

    public function testExportCsv()
    {
        Event::fake();

        // Usuário em memória (objeto) — testes nunca usam base real
        $user = new User();
        $user->id = 1;
        $user->empresa_id = 100;

        // Criador de exportação no-op para não persistir no banco
        $exportacaoCreator = function () {
            // não persiste; apenas evita chamar Exportacao::create()
        };

        $headers = ['Nome', 'Email'];
        $data = [
            ['John Doe', 'john@example.com'],
            ['Jane Smith', 'jane@example.com'],
        ];

        $exporter = new CsvExporter($user, 'TestLocal', $headers, $data, $exportacaoCreator);
        $fileName = $exporter->export();

        $this->assertNotNull($fileName);
        $this->assertTrue(Storage::disk(Arquivo::DISCO_EXPORTACAO)->exists($fileName));
    }
}
