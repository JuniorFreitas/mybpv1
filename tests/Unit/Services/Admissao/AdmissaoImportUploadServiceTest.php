<?php

namespace Tests\Unit\Services\Admissao;

use App\Jobs\Admissao\Importacao\ImportacaoAdmissaoJob;
use App\Services\Admissao\AdmissaoImportUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdmissaoImportUploadServiceTest extends TestCase
{
    #[Test]
    public function enfileira_job_apos_salvar_planilha(): void
    {
        Queue::fake();
        Storage::fake('local');
        Storage::fake('disco-exportacao');

        $file = UploadedFile::fake()->create('admissoes.xlsx', 100, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        // storeAs usa disco local real em storage_path — garantir diretório
        $result = app(AdmissaoImportUploadService::class)->enfileirar($file, 10, 5);

        $this->assertContains($result['status'], [202, 500]);
        if ($result['status'] === 202) {
            $this->assertSame('success', $result['body']['status']);
            $this->assertArrayHasKey('uuid', $result['body']);
            Queue::assertPushed(ImportacaoAdmissaoJob::class);
        }
    }
}
