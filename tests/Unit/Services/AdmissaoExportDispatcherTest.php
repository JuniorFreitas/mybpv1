<?php

namespace Tests\Unit\Services;

use App\Jobs\JobExportaAdmissoes;
use App\Services\Admissao\AdmissaoExportDispatcher;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdmissaoExportDispatcherTest extends TestCase
{
    #[Test]
    public function dispara_job_e_grava_cache(): void
    {
        Queue::fake();
        Cache::flush();

        $result = app(AdmissaoExportDispatcher::class)->dispatch(7, ['status' => 'admitido']);

        $this->assertSame(200, $result['status']);
        $this->assertArrayHasKey('export_id', $result['body']);
        Queue::assertPushed(JobExportaAdmissoes::class);
        $this->assertNotNull(Cache::get($result['body']['export_id']));
    }

    #[Test]
    public function impede_duplicidade_enquanto_em_andamento(): void
    {
        Queue::fake();
        $key = 'export_admissoes_7_' . md5(json_encode(['status' => 'admitido']));
        Cache::put($key, ['status' => 'processing', 'attempt' => 1, 'max_tries' => 3], now()->addMinutes(5));

        $result = app(AdmissaoExportDispatcher::class)->dispatch(7, ['status' => 'admitido']);

        $this->assertSame(200, $result['status']);
        $this->assertSame('processing', $result['body']['status']);
        Queue::assertNothingPushed();
    }
}
