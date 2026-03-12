<?php

namespace Tests\Unit\Jobs\Admissao\Importacao;

use App\Jobs\Admissao\Importacao\ImportacaoAdmissaoJob;
use Illuminate\Support\Facades\Queue;
use RuntimeException;
use Tests\TestCase;

class ImportacaoAdmissaoJobTest extends TestCase
{
    public function testJobPodeSerEnfileirado(): void
    {
        Queue::fake();
        ImportacaoAdmissaoJob::dispatch('importacao_admissoes/arquivo.xlsx', 1, null, 100);
        Queue::assertPushed(ImportacaoAdmissaoJob::class);
    }

    public function testHandleLancaExcecaoQuandoArquivoNaoExiste(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Arquivo não encontrado');

        $job = new ImportacaoAdmissaoJob(
            'caminho/inexistente_xyz_999.xlsx',
            1,
            null,
            100
        );
        $job->handle();
    }
}
